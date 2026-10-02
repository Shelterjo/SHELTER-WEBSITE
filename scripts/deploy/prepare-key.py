#!/usr/bin/env python3
"""Writes the deploy key from the environment variable KEY to the file given (mode 600), repaired for how it is pasted.

A private key copied on Windows and pasted into a GitHub secret can arrive with CRLF line endings, with its lines
joined by spaces, with surrounding blanks or quotes. The armour (BEGIN/END lines) is kept and the base64 body is
re-wrapped, which is what OpenSSH reads. When the value cannot be a usable key, the reason is printed in plain words
and the exit code is 1. Nothing from the key is ever printed: only its kind (public key, PuTTY file, passphrase) and
lengths.
"""
import base64
import binascii
import os
import re
import struct
import sys

ARMOUR = re.compile(r"-----BEGIN ([A-Z0-9 ]+)-----(.*?)-----END \1-----", re.S)
PUBLIC = re.compile(r"^\s*(ssh-(ed25519|rsa|dss)|ecdsa-sha2-\S+|sk-\S+)\s+AAAA", re.M)


def fail(reason: str) -> int:
    print(f"::error::STAGING_SSH_KEY {reason}")
    return 1


def openssh_cipher(body: bytes) -> str | None:
    """The cipher of an OpenSSH private key ("none" = no passphrase); None when the body is not that format."""
    magic = b"openssh-key-v1\0"
    if not body.startswith(magic) or len(body) < len(magic) + 4:
        return None
    (size,) = struct.unpack(">I", body[len(magic) : len(magic) + 4])
    return body[len(magic) + 4 : len(magic) + 4 + size].decode("ascii", "replace")


def main() -> int:
    target = sys.argv[1]
    raw = os.environ.get("KEY", "").replace("\r", "").strip().strip("'\"").strip()
    if raw == "":
        return fail("is empty — add the secret in GitHub › Settings › Secrets and variables › Actions.")
    if "PuTTY-User-Key-File" in raw:
        return fail("is a PuTTY .ppk file — paste the OpenSSH private key file (shelter_staging) instead.")
    match = ARMOUR.search(raw)
    if match is None:
        if PUBLIC.search(raw):
            return fail("holds the PUBLIC key (shelter_staging.pub). Paste the PRIVATE key file shelter_staging (no .pub).")
        if "-----BEGIN" in raw:
            return fail("is cut: the -----END … PRIVATE KEY----- line is missing. Copy the whole file.")
        return fail("has no -----BEGIN … PRIVATE KEY----- line. Copy the whole private key file, first line to last.")
    kind, inner = match.group(1), match.group(2)
    if "PRIVATE KEY" not in kind:
        return fail(f"is a '{kind}' block, not a private key.")

    headers = [line.strip() for line in inner.split("\n") if ":" in line]
    if any("ENCRYPTED" in line for line in headers) or "ENCRYPTED" in kind:
        return fail("is protected by a passphrase. Make a new key and press Enter twice when it asks for a passphrase.")
    body = re.sub(r"\s+", "", "\n".join(line for line in inner.split("\n") if ":" not in line))
    try:
        decoded = base64.b64decode(body, validate=True)
    except (binascii.Error, ValueError):
        return fail(f"body is not valid base64 ({len(body)} characters) — part of the key was lost when copying.")
    cipher = openssh_cipher(decoded) if kind == "OPENSSH PRIVATE KEY" else None
    if kind == "OPENSSH PRIVATE KEY" and cipher is None:
        return fail("is damaged (not an OpenSSH key inside the BEGIN/END lines) — copy the file again.")
    if cipher not in (None, "none"):
        return fail("is protected by a passphrase. Make a new key and press Enter twice when it asks for a passphrase.")

    wrapped = "\n".join(body[i : i + 70] for i in range(0, len(body), 70))
    fd = os.open(target, os.O_WRONLY | os.O_CREAT | os.O_TRUNC, 0o600)
    with os.fdopen(fd, "w", encoding="ascii") as out:
        out.write(f"-----BEGIN {kind}-----\n")
        out.write("".join(line + "\n" for line in headers))
        out.write(wrapped + f"\n-----END {kind}-----\n")
    return 0


if __name__ == "__main__":
    sys.exit(main())
