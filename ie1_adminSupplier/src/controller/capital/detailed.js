/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/6/28
 * js 供货商资金明细
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
        var setter = layui.setter;//配置
        var base_url = setter.baseUrl;
        var layDate = layui.laydate;
        var sucMsg = setter.successMsg;//成功提示 数组
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var timeOutCode = setter.timeOutCode;//token错误代码
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单

        var group_data = 0;//是否已加载分组 是 1 否 0
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'supplierBalance';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'balance_sn', title: '提现单号'},
            {field: 'order_sn', title: '订单编号'},
            {field: 'fee', title: '手续费'},
            {field: 'money', title: '金额'},
            {field: 'remain_money', title: '到账金额'},
            {field: 'content', title: '详细'},
            {
                field: 'send_type', title: '提现类型', templet: function (d) {
                    var send_type = '类型错误';
                    if (d.send_type === '2') {
                        send_type = '支付宝';
                    } else if (d.send_type === '3') {
                        send_type = '银行卡';
                    }
                    return send_type;
                }
            },
            {
                field: 'status', title: '状态', templet: function (d) {
                    var status = '类型错误';
                    if (d.status === '0') {
                        status = '结算中';
                    } else if (d.status === '1') {
                        status = '已结算';
                    } else if (d.status === '2') {
                        status = '已拒绝';
                    }
                    return status;
                }
            }
        ];

        //选择日期
        layDate.render({
            elem: '#datetime',
            type: 'datetime'
        });
        /*diy设置结束*/

        //以下基本不动
        //默认加载列表
        var render = table.render({
            elem: '#pageTable',
            url: base_url + '/' + ajax_method,
            page: true, //开启分页
            limit: 10,
            limits: [10,20,30],
            loading: true,
            cols: [cols],
            response: {
                statusName: 'status', //数据状态的字段名称，默认：code
                statusCode: "200", //成功的状态码，默认：0
                dataName: 'data' //数据列表的字段名称，默认：data
            },
            headers: headers,
            done: function (res) {
                if (res.status == timeOutCode) {
                    layer.msg(timeOutMsg);
                    admin.exit();
                    return false;
                }
                if (res && res.balance) {
                    $('.withdrawable_cash').html(parseFloat(res.balance));
                }
                if (res.status !== 200) {
                    // layer.msg(res.message);
                    return false;
                }
            }
        });

        //搜索
        form.on('submit(find)', function (data) {//查询
            render.reload({
                where: {searchName: data.field.searchName},
                page: {curr: 1}
            });
        });

        //点击提现执行方法
        form.on('submit(withdrawal)', function () {//查询
            open_index = layer.open({
                type: 1,
                title: '编辑',
                content: add_edit_form,
                shade: 0,
                offset: '100px',
                area: ['600px', 'auto'],
                cancel: function () {
                    add_edit_form.hide();
                }
            })
        });

        //点击佣金流水执行方法
        var commission_flow = $('.commission_flow');
        form.on('submit(commission_flow)', function () {//查询
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTableFlow',//需要加载的 table 表格对应的 id
                method: 'supplierCommission',//请求的 api 接口方法和可能携带的参数 key
                cols: [[//加载的表格
                    {field: 'order_sn', title: '订单编号'},
                    {field: 'money', title: '金额'},
                    {field: 'format_create_time', title: '时间'}
                ]]//加载的表格字段
            };
            getTableRender(arr);//变量名对应 arr 中的 name
            open_index = layer.open({
                type: 1,
                title: '佣金流水',
                content: commission_flow,
                shade: 0,
                offset: '100px',
                area: ['600px', '400px'],
                cancel: function () {
                    commission_flow.hide();
                }
            })
        });

        //执行提现操作
        form.on('submit(sub)', function () {
            arr = {
                method: ajax_method,
                type: 'post',
                data: {
                    realname: $('input[name=realname]').val(),
                    money: $('input[name=withdrawal_money]').val(),
                    send_type: $('select[name=send_type]').val(),
                    pay_number: $('input[name=pay_number]').val()
                }
            };
            var res = getAjaxReturn(arr);
            if (res) {
                layer.msg('提现成功', {icon: 1, time: 2000});
                layer.close(open_index);
                add_edit_form[0].reset();//表单重置
                add_edit_form.hide();
            }
        });

        //修改状态
        form.on('switch(status)', function (obj) {
            arr = {
                method: ajax_method + '/' + this.value,
                type: 'put',
                data: {status: obj.elem.checked ? 1 : 0},
            };
            if (getAjaxReturn(arr)) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
                layer.close(open_index);
            }
        });

    });
    exports('capital/detailed', {})
});
