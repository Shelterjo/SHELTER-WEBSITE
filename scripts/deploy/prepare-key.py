#!/usr/bin/env python3
"""Writes the deploy key from the environment variable KEY to the file given (mode 600), repaired for how it is pasted.

A private key copied on Windows and pasted into a GitHub secret can arrive with CRLF line endings, its lines joined by
spaces, quotes or blanks around it, dashes turned into typographic dashes, invisible characters, or without its
BEGIN/END lines. The armour is rebuilt and the base64 body re-wrapped, which is what OpenSSH reads. When the value
cannot be a usable key, the reason is printed in plain words and the exit code is 1. Nothing from the key is ever
printed: only what kind of value it is (public key, PuTTY file, passphrase, fingerprint…) and its rough shape.
"""
import base64
import binascii
import os
import re
import struct
import sys

DASHES = dict.fromkeys(map(ord, "‐‑‒–—―−﹘﹣－"), "-")
INVISIBLE = re.compile("[﻿​-‏⁠­]")
ARMOUR = re.compile(r"-{3,}\s*BEGIN ([A-Z0-9 ]+?)\s*-{3,}(.*?)-{3,}\s*END \1\s*-{3,}", re.S)
PUBLIC = re.compile(r"(^|\s)(ssh-(ed25519|rsa|dss)|ecdsa-sha2-\S+|sk-\S+)\s+AAAA", re.M)
OPENSSH = b"openssh-key-v1\0"


def fail(reason: str) -> int:
    print(f"::error::STAGING_SSH_KEY {reason}")
    return 1


def decode(body: str) -> bytes | None:
    try:
        return base64.b64decode(body, validate=True)
    except (binascii.Error, ValueError):
        return None


def openssh_cipher(blob: bytes) -> str | None:
    """The cipher of an OpenSSH private key ("none" = no passphrase); None when the blob is not that format."""
    if not blob.startswith(OPENSSH) or len(blob) < len(OPENSSH) + 4:
        return None
    (size,) = struct.unpack(">I", blob[len(OPENSSH) : len(OPENSSH) + 4])
    return blob[len(OPENSSH) + 4 : len(OPENSSH) + 4 + size].decode("ascii", "replace")


def shape(raw: str) -> str:
    """A description of the value that says nothing about its content."""
    lines = [line for line in raw.split("\n") if line.strip() != ""]
    if len(lines) == 1 and len(raw) < 100:
        return "It is one short line — a password, a file name or a fingerprint, not a key file."
    non_ascii = sum(1 for ch in raw if ord(ch) > 126)
    return f"It has {len(lines)} lines and about {round(len(raw), -1)} characters" + (
        f", {non_ascii} of them not plain ASCII." if non_ascii else "."
    )


def main() -> int:
    target = sys.argv[1]
    raw = INVISIBLE.sub("", os.environ.get("KEY", "")).replace("\r", "").replace(" ", " ").translate(DASHES)
    raw = raw.strip().strip("'\"`").strip()
    if raw == "":
        return fail("is empty — add the secret in GitHub › Settings › Secrets and variables › Actions.")
    if "PuTTY-User-Key-File" in raw:
        return fail("is a PuTTY .ppk file — paste the OpenSSH private key file (shelter_staging) instead.")
    if raw.startswith(("SHA256:", "MD5:")):
        return fail("holds a key FINGERPRINT, not the key. Paste the contents of the private key file shelter_staging.")

    match = ARMOUR.search(raw)
    if match is not None:
        kind, inner = match.group(1), match.group(2)
    elif PUBLIC.search(raw):
        return fail("holds the PUBLIC key (shelter_staging.pub). Paste the PRIVATE key file shelter_staging (no .pub).")
    elif re.search(r"BEGIN [A-Z0-9 ]*PRIVATE KEY", raw):
        return fail("is cut: the -----END … PRIVATE KEY----- line is missing. Copy the whole file.")
    else:
        # The body alone (BEGIN/END lines left out): accepted when it really is an OpenSSH key.
        blob = decode(re.sub(r"\s+", "", raw))
        if blob is None or not blob.startswith(OPENSSH):
            return fail("is not a private key (no -----BEGIN … PRIVATE KEY----- line). " + shape(raw)
                        + " Copy the whole private key file shelter_staging, from its first line to its last.")
        kind, inner = "OPENSSH PRIVATE KEY", raw
        print("::notice::STAGING_SSH_KEY had no BEGIN/END lines; they were added back.")

    if "PRIVATE KEY" not in kind:
        return fail(f"is a '{kind}' block, not a private key. Paste the private key file shelter_staging (no .pub).")
    headers = [line.strip() for line in inner.split("\n") if ":" in line]
    if any("ENCRYPTED" in line for line in headers) or "ENCRYPTED" in kind:
        return fail("is protected by a passphrase. Make a new key and press Enter twice when it asks for a passphrase.")
    body = re.sub(r"\s+", "", "\n".join(line for line in inner.split("\n") if ":" not in line))
    blob = decode(body)
    if blob is None:
        return fail(f"body is not valid base64 ({len(body)} characters) — part of the key was lost when copying.")
    cipher = openssh_cipher(blob) if kind == "OPENSSH PRIVATE KEY" else None
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
