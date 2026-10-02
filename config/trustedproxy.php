<?php

/*
| Trusted proxies (deploy-facts F-1). Laravel's TrustProxies middleware reads this key on every request when no list was
| given in bootstrap/app.php — so TRUSTED_PROXIES now works from .env (and from config:cache). bootstrap/app.php runs
| before .env is loaded: an env() there only ever saw real server variables and fell back to the default.
|
| TRUSTED_PROXIES: comma-separated IP addresses and CIDR ranges (IPv4 and IPv6) whose X-Forwarded-For/-Proto/-Host
| headers are believed. Only the peer that connects to PHP is checked, so a visitor cannot spoof X-Forwarded-For to
| dodge rate limits unless that peer is listed here.
|   - Empty or unset: 127.0.0.1,::1 (Cloudways: Nginx → Varnish → Apache on the same server).
|   - Behind Cloudflare: the loopback pair plus every range Cloudflare publishes at https://www.cloudflare.com/ips/
|     (that page is the source of truth; re-check it when Cloudflare announces changes).
|   - '*' trusts any peer: only for an origin that nothing but the proxy can reach. Never on a public Cloudways IP.
*/

$proxies = env('TRUSTED_PROXIES');
$proxies = array_values(array_filter(array_map('trim', explode(',', is_string($proxies) ? $proxies : '')), fn (string $p): bool => $p !== ''));

return [

    'proxies' => match (true) {
        $proxies === [] => ['127.0.0.1', '::1'],
        in_array('*', $proxies, true) => '*',
        default => $proxies,
    },

];
