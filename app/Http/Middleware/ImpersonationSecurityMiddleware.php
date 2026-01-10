<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\MySQLImpersonationService;

class ImpersonationSecurityMiddleware
{
    private $impersonationService;

    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Rate limiting for impersonation requests
        $this->applyRateLimit($request);

        // Validate request origin and referrer
        $this->validateRequestOrigin($request);

        // Log all impersonation attempts
        $this->logImpersonationAttempt($request);

        return $next($request);
    }

    /**
     * Apply rate limiting to prevent abuse
     */
    private function applyRateLimit(Request $request)
    {
        $adminId = auth()->id();
        $ip = $request->ip();

        // Multiple rate limit keys for comprehensive protection
        $rateLimits = [
            "impersonation_admin_{$adminId}" => ['max' => 100, 'window' => 3600], // 10/hour per admin
            "impersonation_ip_{$ip}" => ['max' => 20, 'window' => 3600],        // 20/hour per IP
            "impersonation_global" => ['max' => 100, 'window' => 3600]          // 100/hour global
        ];

        foreach ($rateLimits as $key => $limit) {
            $attempts = Cache::get($key, 0);

            if ($attempts >= $limit['max']) {
                // Exponential backoff for repeated violations
                $backoffTime = min(3600, pow(2, $attempts - $limit['max']) * 60);
                Cache::put($key, $attempts + 1, now()->addSeconds($backoffTime));

                Log::warning('Impersonation rate limit exceeded', [
                    'admin_id' => $adminId,
                    'ip' => $ip,
                    'user_agent' => $request->userAgent(),
                    'limit_key' => $key,
                    'attempts' => $attempts,
                    'backoff_time' => $backoffTime
                ]);

                abort(429, 'Too many impersonation attempts. Please try again later.');
            }

            Cache::put($key, $attempts + 1, now()->addSeconds($limit['window']));
        }
    }

    /**
     * Validate request origin and referrer for security
     */
    private function validateRequestOrigin(Request $request)
    {
        $allowedOrigins = [
            'admin.jippymart.in',
            'localhost', // For development
            '127.0.0.1'  // For development
        ];

        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $host = $request->getHost();

        // Check if request is from allowed origin
        $isValidOrigin = false;
        foreach ($allowedOrigins as $allowedOrigin) {
            if (strpos($origin, $allowedOrigin) !== false ||
                strpos($referer, $allowedOrigin) !== false ||
                strpos($host, $allowedOrigin) !== false) {
                $isValidOrigin = true;
                break;
            }
        }

        if (!$isValidOrigin && !app()->environment('local')) {
            Log::warning('Invalid impersonation request origin', [
                'origin' => $origin,
                'referer' => $referer,
                'host' => $host,
                'ip' => $request->ip(),
                'admin_id' => auth()->id()
            ]);

            abort(403, 'Invalid request origin');
        }
    }

    /**
     * Log impersonation attempts for security audit
     */
    private function logImpersonationAttempt(Request $request)
    {
        $logData = [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email ?? 'unknown',
            'restaurant_id' => $request->input('restaurant_id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'request_data' => $request->except(['_token', 'password'])
        ];

        Log::info('Impersonation attempt', $logData);
    }
}
