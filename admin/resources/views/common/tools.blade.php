<script type="text/javascript">
    //向服务器查询 查询后jump
    function form_jump(url, form_data, jump) {
        var index = layer.msg('请求服务器中...', {icon: 16, time: 0, shade: 0.5});
        jQuery.comm.sendmessage(url, form_data,
            data => {
                layer.close(index);
                // var datax = eval("(" + data + ")");
                if (data.code == 200) {
                    tip_ok();
                    if (jump != undefined) {
                        setTimeout(function () {
                            window.location.href = jump;
                        }, 1500);
                    }
                } else {
                    tip_error(data.message);
                }
            },
            //ajax error
            data => {
                layer.close(index);
                tip_error(data.responseText);
            });
    }

    //向服务器查询  查询后执行func
    function form_func(url, form_data, func) {
        var index = layer.msg('请求服务器中...', {icon: 16, time: 0, shade: 0.5});
        jQuery.comm.sendmessage(url, form_data,
            data => {
                layer.close(index);
                // var datax = eval("(" + data + ")");
                if (data.code == 200) {
                    tip_ok();
                    if (undefined != func)
                        func(data.data);
                } else {
                    console.log(data)
                    if (data.code == 422){
                        tip_errors(data.msg)
                    } else{
                        tip_error(data.msg);
                    }

                }
            },
            //ajax error
            data => {
                layer.close(index);
                // var msg = Object.values(data.responseJSON.errors).join('\n\t');
                var msg = Object.values(data.responseJSON.errors)[0][0];
                tip_error(msg);
            });
    }

    //成功提示，1.5秒后消失
    function tip_ok(msg) {
        if (undefined == msg)
            msg = "操作成功!";
        swal({
            type: 'success',
            title: msg,
            showConfirmButton: false,
            timer: 1000
        });
    }

    //失败提示，带错误消息，点击ok关闭
    function tip_error(msg) {
        if (undefined == msg)
            msg = '操作失败！';
        swal({
            type: 'error',
            title: msg,
            timer:1500,
            showConfirmButton: true,
        })
    }

    //失败提示，带错误消息，点击ok关闭
    function tip_errors(msg) {
        console.log(typeof msg);
        let content='';
        for(let i in msg){
            content += msg[i][0] +'  '
        }
        console.log(content);
        swal({
            type: 'error',
            title: '操作失败！',
            text:content,
            showConfirmButton: false,
        })
    }

    //确认提示,如果自定义title时，注意cancel_func需要传null
    function confirm_opt(confirm_func, cancel_func, msg) {
        msg = (msg == undefined) ? '确认操作吗？' : msg;
        swal({
            title: msg,
            type: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#999',
            confirmButtonText: '确定!',
            cancelButtonText: '取消'
        }).then(function (res) {
            if (res.value) {
                confirm_func();
            } else {
                if (cancel_func != undefined)
                    cancel_func();
            }
        })

    }

    //将数字（整数）转为汉字，从零到一亿亿，需要小数的可自行截取小数点后面的数字直接替换对应arr1的读法就行了
    function convertToChinaNum(num) {
        var arr1 = new Array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        var arr2 = new Array('', '十', '百', '千', '万', '十', '百', '千', '亿', '十', '百', '千','万', '十', '百', '千','亿');//可继续追加更高位转换值
        if(!num || isNaN(num)){
            return "零";
        }
        var str1 = num.toString().split("")
        var str1 = num.toString().split(".")[0];
        var str2 = num.toString().split(".")[1];

        var result = "";
        for (var i = 0; i < str1.length; i++) {
            var des_i = str1.length - 1 - i;//倒序排列设值
            result = arr2[i] + result;
            var arr1_index = str1[des_i];
            result = arr1[arr1_index] + result;
        }
        //将【零千、零百】换成【零】 【十零】换成【十】
        result = result.replace(/零(千|百|十)/g, '零').replace(/十零/g, '十');
        //合并中间多个零为一个零
        result = result.replace(/零+/g, '零');
        //将【零亿】换成【亿】【零万】换成【万】
        result = result.replace(/零亿/g, '亿').replace(/零万/g, '万');
        //将【亿万】换成【亿】
        result = result.replace(/亿万/g, '亿');
        //移除末尾的零
        result = result.replace(/零+$/, '')
        //将【零一十】换成【零十】
        //result = result.replace(/零一十/g, '零十');//貌似正规读法是零一十
        //将【一十】换成【十】
        result = result.replace(/^一十/g, '十');
        return result;
    }

    function convertCurrency(money) {
        //汉字的数字
        // var cnNums = new Array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
        var cnNums = new Array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        //基本单位
        var cnIntRadice = new Array('', '十', '百', '千');
        //对应整数部分扩展单位
        var cnIntUnits = new Array('', '万', '亿', '兆');
        //对应小数部分单位
        var cnDecUnits = new Array('角', '分', '毫', '厘');
        //整数金额时后面跟的字符
        var cnInteger = '整';
        //整型完以后的单位
        var cnIntLast = '元';
        //最大处理的数字
        var maxNum = 999999999999999.9999;
        //金额整数部分
        var integerNum;
        //金额小数部分
        var decimalNum;
        //输出的中文金额字符串
        var chineseStr = '';
        //分离金额后用的数组，预定义
        var parts;
        if (money == '') { return ''; }
        money = parseFloat(money);
        if (money >= maxNum) {
            //超出最大处理数字
            return '超出范围';
        }
        if (money == 0) {
            chineseStr = cnNums[0];
            return chineseStr;
        }
        //转换为字符串
        money = money.toString();
        if (money.indexOf('.') == -1) {
            integerNum = money;
            decimalNum = '';
        } else {
            parts = money.split('.');
            integerNum = parts[0];
            decimalNum = parts[1].substr(0, 2);
        }
        //获取整型部分转换
        if (parseInt(integerNum, 10) > 0) {
            var zeroCount = 0;
            var IntLen = integerNum.length;
            for (var i = 0; i < IntLen; i++) {
            var n = integerNum.substr(i, 1);
            var p = IntLen - i - 1;
            var q = p / 4;
            var m = p % 4;
            if (n == '0') {
                zeroCount++;
            } else {
                if (zeroCount > 0) {
                chineseStr += cnNums[0];
                }
                //归零
                zeroCount = 0;
                chineseStr += cnNums[parseInt(n)] + cnIntRadice[m];
            }
            if (m == 0 && zeroCount < 4) {
                chineseStr += cnIntUnits[q];
            }
            }
            // chineseStr += cnIntLast;
        } else {
            chineseStr += cnNums[0];
        }
        //小数部分
        if (decimalNum != '') {
            chineseStr += '点';
            var decLen = decimalNum.length;
            for (var i = 0; i < decLen; i++) {
            var n = decimalNum.substr(i, 1);
            if (n != '0') {
                chineseStr += cnNums[Number(n)];
            }
            }
        }
        if (chineseStr == '') {
            chineseStr += cnNums[0];
        } else if (decimalNum == '') {
            // chineseStr;
        }
        return chineseStr;
        }
</script>
<script src="/admin/js/accounting.min.js"></script>