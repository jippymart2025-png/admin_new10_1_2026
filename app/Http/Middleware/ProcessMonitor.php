<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProcessMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check current process count
        $currentProcesses = $this->getCurrentProcessCount();
        $maxProcesses = 400; // Your server limit
        $warningThreshold = 350; // 87.5% of limit
        
        // Log warning if approaching limit
        if ($currentProcesses > $warningThreshold) {
            Log::warning("High process usage detected", [
                'current_processes' => $currentProcesses,
                'max_processes' => $maxProcesses,
                'usage_percentage' => round(($currentProcesses / $maxProcesses) * 100, 2),
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }
        
        // Block requests if at critical limit
        if ($currentProcesses >= ($maxProcesses - 10)) {
            Log::critical("Process limit nearly reached - blocking request", [
                'current_processes' => $currentProcesses,
                'max_processes' => $maxProcesses,
                'url' => $request->url()
            ]);
            
            return response()->json([
                'error' => 'Server temporarily unavailable',
                'message' => 'Please try again in a few moments'
            ], 503);
        }
        
        return $next($request);
    }
    
    /**
     * Get current process count
     */
    private function getCurrentProcessCount()
    {
        try {
            // If shell_exec is unavailable (disabled by hosting), skip counting
            if (!\function_exists('shell_exec')) {
                Log::notice('shell_exec is disabled; skipping process count in ProcessMonitor');
                return 0;
            }
            // Windows-compatible process counting
            if (PHP_OS_FAMILY === 'Windows') {
                // Use PowerShell for more reliable process counting
                $output = \shell_exec('powershell "Get-Process php -ErrorAction SilentlyContinue | Measure-Object | Select-Object -ExpandProperty Count"');
                $count = (int) trim($output);
                
                // Fallback to tasklist if PowerShell fails
                if ($count === 0) {
                    $output = \shell_exec('tasklist /FI "IMAGENAME eq php.exe" 2>nul | find /C "php.exe"');
                    $count = (int) trim($output);
                }
                
                return $count;
            } else {
                // Linux/Unix
                $output = \shell_exec('ps aux | grep php | grep -v grep | wc -l');
                return (int) trim($output);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to get process count: ' . $e->getMessage());
            return 0;
        }
    }
}
