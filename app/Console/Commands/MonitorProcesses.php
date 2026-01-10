<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorProcesses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:processes {--threshold=350 : Warning threshold for process count}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor server process usage and log warnings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = $this->option('threshold');
        $currentProcesses = $this->getCurrentProcessCount();
        $maxProcesses = 400;
        
        $usagePercentage = round(($currentProcesses / $maxProcesses) * 100, 2);
        
        $this->info("Current Process Usage: {$currentProcesses}/{$maxProcesses} ({$usagePercentage}%)");
        
        if ($currentProcesses > $threshold) {
            $this->warn("⚠️  HIGH PROCESS USAGE DETECTED!");
            $this->warn("Current: {$currentProcesses}, Threshold: {$threshold}");
            
            Log::warning("High process usage detected", [
                'current_processes' => $currentProcesses,
                'max_processes' => $maxProcesses,
                'usage_percentage' => $usagePercentage,
                'threshold' => $threshold,
                'timestamp' => now()->toISOString()
            ]);
            
            // Suggest actions
            $this->line('');
            $this->line('🚨 RECOMMENDED ACTIONS:');
            $this->line('1. Check for stuck processes: ps aux | grep php');
            $this->line('2. Restart queue worker: php artisan queue:restart');
            $this->line('3. Clear caches: php artisan cache:clear');
            $this->line('4. Check logs: tail -f storage/logs/laravel.log');
            
        } else {
            $this->info("✅ Process usage is within normal limits");
        }
        
        return 0;
    }
    
    /**
     * Get current process count
     */
    private function getCurrentProcessCount()
    {
        try {
            // If shell_exec is unavailable (disabled by hosting), skip counting
            if (!\function_exists('shell_exec')) {
                $this->warn('shell_exec is disabled; skipping process count');
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
            $this->error('Failed to get process count: ' . $e->getMessage());
            return 0;
        }
    }
}
