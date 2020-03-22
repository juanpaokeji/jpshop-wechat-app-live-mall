/**

 @Name：layuiAdmin 主页控制台
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：GPL-2
 商户列表
 */

layui.define(function (exports) {
    var url = 'http://192.168.188.12';
    layui.use(['jquery'], function () {
        var $ = layui.$;
        $.ajax({
            url: url + '/info',
            type: 'get',
            async: false,
            headers: {
                'Access-Token': layui.data('layuiAdmin').access_token
            },
            success: function (data) {
                console.log(data)
            }
        })
    });

    exports('layout', {})
});