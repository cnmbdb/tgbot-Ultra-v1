<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Route;
use App\Models\Admin\Admin;

class PermissionAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('admin')->user();
        // 超级管理员
        if ($user->hasRole('超级管理员')) {
            return $next($request);
        }
        $route = Route::currentRouteName();
        $permission = Permission::where('route', $route)->first();
        // 判断权限表中这条路由是否需要验证
        if ($permission) {
            if (! $user->hasPermissionTo($permission->id)) {
                // return response()->view('errors.403', ['status' => "权限不足，需要：{$permission->name}权限"]);
                abort(403, '没有权限');
            }
        }
        return $next($request);
    }
}
