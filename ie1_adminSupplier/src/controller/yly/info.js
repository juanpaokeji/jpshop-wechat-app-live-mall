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
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        var operationId;
        var arr, res;
        form.render();

        /*diy设置开始*/
        //页面不同属性
        var ajax_method = 'supplierYlyPrint';//新ajax需要的参数 method

        //获取小票机信息
        arr = {
            method: ajax_method,
            type: 'get'
        };
        res = getAjaxReturn(arr);
        if (res && res.data) {
            operationId = res.data.id;
            if (res.data.yly_config)  {
                var yly_config = res.data.yly_config;
                $("input[name=name]").val(yly_config.name);
                $("input[name=apikey]").val(yly_config.apikey);
                $("input[name=machine_code]").val(yly_config.machine_code);
                $("input[name=msign]").val(yly_config.msign);
                $("input[name=partner]").val(yly_config.partner);
            }
        }

        //执行编辑
        form.on('submit(sub)', function () {
            arr = {
                method: ajax_method + '/' + operationId,
                type: 'put',
                data: {
                    yly_config: {
                        name: $('input[name=name]').val(),
                        apikey: $('input[name=apikey]').val(),
                        machine_code: $('input[name=machine_code]').val(),
                        msign: $('input[name=msign]').val(),
                        partner: $('input[name=partner]').val()
                    }
                }
            };
            var res = getAjaxReturn(arr);
            if (res) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
            }
        });

    });
    exports('yly/info', {})
});
