<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 后台管理系统</title>
    <link href="/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="/admin/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="/admin/css/animate.css" rel="stylesheet">
    <link href="/admin/css/style.css" rel="stylesheet">
    <link href="/admin/css/ui-ux-pro-max.css" rel="stylesheet">
    @if (!empty($turnstileRequired) && !empty($turnstileSiteKey))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    <style>
        /* Override specific login styles */
        body {
            background: #ffffff !important;
        }
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
        }
        .login-box {
            background: #ffffff;
            border-radius: 24px;
            padding: 48px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
        .login-title {
            font-size: 24px;
            font-weight: 700;
            color: #1F2937;
            text-align: center;
            margin-bottom: 8px;
        }
        .login-subtitle {
            font-size: 14px;
            color: #6B7280;
            text-align: center;
            margin-bottom: 32px;
        }
        .input-group-modern {
            margin-bottom: 20px;
            position: relative;
        }
        .input-group-modern i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 18px;
        }
        .input-group-modern input {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s;
            background: #F9FAFB;
        }
        .input-group-modern input:focus {
            background: #FFFFFF;
            border-color: #4F46E5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        .btn-modern {
            width: 100%;
            padding: 12px;
            background: #00DC82;
            color: #003728;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }
        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(0, 220, 130, 0.4);
        }
        .turnstile-wrap {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }
        .err {
            color: #EF4444;
            font-size: 13px;
            margin-top: 8px;
            text-align: center;
            background: #FEF2F2;
            padding: 8px;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-box animated fadeInDown">
            <div class="login-title">后台管理系统</div>
            <div class="login-subtitle">TGBot Ultra Admin Panel</div>
            
            <form method="POST" action="{{ route('admin.login') }}">
                {{ csrf_field() }}
                
                <div class="input-group-modern">
                    <i class="fa fa-user"></i>
                    <input type="text" name="name" placeholder="账号" required value="{{old('name')}}">
                </div>
                
                <div class="input-group-modern">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="密码" required>
                </div>

                @if ($errors->has('name'))
                    <div class="err">{{ $errors->first('name') }}</div>
                @endif

                @if (!empty($turnstileRequired) && !empty($turnstileSiteKey))
                    <div class="turnstile-wrap">
                        <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-theme="light"></div>
                    </div>
                @endif

                <button type="submit" class="btn-modern">
                    登录系统
                </button> 
            </form>
        </div>
    </div>

    <!-- Mainly scripts -->
    <script src="/admin/js/jquery-3.1.1.min.js"></script>
    <script src="/admin/js/popper.min.js"></script>
    <script src="/admin/js/bootstrap.js"></script>
</body>
</html>
