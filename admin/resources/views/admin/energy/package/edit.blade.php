@extends('layouts.admin.app')
@section('nav-status-distribution', 'active')
@section('style')
    <style>
        /* head */
        .head-title{
            padding: 5px;
        }
        .layui-form-label{
            width: 170px;
        }
        .kv-file-upload, .kv-file-zoom,.fileinput-upload-button{
            display: none;
        }
        .thumb-box{
            width: 150px;
            height: 150px;
            border: 1px solid #ccc;
            background: url('{{asset("images/upload.png")}}') no-repeat top left;
            background-size:contain;
        }
        #thumb{
            width: 150px;
            height: 150px;
            opacity: 0;
        }
    </style>
@endsection
@section('contents')
    <div class="widget-body">
        <div class="widget-main" style="padding:0;">
            @section('bread')
                <div class="breadcrumbs" id="breadcrumbs">
                    <ul class="breadcrumb">
                        <li>
                            <i class="menu-icon ace-icon fa fa-users"></i>
                            <a href="">能量套餐</a>
                        </li>
                        <li class="active">编辑</li>
                    </ul>
                </div>
            @endsection
            <div class="form-box">
                <form class="layui-form" action="{{route('admin.energy.package.update')}}" id="add_news" name="add_news"  method="POST">
                    
                    <div class="layui-form-item" style="margin-top:10px">
                        <label class="layui-form-label">机器人名称：</label>
                        <div class="layui-col-md5"  style="margin-top:10px">
                            <span >{{$data->bot_username}}</span>
                        </div>
                    </div>
                    
                    <div class="layui-form-item">
                        <label class="layui-form-label">套餐类型：</label>
                        <div class="layui-col-md5">
                            <select name="package_type" lay-filter="package_type"  id="package_type" lay-verify="required">
                                @foreach ($PackageType as $k => $val)
                                    <option value="{{$k}}" @if ($data->package_type == $k) selected @endif>{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="layui-form-item">
                            <label class="layui-form-label">套餐名称：</label>
                            <div class="layui-col-md5">
                                <input type="text" name="package_name"  autocomplete="off" class="layui-input" value="{{$data->package_name}}">
                            </div>
                    </div>
                    
                    <div class="layui-form-item">
                        <label class="layui-form-label">套餐图片：</label>
                        <div class="layui-col-md5">
                            <div class="thumb-box">
                                <input type="file" name="thumb" id="thumb" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="layui-form-item">
                            <label class="layui-form-label">套餐量：</label>
                            <div class="layui-col-md5">
                                <input type="text" name="energy_amount"  autocomplete="off" class="layui-input" value="{{$data->energy_amount}}">
                            </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">套餐期限：</label>
                        <div class="layui-col-md5">
                            <select name="energy_day" lay-filter="energy_day"  id="energy_day" lay-verify="required">
                                @foreach ($EnergyDay as $k => $val)
                                    <option value="{{$k}}" @if ($data->energy_day == $k) selected @endif>{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                            <label class="layui-form-label">TRX售价：</label>
                            <div class="layui-col-md5">
                                <input type="text" name="trx_price"  autocomplete="off" class="layui-input" value="{{$data->trx_price}}">
                            </div>
                    </div>
                    <div class="layui-form-item">
                            <label class="layui-form-label">代理TRX售价：</label>
                            <div class="layui-col-md5">
                                <input type="text" name="agent_trx_price"  autocomplete="off" class="layui-input" value="{{$data->agent_trx_price}}">
                            </div>
                    </div>
                    <div class="layui-form-item">
                            <label class="layui-form-label">显示说明：</label>
                            <div class="layui-col-md5">
                                <textarea name="show_notes"  autocomplete="off" class="layui-textarea">{{$data->show_notes}}</textarea>
                            </div>
                    </div>
                    <div class="layui-form-item">
                            <label class="layui-form-label">排序：</label>
                            <div class="layui-col-md5">
                                <input type="text" name="seq_sn"  autocomplete="off" class="layui-input" value="{{$data->seq_sn}}">
                            </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block layui-input-block-fine-tuning">
                            <input type="hidden" name="rid" value="{{$data->rid}}">
                            <input type="hidden" name="bot_rid" value="{{$data->bot_rid}}">
                            <a class="layui-btn layui-btn-normal" href="javascript:history.back(-1)">返回</a>
                            <button class="layui-btn submit" lay-submit lay-filter="formDemo">提交</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!--<script src="{{ asset('js/fileinput.js')}}"></script>-->
    <!--<script src="{{ asset('js/jquery-form.js')}}"></script>-->
    <script>
        var store_type_arr = [];
        layui.use(['table', 'layer', 'laydate'], function () {
            var table = layui.table,
                $ = layui.$,
                form = layui.form;

            // 表单提交
            form.on('submit(formDemo)', function(data){
                var store = [];

                layer.load(1);
                $("#add_news").ajaxSubmit({
                    data:{
                        'store' : store,
                    },
                    'success':function(res){
                        layer.closeAll('loading');
                        if (res.code == 200) {
                            layer.msg(res.msg,{icon:6});
                            setTimeout(() => {
                                location.href= '{{route("admin.energy.package.index")}}';
                            }, 1000);
                            return false;
                        } else {
                            layer.msg(res.msg,{icon:5});
                            return false;
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
                            layer.msg('上传错误');
                        }
                        return false;
                    }
                });
                return false;
            });
        });
        
        var packagephoto = '{{$data->package_pic}}';
        if(packagephoto != ''){
           $(".thumb-box").css({
                'background-image':'url({{$data->package_pic}})',
                'border':'none'
            }); 
        }
        
        // 缩略图预览
        $("#thumb").change(function(){
            var file=this.files[0] // 获取input上传的图片数据;
            var img=new Image() ;
            var url=window.URL.createObjectURL(file);
            $(".thumb-box").css({
                'background-image':'url('+url+')',
                'border':'none'
            });
        });
    </script>

@endsection


