<div class="navbar-header">
    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
    <form role="search" class="navbar-form-custom" action="search_results.html">
        <!--<div class="form-group">-->
             <!--<input type="text" placeholder="Search for something..." class="form-control" name="top-search" id="top-search"> -->
        <!--</div>-->
        <li style="padding: 20px;">
            <span>TRX机器人-管理后台</span>
        </li>
    </form>
</div>
<ul class="nav navbar-top-links navbar-right">
    <li style="padding: 20px">
        <span >JOB任务状态(10S检查一次)：</span>
        <span id="jobstatus" name="jobstatus" style="color:red;">10秒后检测</span>
    </li>
    <li >
        <a class="m-r-sm fa fa-question-circle" onclick="javascript:confirm_opt(()=>{form_func('{{route("admin.setting.config.clearjobcache")}}');});">清理JOB缓存</a>
    </li>
    <li style="padding: 20px">
        <span class="m-r-sm fa fa-expeditedssl" style="cursor:pointer;" onclick="javascript:tools_add('修改密码',
            {
                'oldpassword':['原密码','password','', '原密码'],
                'xinpassword':['新密码','password','', '新密码'],
                'qrpassword':['确认密码','password','', '确认密码'],
            },
            '{{route("admin.system.admin.change_password")}}');">修改密码</span>
    </li>
    <li>
        <a href="javascript:;" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            <i class="fa fa-sign-out"></i> 退出
        </a>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </li>
</ul>

<script>
    var check_job = setInterval('checkjob()', 10000); //10秒检查一次
    function checkjob(){
		$.ajax({
			type: 'get',
			url:  '{{route("admin.setting.config.checkjob")}}',
			async : false,
			success:function(data){
			    $('#jobstatus').text("✅" + data.msg);
			},
			error:function(data){
			    $('#jobstatus').text("❌" + data.msg);
			}
		})
	}
</script>