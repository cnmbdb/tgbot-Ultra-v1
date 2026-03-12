<!DOCTYPE html>
<html lang="en">

@include('layouts.admin._head')
@include('common.tools')
@include('common.modal')

@yield('style')
<style>
    body.admin-module-body { background: #f3f4f6; margin: 0; font-family: inherit; }
    .admin-module-wrap { padding: 24px; max-width: 960px; margin: 0 auto; min-height: 100vh; box-sizing: border-box; }
    .admin-module-wrap .page-title { font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 24px; }
</style>

<body class="admin-module-body">
    <div class="admin-module-wrap">
        @yield('contents')
    </div>

    <!-- Mainly scripts -->
    <script src="{{asset('admin/js/jquery-form.js')}}"></script>
    <script src="{{asset('admin/js/popper.min.js')}}"></script>
    <script src="{{asset('admin/js/bootstrap.js')}}"></script>
    <script src="{{asset('admin/js/plugins/metisMenu/jquery.metisMenu.js')}}"></script>
    <script src="{{asset('admin/js/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>

    <!-- Custom and plugin javascript -->
    <!--<script src="{{asset('admin/js/inspinia.js')}}"></script>-->
    <script src="{{asset('admin/js/plugins/pace/pace.min.js')}}"></script>
    
    @yield('scripts')
</body>
</html>