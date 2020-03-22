/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/11/16
 * 小程序 支付配置
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var saa_key = sessionStorage.getItem('saa_key');
        var config_type = 'miniprogrampay';
        form.render();

        var url = baseUrl + '/merchantConfig' + '?key=' + saa_key;
        $.ajax({
            url: url + '&config_type=' + config_type,
            type: 'get',
            async: false,
            headers: headers,
            beforeSend: function () {
                loading = layer.load(loadType, loadShade);//显示加载图标
            },
            success: function (res) {
                layer.close(loading);//关闭加载图标
                if (res.status == timeOutCode) {
                    layer.msg(timeOutMsg);
                    admin.exit();
                    return false;
                }
                if (res.status == 500) {
                    layer.msg(res.message, {icon: 1, time: 1000});
                    return false;
                }
                if (!res.data) {
                    return false;
                }
                $("input[name=app_id]").val(res.data.app_id);
                $("input[name=mch_id]").val(res.data.mch_id);
                $("input[name=pay_key]").val(res.data.pay_key);
            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);
            }
        })

        //监听提交
        form.on('submit(sub)', function (data) {
            var json = {
                app_id: data.field.app_id,
                mch_id: data.field.mch_id,
                pay_key: data.field.pay_key,
                config_type: config_type,
                key: saa_key
            };
            $.ajax({
                url: url,
                type: 'put',
                async: false,
                data: json,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    layer.close(loading);//关闭加载图标
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    if (res.status != 200) {
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg(sucMsg.put);
                    layer.close(openIndex);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

    });

    exports('miniProgram/pay', {})
});