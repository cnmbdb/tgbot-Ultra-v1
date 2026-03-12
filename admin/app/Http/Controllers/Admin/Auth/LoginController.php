<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        return view('admin.auth.login', [
            'turnstileRequired' => $this->requiresTurnstile(request()),
            'turnstileSiteKey' => config('services.turnstile.site_key'),
        ]);
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
        if ($this->requiresTurnstile($request) && !$this->passesTurnstile($request)) {
            return back()->withErrors([
                $this->username() => '请先完成安全验证',
            ])->withInput($request->only($this->username()));
        }

        $data = Admin::where('name',$request->name)->first();
        if(empty($data)){
            return $this->failLogin($request);
        }else{
            if(!empty($data->white_ip)){
                $ip = getClientIP();
                $canLogin = Admin::where('name',$request->name)
                    ->where('status',1)
                    ->whereRaw("? = ANY(string_to_array(white_ip, ','))", [$ip])
                    ->first();
                if(empty($canLogin)){
                    return $this->failLogin($request);
                }
            }
            
            $admin = Admin::where('name', $request->name)->where('status', 1)->first();
            
            if ($admin) {
                $passwordMatch = false;
                if (strpos($admin->password, '$2y$') === 0) {
                    $passwordMatch = \Hash::check($request->password, $admin->password);
                } else {
                    $passwordMatch = (md5($request->password) === $admin->password);
                }
                
                if ($passwordMatch) {
                    Auth::guard('admin')->login($admin);
                    $this->clearTurnstileRequired($request);
                    DB::table('t_admin_login_log')->insert(['admin_name' => $request->name,'login_time' => nowDate(),'login_ip' => getClientIP()]);
                    return redirect($this->redirectTo);
                } else {
                    return $this->failLogin($request);
                }
            } else {
                return $this->failLogin($request);
            }
        }
    }

    protected function failLogin(Request $request)
    {
        $this->markTurnstileRequired($request);
        return $this->sendFailedLoginResponse($request);
    }

    protected function turnstileEnabled()
    {
        return config('services.turnstile.enable') 
            && !empty(config('services.turnstile.site_key')) 
            && !empty(config('services.turnstile.secret_key'));
    }

    protected function turnstileCacheKey(Request $request)
    {
        return 'admin_login_turnstile_required:'.sha1((string) $request->ip());
    }

    protected function requiresTurnstile(Request $request)
    {
        if (!$this->turnstileEnabled()) {
            return false;
        }

        // 始终显示 Turnstile（防暴力破解）
        return true;
    }

    protected function markTurnstileRequired(Request $request)
    {
        if (!$this->turnstileEnabled()) {
            return;
        }

        Cache::put($this->turnstileCacheKey($request), 1, now()->addHours(24));
    }

    protected function clearTurnstileRequired(Request $request)
    {
        if (!$this->turnstileEnabled()) {
            return;
        }

        Cache::forget($this->turnstileCacheKey($request));
    }

    protected function passesTurnstile(Request $request)
    {
        if (!$this->turnstileEnabled()) {
            return true;
        }

        $token = $request->input('cf-turnstile-response');
        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
            return (bool) data_get($response->json(), 'success', false);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
