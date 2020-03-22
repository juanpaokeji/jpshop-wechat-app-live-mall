/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/8/24 10:10
 * js 菜单
 */

layui.define(function (exports) {
    layui.use(['jquery', 'setter'], function () {
        var $ = layui.$;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var errorMsg = setter.errorMsg;//错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性

        //页面不同属性
        var url = baseUrl + "/wechat/officialAccount/menu";//当前页面主要使用 url

        //点击获取列表
        $(".list").on('click', function(){
            useAjax(url, 'get', '');
        });

        //点击创建
        $(".create").on('click', function(){
            useAjax(url + '/create', 'post', '');
        });

        //点击修改
        $(".update").on('click', function(){
            useAjax(url + '/create', 'post', '');
        });

        //点击删除
        $(".delete").on('click', function(){
            useAjax(url + '/delete', 'delete', '');
        });




        //通用请求ajax方法
        function useAjax(ajaxUrl, ajaxType, ajaxData) {
            $.ajax({
                url: ajaxUrl,
                type: ajaxType,
                data: ajaxData,
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    layer.close(loading);//关闭加载图标
                    layer.msg(res.message);
                    if (res.status != 200) {
                        return false;
                    }
                    console.log(res);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }

    })
    exports('wx/officialAccount/menu', {})
});
