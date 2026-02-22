<script id="tools_tpl" type="text/html">
    <div class="layui-form-item @{{if type=='textarea' }} layui-form-text @{{/if}}" @{{if type=="hidden"}}
         style="display:none" @{{/if}}>
    <label class="layui-form-label tool_label"> ${introwords}： </label>
    <div class="layui-input-inline tool_input">@{{html chosehtml}}</div>
    </div>
</script>
<script type="text/javascript">
    /**
     * 所有页面中的ajax请求都走这里（table除外），不要自定义
     *  tools_add   弹出一个模态框，做新增，修改
     *              修改页面在modules中加入一个hidden字段，作为编辑的key
     *  form_jump   请求后 执行跳转（可以不跳）
     *  form_func   请求后 执行func函数
     *
     *  confirm_opt 确认提示框
     *              传入2个函数作为 确定 和 取消后的动作（取消可以不传）
     */
    function tools_add(title, modules, url, func) {
        swal({
            title: title,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText:'确认',
            cancelButtonText: '取消',
            width: 480,
            html: '<form class="layui-form" id="tools_form">' + '</form>',

            onOpen: () => {
                var dataItems = parse_module(modules);
                $('#tools_form').html($("#tools_tpl").tmpl(dataItems));
                layui.use(['table', 'layer', 'laydate', 'form'], function () {
                    var table = layui.table,
                        laydate = layui.laydate,
                        form = layui.form;
                    laydate.render({
                        elem: '#date'
                    });
                    laydate.render({
                        elem: '#datetime',type:'datetime'
                    });
                    //全选
                    form.render();
                    for (var i in modules) {
                        var moduel = modules[i];
                        if (moduel[1] == 'checkbox') {
                            form.on('checkbox(all)', function (obj) {
                                console.log(i);
                                $('input[name="' + i + '[]" ]').prop('checked', obj.elem.checked);
                                form.render('checkbox');
                            });
                            form.on('checkbox(check_item)', function (obj) {
                                var total = $(obj.elem).parent().find('input[name="' + i + '[]"]').length;
                                var checked_total = $(obj.elem).parent().find('input[name="' + i + '[]"]:checked').length;
                                if (total == checked_total) {
                                    $("#all").prop('checked', true);
                                    form.render();
                                } else {
                                    $("#all").prop('checked', false);
                                    form.render();
                                }
                            });
                            break;
                        }
                    }
                });
            },
            preConfirm: () => {
                var arr = $('#tools_form').serialize();
                return arr;
            }
        }).then(function (res) {
            if (res.value) {
                form_func(url, res.value, func);
            }
        }).catch(() => {

        });
    }

    function tools_html(title, html, url, func) {
        $.ajax({
            url: html,
            type: "POST",
            dataType: "html",
            success: function (result) {
                swal({
                    title: title,
                    focusConfirm: false,
                    showCancelButton: true,
                    cancelButtonText: 'cancel',
                    width: 450,
                    html: result,

                    onOpen: () => {
                        layui.use(['table', 'layer', 'laydate', 'form'], function () {
                            var table = layui.table,
                                laydate = layui.laydate,
                                form = layui.form;
                            laydate.render({
                                elem: '#date'
                            });
                            //全选
                            form.render();

                        });
                    },
                    preConfirm: () => {
                        var arr = $('#tools_form').serialize();
                        return arr;
                    }
                }).then(function (res) {
                    if (res.value) {
                        form_func(url, res.value, func);
//                console.log(res.value);
                    }
                }).catch(() => {

                });
            }
        });

    }

    function tools_download(title, modules, url) {
        swal({
            title: title,
            focusConfirm: false,
            showCancelButton: true,
            cancelButtonText: 'cancel',
            width: 450,
            html: '<form class="layui-form" id="tools_form">' + '</form>',

            onOpen: () => {
                var dataItems = parse_module(modules);
                $('#tools_form').html($("#tools_tpl").tmpl(dataItems));
                layui.use(['table', 'layer', 'laydate', 'form'], function () {
                    var table = layui.table,
                        laydate = layui.laydate,
                        form = layui.form;
                    laydate.render({
                        elem: '#date'
                    });
                    laydate.render({
                        elem: '#datetime',type:'datetime'
                    });
                    //全选
                    form.render();
                    for (var i in modules) {
                        var moduel = modules[i];
                        if (moduel[1] == 'checkbox') {
                            form.on('checkbox(all)', function (obj) {
                                console.log(i);
                                $('input[name="' + i + '[]" ]').prop('checked', obj.elem.checked);
                                form.render('checkbox');
                            });
                            form.on('checkbox(check_item)', function (obj) {
                                var total = $(obj.elem).parent().find('input[name="' + i + '[]"]').length;
                                var checked_total = $(obj.elem).parent().find('input[name="' + i + '[]"]:checked').length;
                                if (total == checked_total) {
                                    $("#all").prop('checked', true);
                                    form.render();
                                } else {
                                    $("#all").prop('checked', false);
                                    form.render();
                                }
                            });
                            break;
                        }

                    }


                });
            },
            preConfirm: () => {
                var arr = $('#tools_form').serialize();
                return arr;
            }
        }).then(function (res) {
            if (res.value) {
                var href = url+'?'+res.value;
                console.log(href);
                window.location.href = href;
            }
        }).catch(() => {

        });
    }

    function parse_module(modules) {
        var dataItems = [];
        for (var index in modules) {
            var chosehtml = '';
            var module = modules[index];
            switch (module[1]) {
                case 'radio':
                    var i = 0;
                    for (var key in module[2][0]) {
                        if (module[3] == key) {
                            checkhtml = 'checked';
                        } else {
                            var checkhtml = i == 0 ? 'checked' : '';
                        }
                        var disable = module[4]?'disabled':'';
                        chosehtml += '<input '+disable+' class="layui-input"  ' + checkhtml + ' name="' + index + '" type="radio" value="' + key + '" title="' + module[2][0][key] + '" />';
                        i++;
                    }
                    break;
                case 'checkbox':
                    chosehtml = '<input type="checkbox" lay-skin="primary" id="all" title="全选" lay-filter="all">';
                    var i = 0;
                    for (var key in module[2][0]) {
                        var checkboxhtml = '';
                        for (var j in module[3]) {
                            if (module[3][j]!=key){
                                continue
                            }else {
                                checkboxhtml = 'checked';
                            }
                        }

                        chosehtml += '<input type="checkbox" ' + checkboxhtml + ' name="' + index + '[]" value="' + key + '" lay-skin="primary" lay-filter="check_item" title="' + module[2][0][key] + '" />';
                        i++;
                    }
                    break;
                case 'switch':
                    chosehtml = '<input type="checkbox" name="' + index + '" value="' + module[2] + '" lay-skin="switch" lay-text="' + module[3] + '">';
                    break;
                case 'select':
                    chosehtml = '<select name="' + index + '">';
                    if (module[4]) {
                        console.log('disable')
                        chosehtml = '<select name="' + index + ' " disabled>';
                    }
                    chosehtml +='<option  value="">请选择</option>';
                    for (var key in module[2][0]) {
                        if (module[3] == key) {
                            var selectedhtml = 'selected="selected"';
                        } else {
                            var selectedhtml = '';
                        }
                        chosehtml += '<option ' + selectedhtml + ' value="' + key + '">' + module[2][0][key] + '</option>';
                        i++;
                    }
                    chosehtml += '</select>';
                    break;
                case 'date':
                    if(module[2] == 'null'){
                        module[2] = '';
                    }
                    chosehtml = '<input type="text" name="' + index + '" value="' + module[2] + '" id="date" lay-verify="date" placeholder="请输入时间" autocomplete="off" class="layui-input">';
                    break;
                case 'datetime':
                    if(module[2] == 'null'){
                        module[2] = '';
                    }
                    chosehtml = '<input type="text" name="' + index + '" value="' + module[2] + '" id="datetime" lay-verify="date" placeholder="请输入时间" autocomplete="off" class="layui-input">';
                    break;
                case 'text':
                    if (module[3]){
                        chosehtml = '<input class="layui-input" name="' + index + '" value="' + module[2] + '" placeholder="'+ module[3] +'" autocomplete="off"/>';
                    }else{
                        chosehtml = '<input class="layui-input" name="' + index + '" value="' + module[2] + '" />';
                    }
                    break;
                case 'textarea':
                    if(module[2] == 'null'){
                        module[2] = '';
                    }
                    chosehtml = '<textarea name="' + index + '" placeholder="' + module[3] + '" class="layui-textarea">' + module[2] + '</textarea>';
                    break;
                case 'password':
                    chosehtml = '<input type="password" name="' + index + '" value="" class="layui-input" placeholder="' + module[3] + '"  autocomplete="new-password"/>';
                    break;
                case 'hidden':
                    chosehtml = '<input type="hidden" name="' + index + '" value="' + module[2] + '" />';
                    break;
                case 'span':
                    chosehtml = '<span style="display: block;margin:9px 10px;font-size: 14px;">' + module[2] + '</span>';
                    break;
            }
            var dataItem = {'introwords': module[0], 'chosehtml': chosehtml, 'type': module[1]};
            dataItems.push(dataItem);
        }

        return dataItems;
    }
</script>