#!/usr/bin/env python3
"""Checks that each staging secret has the shape it should, before anything connects to the server.

The secrets arrive as environment variables named exactly like them. Only a verdict per secret is printed (OK, or what
is wrong with it in plain words) — never a value, a length of a password or a piece of one. Exit code 1 when any
secret is missing or has the wrong shape. A value pasted into the wrong secret (a private key in the host, an IP in
the key) is the usual cause, so that case is named.
"""
import ipaddress
import os
import re
import sys

HOSTNAME = re.compile(r"^(?=.{1,253}$)([A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)*[A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?$")
NAME = re.compile(r"^[A-Za-z0-9_][A-Za-z0-9_.-]{0,63}$")
URL = re.compile(r"^https?://[A-Za-z0-9.-]+(:[0-9]{1,5})?$")


def kind(value: str) -> str:
    """What a misplaced value looks like, without showing it."""
    if "PRIVATE KEY" in value:
        return "a PRIVATE KEY (it belongs in STAGING_SSH_KEY)"
    if re.search(r"(^|\s)ssh-(ed25519|rsa)\s+AAAA", value):
        return "a PUBLIC key (it belongs in Cloudways, not here)"
    if value.startswith(("http://", "https://")):
        return "a web address (it belongs in STAGING_APP_URL)"
    try:
        ipaddress.ip_address(value)
        return "an IP address (it belongs in STAGING_SSH_HOST)"
    except ValueError:
        pass
    lines = len([line for line in value.split("\n") if line.strip()])
    return f"{lines} lines of text" if lines > 1 else "something else"


def check(name: str, raw: str) -> str | None:
    """None when the value has the right shape, else what is wrong."""
    raw = raw.replace("\r", "")
    value = raw.strip()
    if value == "":
        return "is missing or empty"
    if name == "STAGING_SSH_KEY":
        return None if "PRIVATE KEY" in value else f"should be the private key file, but holds {kind(value)}"
    if "PRIVATE KEY" in value:
        return f"holds {kind(value)}"
    if "\n" in value:
        return f"must be one line, but holds {kind(value)}"
    problem = shape(name, value)
    if problem is None and raw != value:
        return "is right but has a space or line break before or after it — edit the secret and remove it"
    return problem


def shape(name: str, value: str) -> str | None:
    if name == "STAGING_SSH_HOST":
        try:
            ipaddress.ip_address(value)
            return None
        except ValueError:
            return None if HOSTNAME.match(value) else f"should be the server's public IP (Cloudways › Servers), but holds {kind(value)}"
    if name == "STAGING_SSH_USER":
        if value.lower().startswith("master"):
            return "is the server MASTER user — use the application user of shelter-staging (Application Credentials)"
        return None if NAME.match(value) else f"should be the application's SFTP/SSH user name, but holds {kind(value)}"
    if name == "STAGING_APP_URL":
        if value.endswith("/"):
            return "must not end with / (e.g. https://….cloudwaysapps.com)"
        return None if URL.match(value) else f"should be the address with https:// and nothing after the host, but holds {kind(value)}"
    if name in ("STAGING_DB_DATABASE", "STAGING_DB_USERNAME"):
        return None if NAME.match(value) else f"should be the name shown in Access Details › MySQL, but holds {kind(value)}"
    if name == "STAGING_BASIC_AUTH_USER":
        return "must not contain ':'" if ":" in value else None
    return None


def main() -> int:
    names = sys.argv[1:]
    failed = 0
    for name in names:
        problem = check(name, os.environ.get(name, ""))
        if problem is None:
            print(f"{name}: OK")
        else:
            print(f"::error::{name} {problem}")
            failed = 1
    return failed


if __name__ == "__main__":
    sys.exit(main())
