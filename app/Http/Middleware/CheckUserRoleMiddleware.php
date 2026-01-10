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

            session(['user_role' => $users->roleName, 'user_permissions' => json_encode($role_has_permissions)]);

        }
        return $next($request);
    }
}
