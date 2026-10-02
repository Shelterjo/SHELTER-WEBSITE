#!/usr/bin/env bash
# SHELTER — what the staging web server answers, from outside: status, server, whether the app answered, page title.
# Needs URL, AUTH_USER, AUTH_PASS. Never prints cookies or bodies beyond the <title>.
set -uo pipefail
tmp="$(mktemp -d)"
probe() {
    local path="$1" mode="$2" code
    local args=(-s -o "$tmp/body" -D "$tmp/head" --max-time 20)
    [ "$mode" = "auth" ] && args+=(-u "$AUTH_USER:$AUTH_PASS")
    code="$(curl "${args[@]}" -w '%{http_code}' "$URL/$path" || true)"
    local server app cache title
    server="$(grep -i '^server:' "$tmp/head" | tr -d '\r' | cut -c9-50 | head -n 1)"
    app="$(grep -qi '^content-security-policy:' "$tmp/head" && echo 'app' || echo 'no-app-headers')"
    cache="$(grep -iE '^(x-cache|via|x-varnish|age):' "$tmp/head" | tr -d '\r' | tr '\n' ' ' | cut -c1-80)"
    title="$(grep -o '<title>[^<]*' "$tmp/body" 2>/dev/null | head -n 1 | cut -c8-70)"
    echo "/$path [$mode] → $code · server: ${server:-?} · $app · ${cache:-no cache headers} · title: ${title:-none}"
}
for path in favicon.ico brand/favicon-32.png index.php up ar/; do
    probe "$path" anon
    probe "$path" auth
done
rm -rf "$tmp"
