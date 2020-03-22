/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/12/24  一直在更新，时间随时修改
 * js 绑定手机
 */

var t = 60;
var inter;
layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性

        //获取验证码
        form.on('submit(get_code)', function () {
            var phone = $('input[name=phone]').val();
            //发送验证码之前判断该手机是否已注册
            $.ajax({
                url: baseUrl + '/merchantUsers?phone=' + phone,
                type: 'get',
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);//关闭加载图标
                    //返回码为200的时候，该手机被注册，否则可继续执行其他操作
                    if (res.status === 200) {
                        layer.msg('该手机号已绑定其他账号', {icon: 1, time: 2000});
                        return false;
                    } else {
                        //发送验证码
                        arr = {
                            method: 'merchantsSmsCode',
                            type: 'get',
                            data: {phone: phone}
                        };
                        res = getAjaxReturn(arr);
                        if (res) {
                            layer.msg('验证码已发送，请注意查收', {icon: 1, time: 2000});
                            $('.code').html('<span>' + t + ' 秒后可重新获取</span>');
                            inter = setInterval("getCode()", 1000);
                        }
                    }
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            });
        });

        //执行保存操作
        form.on('submit(sub)', function () {
            var phone = $('input[name=phone]').val();
            var code = $('input[name=code]').val();
            arr = {
                method: 'updatePhone',
                type: 'put',
                data: {
                    phone: phone,
                    code: code
                }
            };
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg('手机绑定成功', {icon: 1, time: 2000}, function () {
                    sessionStorage.setItem('is_bind_phone', '1');
                    admin.exit();
                });
            }
        })

    });
    exports('user/phone', {})
});

//验证码获取后倒计时
function getCode() {
    t--;
    $('.code').html('<span>' + t + ' 秒后可重新获取</span>');
    if (t <= 0) {
        $('.code').html('<a lay-submit lay-filter="get_code" href="javascript: void(0)" style="color: dodgerblue;">获取验证码</a>');
        clearInterval(inter);
    }
}
