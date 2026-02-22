@extends('layouts.admin.app')
@section('nav-status-system', 'active')
@section('nav-status-system-role', 'active')
@section('style')
  <link rel="stylesheet" href="{{asset('vendor/dtree/dtree.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/dtree/font/dtreefont.css')}}">
@endsection
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <div class="widget-toolbox padding-8 clearfix" style="background-color: #fff;">
                    <div class="permission-tree">
                        <div id="demoTree"></div>
                    </div>
                    <div class="" style="padding: 20px 50px;">
                        <button class="layui-btn layui-btn-default layui-btn-sm submit">提交</button>
                        <a href="{{route('admin.system.role.index')}}" class="layui-btn layui-bg-blue layui-btn-sm">返回</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
@section('scripts')
<script src="{{asset('vendor/dtree/dtree.js')}}"></script>
<script>
     layui.extend({
            dtree: '{{asset("vendor/dtree/dtree")}}'   // {/}的意思即代表采用自有路径，即不跟随 base 路径
            }).use(['dtree','layer','jquery'], function(){
            var dtree = layui.dtree, layer = layui.layer, $ = layui.jquery;

            // 初始化树
            var DemoTree = dtree.render({
                elem: "#demoTree",
                iconfont: ["dtreefont", "layui-icon"], // 使用dtreefont和layui-icon图标库。
                url: "{{route('admin.system.role.permission_data')}}?id={{request('id')}}" ,// 使用url加载（可与data加载同时存在）
                line: true,
                initLevel: "2",
                ficon: ["0"],
                checkbar: true,
                checkbarType: "all" ,
                checkbarData:"choose"
            });

            $(".submit").click(function(){
                var index = layer.load();
                var params = dtree.getCheckbarNodesParam("demoTree");
                data = [];
                $(params).each(function(){
                //    if (this.parentId != 0) {
                        data.push({
                            id:this.nodeId,
                            initchecked:this.initchecked
                        });
                    // }
                });
                console.log(data);
                // return;
                if (data.length == 0) {
                    layer.close(index);
                    layer.msg('未做任何修改');
                    return;
                }
                $.ajax({
                    type:'post',
                    data:{
                        permissions:JSON.stringify(data),
                        id:{{request('id')}}
                    },
                    url:'{{route("admin.system.role.change_permission")}}',
                    success:function(res){
                        layer.close(index);
                        if (res.code == 200) {
                            tip_ok(res.msg);
                        } else {
                            tip_error(res.msg);
                        }
                    },
                    error:function(res){
                        layer.close(index);
                        tip_error('系统错误');
                    }
                })
            });


            // 绑定节点点击
            dtree.on("node('demoTree')" ,function(obj){
                if(!obj.param.leaf){
                    var $div = obj.dom;
                    DemoTree.clickSpread($div);  //调用内置函数展开节点
                }
                // layer.msg(JSON.stringify(obj.param));
            });
        });


</script>

@endsection
