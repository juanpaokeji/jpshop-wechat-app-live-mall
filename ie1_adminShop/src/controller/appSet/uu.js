/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/8/13
 * 配置UU跑腿信息
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        var arr, res;
        form.render();

        var ajax_method = 'merchantUuAccount';

        //获取闪送信息
        arr = {
            method: ajax_method,
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data) {
            $("input[name=appid]").val(res.data.appid);
            $("input[name=appkey]").val(res.data.appkey);
            $("input[name=openid]").val(res.data.openid);
            form.render();
        }

        //设置闪送信息
        form.on('submit(setInfo)', function () {
            var subData = {
                appid: $("input[name=appid]").val(),
                appkey: $("input[name=appkey]").val(),
                openid: $("input[name=openid]").val()
            };

            //提交修改
            arr = {
                method: ajax_method,
                type: 'post',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
            }
        });

    });

    exports('appSet/uu', {})
});