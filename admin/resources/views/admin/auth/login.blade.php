<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录界面</title>
    <link href="/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="/admin/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="/admin/css/animate.css" rel="stylesheet">
    <link href="/admin/css/style.css" rel="stylesheet">
    <link href="/admin/css/login.css" rel="stylesheet">
</head>

<body class="gray-bg">
    <div>
        <div class="logo-name">后台管理系统</div>
    </div>
    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <form method="POST" action="{{ route('admin.login') }}">
                {{ csrf_field() }}
                <div class="main">
                    <div class="main-btn">
                        <div class="flex-center flex-justify-between">
                            <div class="icon-box flex-center">
                                <img src="img/user.png" class="user-icon">
                            </div>
                            <div class="flex1 flex-center">
                                <input type="test" name="name" placeholder="账号" required="" value="{{old('name')}}">
                            </div>
                        </div>
                        <div class="flex-center flex-justify-between">
                            <div class="icon-box flex-center">
                                <img src="img/pwd.png" class="pwd-icon">
                            </div>
                            <div  class="flex1 flex-center">
                                <input type="password" name="password" placeholder="密码" required="">
                            </div>
                        </div>
                    </div>
                    <div class="mian-bg"></div>
                </div>
                @if ($errors->has('name'))
                    <div class="err">{{ $errors->first('name') }}</div>
                @endif
                <button type="submit" class="btn login">
                    <div class="flex-center">
                        <div class="p-r-10">登</div><div class="p-l-10">录</div>
                    </div>
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