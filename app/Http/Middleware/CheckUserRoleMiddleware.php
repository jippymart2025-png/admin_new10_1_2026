<?php

namespace App\Http\Middleware;

use App\Models\admin_users;
use App\Models\Permission;
use Auth;
use Closure;

class CheckUserRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {

            $user = auth()->user();

            $role_has_permissions = Permission::where('role_id', $user->role_id)->pluck('routes')->toArray();

            $role_has_permissions = array_unique($role_has_permissions);

            $users = admin_users::join('role', 'role.id', '=', 'admin_users.role_id')->where('admin_users.id', '=', $user->id)->select('role.role_name as roleName')->first();

            // 🔥 NULL SAFETY
            $roleName = $users ? $users->roleName : null;

            if (!$roleName) {
                logger()->error('User logged in without role', [
                    'user_id' => $user->id,
                    'role_id' => $user->role_id
                ]);

                abort(403, 'Role not assigned to user');
            }

            session(['user_role' => $users->roleName, 'user_permissions' => json_encode($role_has_permissions)]);

        }
        return $next($request);
    }
}
