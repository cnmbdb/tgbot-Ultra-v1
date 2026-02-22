@extends('layouts.admin.app')
@section('nav-status-setting', 'active')
@section('nav-status-setting-config', 'active')
@section('style')
    <style>
        .layui-table td, .layui-table th{font-size: 16px;}
        .tip{
            border: 1px solid #16a8f8;
            border-radius: 5px;
            padding: 5px;
            background-color: aliceblue;
        }
        .layui-icon{
            color: #16a8f8;
            margin-right: 5px;
        }
        th.fu-title{font-weight: bold;}
        th.zhu-title{text-align: center;font-weight: bold;font-size: 18px;}
        .thumb-box{
            width: 150px;
            height: 150px;
            border: 1px solid #ccc;
            background: url('{{asset("admin/img/upload.png")}}') no-repeat top left;
            background-size:100% 100%;
        }
        .thumb{
            width: 150px;
            height: 150px;
            opacity: 0;
            cursor: pointer;
        }
        .layui-form-switch{margin-bottom: 10px;}
    </style>
    <link href="{{asset('admin/css/config.css')}}" rel="stylesheet">
@endsection
@section('contents')

    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <div class="layui-form-item bg_white">
                    <div class="layui-col-md10 layui-tab">
                        <ul class="layui-tab-title">
                            <li class="layui-this">基本配置</li>
                        </ul>
                        <div class="layui-tab-content">
                            <div class="layui-tab-item layui-show">
                                <form class="layui-form" id="config-form1" action="{{route('admin.setting.config.update')}}" method="POST">
                                    <input type="hidden" name="config_type" value="1">
                                    <table class="layui-table" lay-skin="nob">
                                        <tr>
                                            <th class="fu-title">job任务域名URL：</th>
                                            <td>
                                                <div>
                                                    <div class="title-key flex">
                                                        @php
                                                            $jobConfig = $data->firstWhere('config_key', 'job_url');
                                                            $jobUrl = $jobConfig ? $jobConfig->config_val->url : 'http://tgbot-job:9503';
                                                        @endphp
                                                        <input type="text" class="z-input" name="job_url[url]" autocomplete="off" value="{{$jobUrl}}">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <p class="tip"><i class="layui-icon layui-icon-tips"></i>{{$jobConfig ? $jobConfig->comments : '任务域名url'}}</p>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <th class="fu-title">ton支付接口url：</th>
                                            <td>
                                                <div>
                                                    <div class="title-key flex">
                                                        @php
                                                            $tonConfig = $data->firstWhere('config_key', 'ton_url');
                                                            $tonUrl = $tonConfig ? $tonConfig->config_val->url : 'http://host.docker.internal:4444/api/premium';
                                                        @endphp
                                                        <input type="text" class="z-input" name="ton_url[url]" autocomplete="off" value="{{$tonUrl}}">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <p class="tip"><i class="layui-icon layui-icon-tips"></i>{{$tonConfig ? $tonConfig->comments : 'ton支付接口url(不需要开通tg会员,用不到这个接口)'}}</p>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <th class="fu-title">API连接url：</th>
                                            <td>
                                                <div>
                                                    <div class="title-key flex">
                                                        @php
                                                            $apiWebConfig = $data->firstWhere('config_key', 'api_web_url');
                                                            $apiWebUrl = $apiWebConfig ? $apiWebConfig->config_val->url : 'http://host.docker.internal:4444/';
                                                        @endphp
                                                        <input type="text" class="z-input" name="api_web_url[url]" autocomplete="off" value="{{$apiWebUrl}}">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <p class="tip"><i class="layui-icon layui-icon-tips"></i>API连接url</p>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <button class="layui-btn layui-btn-nomal" lay-submit lay-filter="formDemo">保存</button>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!--操作:修改，删除-->
    <script>
        layui.use(['table', 'layer', 'element'], function () {
            var table = layui.table,
                element = layui.element,
                form = layui.form;


            // 表单提交
            form.on('submit(formDemo)', function(data){
                layer.load(1);
                $("#config-form"+data.field.config_type).ajaxSubmit({
                    'success':function(res){
                        layer.closeAll('loading');
                        if (res.code == 200) {
                            layer.msg('保存成功', {icon: 6});
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        } else {
                            layer.msg(res.msg, {icon: 5});
                        }
                    },
                    'error':function(res){
                        layer.closeAll('loading');
                        if (res.status == 422) {
                            var error = res.responseJSON.errors;
                            for(val in error) {
                                layer.msg(error[val][0], {icon: 5});
                                break;
                            }
                        } else {
                            layer.msg('修改失败');
                        }
                    }
                })

                return false;
            });

            // 缩略图预览
            $(".thumb").change(function(){
                var file=this.files[0] // 获取input上传的图片数据;
                var img=new Image() ;
                var url=window.URL.createObjectURL(file);
                $(this).parent().css({
                    'background-image':'url('+url+')',
                    'border':'none'
                });
            });
        });
    </script>
@endsection
