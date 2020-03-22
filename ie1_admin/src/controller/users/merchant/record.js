/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/7/3
 * js 套餐购买记录
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var saa_key = sessionStorage.getItem('saa_key');
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'adminMerchantComboAlls';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'merchant_phone', title: '用户名'},
            {field: 'app_name', title: '应用名称'},
            {field: 'combo_name', title: '套餐名称'},
            {field: 'sms_number', title: '短信数量'},
            {field: 'sms_remain_number', title: '短信剩余数量'},
            {field: 'order_number', title: '订单数量'},
            {field: 'order_remain_number', title: '订单剩余数量'},
            {field: 'format_create_time', title: '购买时间'},
            {field: 'format_validity_time', title: '到期时间'},
            {field: 'remarks', title: '购买方式'}
        ];
        /*diy设置结束*/

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols],//加载的表格字段
        };
        getTableRender(arr);//变量名对应 arr 中的 name

    });
    exports('users/merchant/record', {})
});
