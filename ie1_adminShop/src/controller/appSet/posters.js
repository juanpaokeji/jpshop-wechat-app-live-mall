/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/10/21
 * 分享海报
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter', 'laydate'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var sucMsg = setter.successMsg;//成功提示 数组
        var openIndex;//定义弹出层，方便关闭
        var arr, res;
        var ajax_method = 'posters';//新ajax需要的参数 method
        var saa_key = sessionStorage.getItem('saa_key');
        form.render();

        //获取分享海报信息
        arr = {
            method: ajax_method,
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res.status !== 200) {
            if (res.status !== 204) {
                layer.msg(res.message);
            } else {
                $("#image").attr('src', 'https://api.juanpao.com/uploads/default_poster/poster03.png');
                $("#imageInfo").attr('src', 'https://api.juanpao.com/uploads/default_poster/poster02.png');
            }
        }
        if (res && res.data) {
            if (res.data.length > 2) {
                layer.msg('图片数据错误', {icon: 1, time: 2000});
                return;
            }
            for (var i = 0; i < res.data.length; i++) {
                if (res.data[i].type === '0') {
                    $("#image").attr('src', res.data[i].pic_url);
                } else if (res.data[i].type === '1') {
                    $("#imageInfo").attr('src', res.data[i].pic_url);
                }
            }
            form.render();
        }

        //首页事件
        $("#addImgPut").change(function () {//加载图片至img
            var file = this.files[0];
            var formData = new FormData();
            formData.append('type', '0');
            formData.append('key', saa_key);
            formData.append('file', file);
            $.ajax({
                type: "post",
                url: baseUrl + '/' + ajax_method,
                data: formData,
                headers: headers,
                dataType: "json",
                cache: false,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status === 200)
                        $("#image").attr("src", res.data);
                    else
                        layer.msg(res.message, {icon: 1, time: 2000});
                }
            });
        });

        //详情事件
        $("#addImgPutInfo").change(function () {//加载图片至img
            var file = this.files[0];
            var formData = new FormData();
            formData.append('type', '1');
            formData.append('key', saa_key);
            formData.append('file', file);
            $.ajax({
                type: "post",
                url: baseUrl + '/' + ajax_method,
                data: formData,
                headers: headers,
                dataType: "json",
                cache: false,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status === 200)
                        $("#imageInfo").attr("src", res.data);
                    else
                        layer.msg(res.message, {icon: 1, time: 2000});
                }
            });
        });

    });

    exports('appSet/posters', {})
});