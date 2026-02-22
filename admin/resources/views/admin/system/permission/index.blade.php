@extends('layouts.admin.app')
@section('nav-status-system', 'active')
@section('nav-status-system-permission', 'active')
@section('style')
  <link rel="stylesheet" href="{{asset('vendor/dtree/dtree.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/dtree/font/dtreefont.css')}}">
@endsection
@section('contents')
    <div class="main">
        @section('bread')
        @endsection
        <div class="head" style="padding:10px;">
            <button class="layui-btn layui-btn-normal layui-btn-sm" dtree-id="demoTree" dtree-menu="addRoot">添加根节点</button>
        </div>
        <div class="permission-tree">
            <div id="demoTree"></div>
        </div>
    </div>
    <div id="form">
        
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
                url: "{{route('admin.system.permission.get_data')}}" ,// 使用url加载（可与data加载同时存在）
                line: true,
                initLevel: "1",
                ficon: ["0"],
                toolbar:true,
                menubar:true,
                menubarTips:{
                    group:[],  // 你也可以在这里指定这个方法
                    freedom:[{
                        menubarId:"addRoot",
                        handler:function(node, $div){
                            html = '<div class="layui-form-item  layui-form-text ">';
                            html += '';
                            html += '<div class="layui-input-inline tool_input">';
                            html += '<input class="layui-input" id="node_name" value="" placeholder="请输入节点名称" >';
                            html += '</div></div>';
                            layer.open({
                                title:'添加根节点',
                                content:html,
                                btn:['确认'],
                                yes:function(index){
                                   var name = $("#node_name").val();
                                   $.post('{{route("admin.system.permission.add")}}', {context:name}, function(res){
                                       if (res.code == 200) {
                                        layer.close(index);
                                        // 重新加载树
                                        DemoTree.reload("demoTree",{
                                            url: "{{route('admin.system.permission.get_data')}}",
                                            initLevel: "1",
                                            success: function(data, obj){
                                                // obj.attr("data-id","-1"); // 修改ul的data-id
                                            }
                                        });
                                       } else {
                                           layer.msg(res.msg);
                                       }
                                   })
                                    
                                }

                            })
                        // jsonData.push(json);  // 添加数据
                        // dtree.reload("menubarTree5", {data: jsonData});  // 重载树
                        }
                    }]
                },
                toolbarStyle:{
                    title:'权限'
                },
                toolbarBtn:[
                    [
                        {"label":"路由地址","name":"route","type":"text"},

                    ],
                    [
                        {"label":"路由地址","name":"route","type":"text"},
                    ]
                ],
                toolbarFun:{
                    editTreeLoad: function(treeNode){
                        // 默认值
                        $.ajax({
                            type:'get',
                            data:treeNode,
                            url:'{{route('admin.system.permission.get_item')}}',
                            success: function(res){
                                console.log(res.data.route)
                                var param = {
                                    level:treeNode.level, 
                                    route:res.data.route
                                };
                                // 这里的param格式为：  {level:treeNode.level, test:"3"};
                                DemoTree.changeTreeNodeDone(param); // 配套使用
                            }
                        }) 
                    },
                    // 增加
                    addTreeNode: function(treeNode, $div){
                        console.log(treeNode)
                        console.log($div)
                        $.ajax({
                            type:'post',
                            data:treeNode,
                            url:'{{route("admin.system.permission.add")}}',
                            success:function(res){
                                console.log(res)
                                if (res.code == 200) {
                                    layer.msg(res.msg);
                                    DemoTree.changeTreeNodeAdd(res.data.id); // 添加成功，返回ID
                                    DemoTree.changeTreeNodeAdd(true); // 添加成功
                                    DemoTree.changeTreeNodeAdd(treeNode); // 添加成功，返回一个JSON对象
                                    DemoTree.changeTreeNodeAdd("refresh"); // 添加成功，局部刷新树
                                } else {
                                    tip_error(res.msg);
                                }
                                
                            }
                        })
                    
                    },
                    // 修改
                    editTreeNode: function(treeNode, $div){
                        $.ajax({
                            type: "post",
                            data: treeNode,
                            url: "{{route('admin.system.permission.update')}}",
                            success: function(res){
                                if (res.code == 200) {
                                    layer.msg(res.msg);
                                    DemoTree.changeTreeNodeEdit(true);// 修改成功
                                    DemoTree.changeTreeNodeEdit(result.param); // 修改成功，返回一个JSON对象
                                } else {
                                    tip_error(res.msg);
                                }
                            },
                            error: function(){
                                DemoTree.changeTreeNodeEdit(false);//修改失败
                            }
                        });
                    },
                    // 删除
                    delTreeNode: function(treeNode, $div){
                        console.log(treeNode);
                        $.ajax({
                            type: "get",
                            data: treeNode,
                            url: "{{route('admin.system.permission.del')}}",
                            success: function(res){
                                if (res.code == 200) {
                                    layer.msg(res.msg);
                                    DemoTree.changeTreeNodeDel(true); // 删除成功
                                } else {
                                    tip_error(res.msg);
                                }
                            },
                            error: function(){
                                tip_error('error');
                                DemoTree.changeTreeNodeDel(false);// 删除失败
                            }
                        });
                    }
                }
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