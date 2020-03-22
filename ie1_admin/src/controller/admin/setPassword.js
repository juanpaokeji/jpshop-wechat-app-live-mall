/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/5/28 9:50
 * 员工管理后台修改密码
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        // var table = layui.table;
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

        //设置我的资料
        form.on('submit(setmypass)', function (obj) {
            if (obj.field.password != obj.field.repassword) {
                layer.msg('两次密码输入不一致');
                return false;
            }
            //提交修改
            $.ajax({
                url: baseUrl + '/password',
                data: obj.field,
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
                        layer.msg(res.message);
                        return false;
                    }
                    layer.msg('密码修改成功，请重新登录', {time: 2000}, function () {
                        admin.exit();
                    });
                    layer.close(openIndex);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });
    });

    exports('/admin/setPassword', {})
});