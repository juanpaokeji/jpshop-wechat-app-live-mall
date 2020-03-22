/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/8/13
 * 配置闪送信息
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        var arr, res;
        var operationId;
        var route = 'shansong';
        form.render();

        var ajax_method = 'merchantUnits';

        //获取闪送信息
        arr = {
            method: ajax_method,
            type: 'get',
            data: {route: route}
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data) {
            operationId = res.data.id;
            if (res.data.config) {
                $("input[name=md5]").val(res.data.config.md5);
                $("input[name=m_id]").val(res.data.config.m_id);
                $("input[name=partnerNo]").val(res.data.config.partnerNo);
                $("input[name=token]").val(res.data.config.token);
                $("input[name=mobile]").val(res.data.config.mobile);
                form.render();
            }
        }

        //设置闪送信息
        form.on('submit(setInfo)', function () {
            var subData = {
                md5: $("input[name=md5]").val(),
                m_id: $("input[name=m_id]").val(),
                partnerNo: $("input[name=partnerNo]").val(),
                token: $("input[name=token]").val(),
                mobile: $("input[name=mobile]").val(),
                route: route
            };

            //提交修改
            arr = {
                method: ajax_method + '/' + operationId,
                type: 'put',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
            }
        });

    });

    exports('appSet/shansong', {})
});