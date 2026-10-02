#!/usr/bin/env bash
# SHELTER — read-only look at the staging application folder, run ON the server as the application user.
# Prints names, links, versions, the app's own environment summary and the last log lines — never .env values.
set -uo pipefail
cd "${SHELTER_APP_ROOT:-.}" || exit 2
app="$(pwd)"
echo "== who: $(id)"
echo "== web server users: nginx=$(ps -o user= -C nginx 2>/dev/null | sort -u | tr '\n' ' ') php-fpm=$(ps -o user= -C php-fpm8.3,php-fpm 2>/dev/null | sort -u | tr '\n' ' ')"
echo "== owners and modes"
for p in . public_html public_html/releases public_html/current/ public_html/current/public public_html/current/public/index.php \
    public_html/current/bootstrap/cache public_html/current/bootstrap/cache/config.php private_html private_html/shelter \
    private_html/shelter-public private_html/shelter-public/media public_html/public_html; do
    [ -e "$p" ] && stat -c '%a %U:%G %n' "$p"
done
echo "== public_html/public_html (should not exist)"; find public_html/public_html -maxdepth 3 2>/dev/null | head -n 10
echo "== public_html"; ls -la public_html | awk 'NR>1 {print $1, $3":"$4, $9, $10, $11}'
echo "== current → $(readlink public_html/current 2>/dev/null || echo 'not a link')"
echo "== current/public"; ls -la public_html/current/public 2>&1 | awk 'NR>1 {print $1, $9, $10, $11}' | head -n 20
echo "== php: $(php -v | head -n 1)"
echo "== extensions: $(php -m | grep -ixE 'intl|gd|zip|pdo_mysql|mbstring|openssl' | tr '\n' ' ')"
echo "== artisan about"
(cd public_html/current && php artisan about --only=environment,cache,drivers 2>&1 | grep -vi 'url' | head -n 30)
echo "== web server answer from the server itself (bypassing Cloudflare/outside network)"
for path in up ar/ index.php favicon.ico; do
    echo "/$path → $(curl -s -o /dev/null -w '%{http_code}' --max-time 10 -H "Host: ${APP_HOST:-localhost}" "http://127.0.0.1/$path")"
done
log="$(ls -1t "$app"/private_html/shelter/storage/logs/*.log 2>/dev/null | head -n 1)"
echo "== last log lines: ${log##*/}"
[ -n "$log" ] && tail -n 25 "$log" | cut -c1-240
exit 0
