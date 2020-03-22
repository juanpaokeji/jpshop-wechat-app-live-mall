/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/8/10  一直在更新，时间随时修改
 * js model
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
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
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var saa_key = sessionStorage.getItem('saa_key');
        form.render();

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantShopConfig";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        /*diy设置结束*/

        //获取配置信息
        $.ajax({
            url: url + key,
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
                if (res.status != 200) {
                    if (res.status != 204) {
                        layer.msg(res.message);
                    }
                    return false;
                }
                var data = res.data;
                if (!data) {
                    return;
                }
                if (data.is_large_scale == 1) {
                    $("input[name=is_large_scale]").prop('checked', true);
                } else {
                    $("input[name=is_large_scale]").removeAttr('checked');
                }
                $('input[name=number]').val(data.number);
                form.render();
            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);//关闭加载图标
            }
        })

        //设置商城设置
        form.on('submit(sub)', function () {
            var is_large_scale = 0;
            if ($('input[name=is_large_scale]:checked').val()) {
                is_large_scale = 1;
            }
            var subData = {
                is_large_scale: is_large_scale,
                number: $('input[name=number]').val(),
                key: saa_key
            };
            //提交修改
            $.ajax({
                url: url,
                data: subData,
                type: 'put',
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    // if ($.type(res) == 'string') {//判断是否为字符串，只有这个地方需要判断，很诡异
                    //     res = $.parseJSON(res);
                    // }
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
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

    })
    exports('shopConfig', {})
});
