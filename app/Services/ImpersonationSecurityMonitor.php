<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ImpersonationSecurityMonitor
{
    /**
     * Monitor impersonation activity for security threats
     */
    public function monitorActivity($adminId, $restaurantId, $ip, $userAgent, $success = true)
    {
        $this->trackMetrics($adminId, $ip, $success);
        $this->checkSuspiciousActivity($adminId, $ip, $userAgent);
        $this->checkAnomalies($adminId, $restaurantId);
    }

    /**
     * Track key security metrics
     */
    private function trackMetrics($adminId, $ip, $success)
    {
        $keys = [
            "impersonation_attempts_admin_{$adminId}",
            "impersonation_attempts_ip_{$ip}",
            "impersonation_attempts_global"
        ];

        if ($success) {
            $keys[] = "impersonation_success_admin_{$adminId}";
            $keys[] = "impersonation_success_ip_{$ip}";
            $keys[] = "impersonation_success_global";
        } else {
            $keys[] = "impersonation_failed_admin_{$adminId}";
            $keys[] = "impersonation_failed_ip_{$ip}";
            $keys[] = "impersonation_failed_global";
        }

        foreach ($keys as $key) {
            $current = Cache::get($key, 0);
            Cache::put($key, $current + 1, 3600); // 1 hour
        }
    }

    /**
     * Check for suspicious activity patterns
     */
    private function checkSuspiciousActivity($adminId, $ip, $userAgent)
    {
        $suspicious = false;
        $reasons = [];

        // Check for multiple failed attempts
        $failedAttempts = Cache::get("impersonation_failed_admin_{$adminId}", 0);
        if ($failedAttempts >= 5) {
            $suspicious = true;
            $reasons[] = "Multiple failed impersonation attempts";
        }

        // Check for unusual IP patterns
        $ipHistory = Cache::get("impersonation_ips_admin_{$adminId}", []);
        if (!in_array($ip, $ipHistory)) {
            $ipHistory[] = $ip;
            Cache::put("impersonation_ips_admin_{$adminId}", $ipHistory, 86400); // 24 hours
            
            if (count($ipHistory) > 3) {
                $suspicious = true;
                $reasons[] = "Impersonation from multiple IP addresses";
            }
        }

        // Check for unusual user agents
        $userAgentHistory = Cache::get("impersonation_useragents_admin_{$adminId}", []);
        if (!in_array($userAgent, $userAgentHistory)) {
            $userAgentHistory[] = $userAgent;
            Cache::put("impersonation_useragents_admin_{$adminId}", $userAgentHistory, 86400);
            
            if (count($userAgentHistory) > 2) {
                $suspicious = true;
                $reasons[] = "Impersonation from multiple user agents";
            }
        }

        if ($suspicious) {
            $this->sendSecurityAlert($adminId, $ip, $userAgent, $reasons);
        }
    }

    /**
     * Check for anomalous behavior
     */
    private function checkAnomalies($adminId, $restaurantId)
    {
        // Check for impersonation of same restaurant multiple times
        $restaurantKey = "impersonation_restaurant_{$restaurantId}_admin_{$adminId}";
        $count = Cache::get($restaurantKey, 0) + 1;
        Cache::put($restaurantKey, $count, 3600);

        if ($count > 10) {
            $this->sendSecurityAlert($adminId, request()->ip(), request()->userAgent(), [
                "Excessive impersonation of restaurant {$restaurantId}"
            ]);
        }

        // Check for impersonation outside business hours
        $hour = now()->hour;
        if ($hour < 6 || $hour > 22) {
            $this->logSuspiciousActivity($adminId, "Impersonation outside business hours", [
                'hour' => $hour,
                'restaurant_id' => $restaurantId
            ]);
        }
    }

    /**
     * Send security alert
     */
    private function sendSecurityAlert($adminId, $ip, $userAgent, $reasons)
    {
        $alertData = [
            'admin_id' => $adminId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'reasons' => $reasons,
            'timestamp' => now()->toISOString(),
            'severity' => 'HIGH'
        ];

        // Log to security log
        Log::channel('security')->warning('Suspicious impersonation activity detected', $alertData);

        // Store in cache for dashboard
        $alertKey = "security_alert_" . uniqid();
        Cache::put($alertKey, $alertData, 86400); // 24 hours

        // Send email alert to security team
        if (config('impersonation.security_alerts_enabled', true)) {
            $this->sendEmailAlert($alertData);
        }
    }

    /**
     * Log suspicious activity
     */
    private function logSuspiciousActivity($adminId, $activity, $context = [])
    {
        Log::channel('security')->info('Suspicious impersonation activity', [
            'admin_id' => $adminId,
            'activity' => $activity,
            'context' => $context,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Send email alert to security team
     */
    private function sendEmailAlert($alertData)
    {
        try {
            Mail::raw("Security Alert: Suspicious Impersonation Activity\n\n" . 
                     "Admin ID: {$alertData['admin_id']}\n" .
                     "IP: {$alertData['ip']}\n" .
                     "User Agent: {$alertData['user_agent']}\n" .
                     "Reasons: " . implode(', ', $alertData['reasons']) . "\n" .
                     "Timestamp: {$alertData['timestamp']}", 
                function ($message) {
                    $message->to(config('impersonation.security_email', 'security@jippymart.in'))
                           ->subject('Security Alert: Suspicious Impersonation Activity');
                });
        } catch (\Exception $e) {
            Log::error('Failed to send security alert email', [
                'error' => $e->getMessage(),
                'alert_data' => $alertData
            ]);
        }
    }

    /**
     * Get security metrics for dashboard
     */
    public function getSecurityMetrics()
    {
        return [
            'total_attempts' => Cache::get('impersonation_attempts_global', 0),
            'success_rate' => $this->calculateSuccessRate(),
            'failed_attempts' => Cache::get('impersonation_failed_global', 0),
            'unique_ips' => $this->getUniqueIPs(),
            'alerts_count' => $this->getAlertsCount()
        ];
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate()
    {
        $total = Cache::get('impersonation_attempts_global', 0);
        $success = Cache::get('impersonation_success_global', 0);
        
        return $total > 0 ? ($success / $total) : 0;
    }

    /**
     * Get unique IPs
     */
    private function getUniqueIPs()
    {
        // This would need to be implemented based on your IP tracking mechanism
        return Cache::get('unique_impersonation_ips', []);
    }

    /**
     * Get alerts count
     */
    private function getAlertsCount()
    {
        $keys = Cache::get('security_alert_keys', []);
        return count($keys);
    }
}
