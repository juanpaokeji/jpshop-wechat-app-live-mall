/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/2/22
 * js log
 */

layui.define(function (exports) {
    layui.use(['jquery', 'admin', 'setter'], function () {
        var $ = layui.$;
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

        $.ajax({
            url: baseUrl + '/merchantVersion?app_id=2',
            type: "get",
            headers: headers,
            beforeSend: function () {
                loading = layer.load(loadType, loadShade);//显示加载图标
            },
            success: function (res) {
                layer.close(loading);
                if (res.status == timeOutCode) {
                    layer.msg(timeOutMsg);
                    admin.exit();
                    return false;
                }
                if (res.status !== 200) {
                    if (res.status != 204) {
                        layer.msg(res.message);
                    }
                    return false;
                }
                //设置页面值
                var data = res.data;
                var data_len = data.length;
                for (var i = 0; i < data_len; i++) {
                    $('.timeLine').append(getLi(data[i].title, data[i].create_time, data[i].simple_info));
                }
            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);
            }
        })

    })
    exports('overview/log', {})
});

function getLi(title, create_time, simple_info) {
    create_time = new Date(parseInt(create_time + '000')).format('yyyy-MM-dd');
    simple_info = simple_info.replace(/\n/g, '<br/>');
    return '\n' +
        '        <li class="layui-timeline-item">\n' +
        '            <i class="layui-icon layui-timeline-axis">&#xe63f;</i>\n' +
        '            <div class="layui-timeline-content layui-text">\n' +
        '                <h3 class="layui-timeline-title">\n' +
        '                    <span class="title">' + title + '</span>\n' +
        '                    <span class="create_time">' + create_time + '</span>\n' +
        '                </h3>\n' +
        '                <p class="simple_info">' + simple_info + '</p>\n' +
        '            </div>\n' +
        '        </li>';
}
