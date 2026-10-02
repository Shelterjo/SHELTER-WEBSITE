#!/usr/bin/env python3
"""Renders an environment template to stdout (docs/platform/ACCESS-SETUP.md: secrets never in chat, Git or logs).

{{NAME}} takes the environment variable ENV_<NAME>; APP_KEY and the RECRUITMENT_* keys are generated (32 random
bytes, base64) when not given. Values are quoted for phpdotenv: single quotes (literal) unless the value holds one.
Fails, without printing any value, when a placeholder has no value or a value has a line break.
"""
import base64
import os
import re
import secrets
import sys

GENERATED = {"APP_KEY", "RECRUITMENT_ID_ENC_KEY", "RECRUITMENT_ID_HMAC_KEY"}


def quote(value: str) -> str:
    if "'" not in value:
        return "'" + value + "'"
    return '"' + value.replace("\\", "\\\\").replace('"', '\\"').replace("$", "\\$") + '"'


def main() -> int:
    template = open(sys.argv[1], encoding="utf-8").read()
    missing = []

    def fill(match: "re.Match[str]") -> str:
        name = match.group(1)
        value = os.environ.get("ENV_" + name, "")
        if value == "" and name in GENERATED:
            value = "base64:" + base64.b64encode(secrets.token_bytes(32)).decode()
        if value == "" or "\n" in value or "\r" in value:
            missing.append(name)
            return ""
        return quote(value)

    rendered = re.sub(r"\{\{([A-Z0-9_]+)\}\}", fill, template)
    if missing:
        print("missing or invalid values for: " + ", ".join(sorted(set(missing))), file=sys.stderr)
        return 1
    sys.stdout.write(rendered)
    return 0


if __name__ == "__main__":
    sys.exit(main())
