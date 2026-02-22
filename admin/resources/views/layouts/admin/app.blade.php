<!DOCTYPE html>
<html lang="en">

@include('layouts.admin._head')
@include('common.tools')
@include('common.modal')

@yield('style')

<body>
    <div>
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