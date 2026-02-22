<?php

namespace App\Http\Middleware;

use Closure;

class Login
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
        $userId = $request->session()->get('userId');

        // 登录部分暂时不做
        // session(['userId' => 1]);
        // $userId = 1;

        if (!$userId) {
            return redirect(url('user/login'), 302);
        }

        return $next($request);
    }
}
