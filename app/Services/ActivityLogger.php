<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class ActivityLogger
{
    protected $collection = 'activity_logs';

    /**
     * Log an activity to Firestore
     *
     * @param mixed $user The authenticated user
     * @param string $module The module name (e.g., 'cuisines', 'orders')
     * @param string $action The action performed (e.g., 'created', 'updated', 'deleted')
     * @param string $description Description of the action
     * @param Request|null $request The HTTP request object
     * @return bool
     */
    public function log($user, $module, $action, $description, Request $request = null)
    {
        try {
            // Get user information
            $userType = $this->getUserType($user);
            $role = $this->getUserRole($user);
            $userName = $this->getUserName($user);
            $userId = $user->id ?? null;

            // Get request information
            $ipAddress = $request ? $request->ip() : request()->ip();
            $userAgent = $request ? $request->userAgent() : request()->userAgent();

            // Save to direct columns (not JSON context)
            ActivityLog::create([
                'admin_id' => $userId,
                'admin_name' => $userName,
                'user_id' => $userId,
                'user_name' => $userName,
                'user_type' => $userType,
                'role' => $role,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'resource_type' => $module,
                'resource_id' => null,
                'old_values' => null,
                'new_values' => null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'additional_data' => json_encode([
                    'timestamp' => now()->toIso8601String(),
                    'url' => $request ? $request->fullUrl() : null,
                    'method' => $request ? $request->method() : null,
                ]),
                'timestamp' => now()->toIso8601String(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info("✅ Activity logged: {$userName} {$action} in {$module}");
            return true;
        } catch (\Exception $e) {
            \Log::error('❌ Activity Logger Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Get user type based on user object
     *
     * @param mixed $user
     * @return string
     */
    protected function getUserType($user)
    {
        if (!$user) {
            return 'unknown';
        }

        // Check if user has role_id (admin user)
        if (isset($user->role_id)) {
            return 'admin';
        }

        // Check for other user types based on user properties
        if (isset($user->user_type)) {
            return $user->user_type;
        }

        // Default to admin if we can't determine
        return 'admin';
    }

    /**
     * Get user role
     *
     * @param mixed $user
     * @return string
     */
    protected function getUserRole($user)
    {
        if (!$user) {
            return 'unknown';
        }

        // If user has role_id, get role name from database
        if (isset($user->role_id)) {
            $role = \App\Models\Role::find($user->role_id);
            return $role ? $role->role_name : 'unknown';
        }

        // Check for role property
        if (isset($user->role)) {
            return $user->role;
        }

        return 'unknown';
    }

    /**
     * Get user name
     *
     * @param mixed $user
     * @return string
     */
    protected function getUserName($user)
    {
        if (!$user) {
            return 'Unknown User';
        }

        // Check for name property
        if (isset($user->name) && !empty($user->name)) {
            return $user->name;
        }

        // Check for first_name and last_name properties
        if (isset($user->first_name) && isset($user->last_name)) {
            return trim($user->first_name . ' ' . $user->last_name);
        }

        // Check for username property
        if (isset($user->username) && !empty($user->username)) {
            return $user->username;
        }

        // Check for email property
        if (isset($user->email) && !empty($user->email)) {
            return $user->email;
        }

        // Fallback to user ID
        if (isset($user->id)) {
            return 'User ' . $user->id;
        }

        return 'Unknown User';
    }

    /**
     * Get logs for a specific module
     *
     * @param string $module
     * @param int $limit
     * @return array
     */
    public function getLogsByModule($module, $limit = 100)
    {
        try {
            $rows = ActivityLog::query()
                ->where('context->module', $module)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            return $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'user_id' => $row->admin_user_id,
                    'user_name' => $row->context['user_name'] ?? null,
                    'user_type' => $row->context['user_type'] ?? null,
                    'role' => $row->context['role'] ?? null,
                    'module' => $row->context['module'] ?? null,
                    'action' => $row->context['action'] ?? null,
                    'description' => $row->context['description'] ?? null,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'created_at' => $row->created_at,
                ];
            })->all();
        } catch (\Exception $e) {
            \Log::error('Error fetching activity logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all logs with pagination
     *
     * @param int $limit
     * @param string|null $startAfter
     * @return array
     */
    public function getAllLogs($limit = 50, $startAfter = null)
    {
        try {
            $rows = ActivityLog::query()
                ->when($startAfter, function ($q) use ($startAfter) {
                    return $q->where('id', '<', (int) $startAfter);
                })
                ->orderByDesc('id')
                ->limit($limit)
                ->get();

            return $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'user_id' => $row->admin_user_id,
                    'user_name' => $row->context['user_name'] ?? null,
                    'user_type' => $row->context['user_type'] ?? null,
                    'role' => $row->context['role'] ?? null,
                    'module' => $row->context['module'] ?? null,
                    'action' => $row->context['action'] ?? null,
                    'description' => $row->context['description'] ?? null,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'created_at' => $row->created_at,
                ];
            })->all();
        } catch (\Exception $e) {
            \Log::error('Error fetching all activity logs: ' . $e->getMessage());
            return [];
        }
    }
}
