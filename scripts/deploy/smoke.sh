#!/usr/bin/env bash
# SHELTER — post-deploy checks of the staging site from outside (docs/CLOUDWAYS-DEPLOYMENT-RUNBOOK.md §4 step 8).
# Needs URL, AUTH_USER, AUTH_PASS in the environment. Prints status codes only; exit 1 when any check fails.
set -uo pipefail
fail=0
anon="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$URL/ar/")"
echo "/ar/ without basic auth → $anon (expected 401)"; [ "$anon" = "401" ] || fail=1
for path in up ar/ en/ ar/jo/menu/ ar/jo/locations/ dashboard/login robots.txt; do
    code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 30 -u "$AUTH_USER:$AUTH_PASS" "$URL/$path")"
    echo "/$path → $code"; [ "$code" = "200" ] || fail=1
done
page="$(curl -s --max-time 30 -u "$AUTH_USER:$AUTH_PASS" "$URL/ar/")"
if grep -q 'name="robots" content="noindex' <<< "$page"; then echo "noindex present"; else echo "::error::staging page is not noindex"; fail=1; fi
# The built assets and the brand files must be readable by the web server too (not only by PHP).
assets="$(grep -oE '/build/assets/[A-Za-z0-9._-]+\.(css|js)' <<< "$page" | sort -u | head -n 3 || true)
$(grep -oE '/brand/[A-Za-z0-9._/-]+\.(svg|webp|png)' <<< "$page" | sort -u | head -n 2 || true)"
[ -n "$(tr -d '[:space:]' <<< "$assets")" ] || { echo "::error::no built assets linked from /ar/"; fail=1; }
for asset in $assets; do
    code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 30 -u "$AUTH_USER:$AUTH_PASS" "$URL$asset")"
    echo "$asset → $code"; [ "$code" = "200" ] || fail=1
done
exit $fail
