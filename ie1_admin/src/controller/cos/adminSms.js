/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2020/2/13
 * js sms设置
 */

layui.define(function (exports) {
    /**
     * use 首参简单解释
     *
     * jquery 必须 很多地方那个用到，必须定义
     * setter 必须 获取config 配置，但不必定义
     * admin 必须 若未用到则不必定义
     * table 不必须 若表格渲染，若无表格操作点击事件，可不必定义
     * form 不必须 表单操作，一般用于页面有新增和编辑
     * laydate 不必须 日期选择器
     */
    layui.use(['jquery', 'setter', 'admin', 'form'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var operation_id;//数据表格操作需要用到单条 id
        var arr, res;//全局ajax请求参数
        var ajax_type = 'post';//ajax 请求类型，一般用于判断新增或编辑

        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'adminSms';//新ajax需要的参数 method

        /*diy设置结束*/

        //获取单条记录
        arr = {
            method: ajax_method,
            type: 'get',
        };
        res = getAjaxReturn(arr);
        if (res && res.data) {
            ajax_type = 'put';
            operation_id = res.data.id;
            /*diy设置开始*/
            $("input[name=appId]").val(res.data.appId);
            $("input[name=appkey]").val(res.data.appkey);
            /*diy设置结束*/
        }

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var success_msg;
            var method = ajax_method;
            if (ajax_type === 'post') {
                success_msg = sucMsg.post;
            } else if (ajax_type === 'put') {
                method += '/' + operation_id;
                success_msg = sucMsg.put;
            }
            arr = {
                method: method,
                type: ajax_type,
                data: {
                    appId: $('input[name=appId]').val(),
                    appkey: $('input[name=appkey]').val(),
                }
            };
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg(success_msg, {icon: 1, time: 2000});
            }
        });

    });
    exports('cos/adminSms', {})
});
