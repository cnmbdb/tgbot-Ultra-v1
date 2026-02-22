<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\AdminLoginLog;
use App\Models\Admin\Admin;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
         $this->middleware('admin.guest')->except('logout','admin.logout');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard('admin')->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/admin/login');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'name';
    }

    public function login(Request $request)
    {
        // 验证IP
        $data = Admin::where('name',$request->name)->first();
        if(empty($data)){
            return $this->sendFailedLoginResponse($request);
        }else{
            if(!empty($data->white_ip)){
                $ip = getClientIP();
                // PostgreSQL 兼容：使用 string_to_array 和 ANY 替代 MySQL 的 FIND_IN_SET
                $canLogin = Admin::where('name',$request->name)
                    ->where('status',1)
                    ->whereRaw("? = ANY(string_to_array(white_ip, ','))", [$ip])
                    ->first();
                if(empty($canLogin)){
                    return $this->sendFailedLoginResponse($request);
                }
            }
            
            // 检查密码（支持 MD5 和 bcrypt）
            $admin = Admin::where('name', $request->name)->where('status', 1)->first();
            
            if ($admin) {
                $passwordMatch = false;
                // 检查是否是 bcrypt 格式（以 $2y$ 开头）
                if (strpos($admin->password, '$2y$') === 0) {
                    // 使用 bcrypt 验证
                    $passwordMatch = \Hash::check($request->password, $admin->password);
                } else {
                    // 使用 MD5 验证
                    $passwordMatch = (md5($request->password) === $admin->password);
                }
                
                if ($passwordMatch) {
                    // 手动登录
                    Auth::guard('admin')->login($admin);
                    DB::table('t_admin_login_log')->insert(['admin_name' => $request->name,'login_time' => nowDate(),'login_ip' => getClientIP()]);
                    // 用户存在，已激活且未被禁用。
                    return redirect($this->redirectTo);
                } else {
                    return $this->sendFailedLoginResponse($request);
                }
            } else {
                return $this->sendFailedLoginResponse($request);
            }
        }
    }

}
