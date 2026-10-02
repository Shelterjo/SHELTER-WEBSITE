<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers on every response (TOOL-043, SECURITY-CENTER.md). No third-party origins are allowed;
 * adding one (analytics, maps…) requires a deliberate change here after owner approval.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $dev = Vite::isRunningHot() ? ' '.$this->devServer() : '';
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self'{$dev}",
            "style-src 'self'{$dev}",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'".($dev !== '' ? $dev.' '.str_replace('http', 'ws', $this->devServer()) : ''),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);

        $headers = [
            'Content-Security-Policy' => $csp,
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()',
            'X-Frame-Options' => 'DENY',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];
        // HSTS only over HTTPS. No includeSubDomains/preload until the shop. subdomain is audited (SECURITY-CENTER.md).
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }
        // A route that already set a stricter value (e.g. the sandboxed CSP of a private file download) keeps it — once.
        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    private function devServer(): string
    {
        $hot = @file_get_contents(public_path('hot'));

        return is_string($hot) ? trim($hot) : '';
    }
}
