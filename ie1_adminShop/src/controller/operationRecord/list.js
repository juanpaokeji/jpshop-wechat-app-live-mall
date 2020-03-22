/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/11/25
 * js 操作记录
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
        var arr, res;//全局ajax请求参数
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'Operation';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'merchant_id', title: '操作人ID'},
            {field: 'operation_type', title: '操作类型'},
            {field: 'operation_id', title: '被操作数据ID'},
            {field: 'module_name', title: '操作模块'},
            {field: 'format_create_time', title: '操作时间'}
        ];
        /*diy设置结束*/

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols]//加载的表格字段
        };
        var render = getTableRender(arr);//变量名对应 arr 中的 name

        //搜索
        form.on('submit(find)', function (data) {//查询
            render.reload({
                where: {searchName: data.field.searchName},
                page: {curr: 1}
            });
        });

    });
    exports('operationRecord/list', {})
});
