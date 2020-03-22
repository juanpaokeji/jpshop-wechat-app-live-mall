/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/5/17 9:50
 * 商户管理
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
        var sucMsg = setter.successMsg;//成功提示 数组
        var openIndex;//定义弹出层，方便关闭
        var operation_id;
        var arr = {}, res, partner, buy = '', layEvent = '', openIndexNumber;//全局ajax请求参数
        var app_key;//需要充值的应用 key
        var group_data = 0;//是否已加载分组 是 1 否 0

        //选择日期
        layDate.render({
            elem: '#validity_time',
            type: 'date'
        });

        //页面不同属性
        var method = 'merchants';//当前页面主要使用 url 请求方法，加载列表和新的render在方法中直接填写，不需要定义
        var partner_method = 'partnersNumber';

        //商户列表
        var cols = [ //表头
            {field: 'phone', title: '手机'},
            {field: 'last_login_time', title: '最后一次登录时间'},
            {field: 'create_time', title: '创建时间'},
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'pay_switch', title: '支付开关', templet: '#paySwitchTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        //商户应用列表
        var colsApp = [ //表头
            {field: 'pic_url', title: '应用图片', templet: '#imgTpl', width: '6%'},
            {field: 'name', title: '应用名称', width: '8%'},
            {field: 'create_time', title: '创建时间', width: '11%'},
            {
                field: 'is_release', title: '小程序是否授权', templet: function (d) {
                    return '<span class="layui-col-md8">' + (d.is_release ? "已授权" : "未授权") + '</span>';
                }, width: '8%'
            },
            {
                field: 'version', title: '小程序最新上传版本', templet: function (d) {
                    return '<span class="layui-col-md8">' + (d.version ? d.version : "未上传") + '</span>';
                }, width: '10%'
            },
            {
                field: 'number', title: '小程序最新发布版本', templet: function (d) {
                    return '<span class="layui-col-md8">' + (d.number ? d.number : "未发布") + '</span>';
                }, width: '10%'
            },
            {field: 'copyright', title: '自定义版权', templet: '#copyrightTpl', width: '7%'},
            {field: 'partner_number', title: '合伙人数量', width: '7%'},
            {field: 'validity_time', title: '套餐到期时间', width: '11%', templet: function (d) {
                    if (Trim(d.validity_time) === '') {
                        return '未购买';
                    } else {
                        return d.validity_time;
                    }
                }},
            {field: 'operationsApp', title: '操作', toolbar: '#operationsApp', width: '14%'}
        ];

        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'app_list') {
                //应用列表
                //加载商户应用和小程序版本列表
                app_table_render = getTableRender({
                    name: 'render_app',
                    elem: '#pageTableApp',
                    method: 'adminVersion?merchant_id=' + data.id,
                    type: 'get',
                    page: false,
                    cols: [colsApp],
                    limit: 30
                });
                //显示商户购买的应用
                openIndex = layer.open({
                    type: 1,
                    title: '应用列表',
                    content: $('#app_form'),
                    shade: 0,
                    offset: ['100px', '240px'],
                    area: ['80vw', '30vw'],
                    cancel: function () {
                        $('#app_form').hide();
                    }
                })
            } else if (layEvent === 'app_number') {
                $('input[name=app_number]').val(data.number);
                //显示应用数量设置
                openIndexNumber = layer.open({
                    type: 1,
                    title: '应用数量设置',
                    content: $('#app_number'),
                    shade: 0,
                    offset: '100px',
                    area: ['400px', 'auto'],
                    cancel: function () {
                        $('#app_number').hide();
                    }
                })
            } else if (layEvent === 'pay_record') {
                arr = {
                    'name': 'render',//必传参
                    'elem': '#pageTablePay',//必传参
                    'method': 'getInstanceLog/' + data.id,//必传参
                    'type': 'get',//必传参
                    'cols': [[ //表头
                        {field: 'orderId', title: '腾讯云订单id'},
                        {field: 'spec', title: '规格'},
                        {field: 'is_pay', title: '是否购买', templet: function (d) {
                                var is_pay = d.is_pay;
                                if (is_pay === '1') {
                                    return '是';
                                } else {
                                    return '否';
                                }
                            }},
                        {field: 'remark', title: '备注'}
                    ]]//必传参
                };
                var renderPay = getTableRender(arr);
                //显示购买记录
                openIndexNumber = layer.open({
                    type: 1,
                    title: '购买记录',
                    content: $('#pay_record'),
                    shade: 0,
                    offset: '100px',
                    area: ['800px', '600px'],
                    cancel: function () {
                        $('#pay_record').hide();
                    }
                })
            } else {
                buy = layer.open({
                    type: 1,
                    title: layEvent + '充值',
                    content: $('#buy'),
                    shade: 0,
                    offset: '100px',
                    area: ['400px', '200px'],
                    cancel: function () {
                        $('#buy').hide()
                    }
                })
            }
        });

        //充值数量页面点击确定执行方法
        form.on('submit(sub)', function () {
            var number = $('input[name=number]').val(),
                data = {merchant_id: operation_id, order_number: 0, sms_number: 0, key: app_key};
            layEvent === '短信' ? data.sms_number = number : data.order_number = number;
            arr = {
                method: 'adminMerchantComboInsert',
                type: 'post',
                data: {merchant_id: operation_id, order_number: 0, sms_number: 0, key: app_key}
            };
            //type 表格类型 1 短信 2 订单
            if (layEvent === '短信') {
                arr.data.type = '1';
                arr.data.sms_number = number;
            } else if (layEvent === '订单') {
                //如果是订单，首先判断是否选择了套餐，未选值为0
                var order_combo_id = $('input[name=radio]:checked').val();
                var order_combo_number = $('input[name=radio]:checked').attr('data');
                if (order_combo_id !== '0') {
                    arr.data.order_number = order_combo_number;
                    arr.data.combo_id = order_combo_id;
                } else {
                    arr.data.combo_id = 0;
                }
                arr.data.type = '2';
                arr.data.validity_time = $('input[name=validity_time]').val();
            }
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg('充值成功', {icon: 1, time: 2000});
                $('#buy').hide();
                layer.close(buy);
                $('input[name=number]').val('');
            }
        });

        //应用数量设置页面点击确定执行方法
        form.on('submit(sub_number)', function () {
            var app_number = $('input[name=app_number]').val(),
                arr = {
                    method: method + '/' + operation_id,
                    type: 'put',
                    data: {
                        number: app_number
                    }
                };
            res = getAjaxReturn(arr);
            if (res) {
                $('#app_number').hide();
                layer.close(openIndexNumber);
                $('input[name=app_number]').val('1');
                layer.msg('修改成功', {icon: 1, time: 1000}, function () {
                    location.reload();
                });
            }
        });

        //以下基本不动
        //默认加载列表
        arr = {
            'name': 'render',//必传参
            'elem': '#pageTable',//必传参
            'method': method,//必传参
            'type': 'get',//必传参
            'cols': [cols],//必传参
        };
        var render = getTableRender(arr);

        //搜索
        form.on('submit(find)', function (data) {//查询
            var searchName = data.field.searchName;
            render.reload({
                where: {
                    searchName: searchName
                },
                page: {
                    curr: 1
                }
            });
        });

        //修改状态
        form.on('switch(status)', function (obj) {
            arr = {
                'method': method + '/' + this.value,
                'type': 'put',
                'data': {status: obj.elem.checked ? 1 : 0},
            };
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg(sucMsg.put);
                layer.close(openIndex);
            }
        });

        //修改支付开关
        form.on('switch(pay_switch)', function (obj) {
            arr = {
                'method': method + '/' + this.value,
                'type': 'put',
                'data': {pay_switch: obj.elem.checked ? 1 : 0},
            };
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg(sucMsg.put);
                layer.close(openIndex);
            }
        });

        var app_id, app_table_render;
        table.on('tool(pageTableApp)', function (obj) {
            var data = obj.data;
            app_id = data.id;
            app_key = data.key;
            layEvent = obj.event;

            if (layEvent === 'app_list') {
                //应用列表
                //加载商户应用和小程序版本列表
                app_table_render = getTableRender({
                    name: 'render_app',
                    elem: '#pageTableApp',
                    method: 'adminVersion?merchant_id=' + data.id,
                    type: 'get',
                    page: false,
                    cols: [colsApp],
                    limit: 30
                })
                //显示商户购买的应用
                openIndex = layer.open({
                    type: 1,
                    title: '应用列表',
                    content: $('#app_form'),
                    shade: 0,
                    offset: ['100px', '240px'],
                    area: ['80vw', '30vw'],
                    cancel: function () {
                        $('#app_form').hide();
                    }
                })
            } else {
                if (layEvent === '订单') {
                    //获取订单套餐列表
                    if (!group_data) {
                        getGroups();
                    } else {
                        $(":radio[name='radio'][value='0']").prop("checked", "checked");
                        // form.render();
                    }
                    $('.order_combo_list').show();
                } else {
                    $('.order_combo_list').hide();
                }

                if (layEvent === '订单' || layEvent === '短信') {
                    var open_height = '200px';
                    if (layEvent === '订单') {
                        open_height = '300px';
                    }
                    buy = layer.open({
                        type: 1,
                        title: layEvent + '充值',
                        content: $('#buy'),
                        shade: 0,
                        offset: '100px',
                        area: ['500px', open_height],
                        cancel: function () {
                            $('#buy').hide()
                        }
                    })
                }
                if (layEvent === '合伙人') {
                    partner = layer.open({
                        type: 1,
                        title: layEvent,
                        content: $('#partner'),
                        shade: 0,
                        offset: '100px',
                        area: ['500px', '200px'],
                        cancel: function () {
                            $('#partner').hide();
                        }
                    })
                }
            }
        });

        //合伙人数量设置页面点击确定执行方法
        form.on('submit(partner_sub)', function () {
            var number = $('input[name=partner_number]').val();
            arr = {
                method: partner_method + '/' + app_id,
                type: 'put',
                data: {
                    partner_number: number
                }
            };
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg('设置成功', {icon: 1, time: 2000});
                app_table_render.reload();
                $('#partner').hide();
                layer.close(partner);
                $('input[name=partner_number]').val('');
            }
        });

        //套餐购买记录
        form.on('submit(record)', function () {//查询
            location.hash = '/users/merchant/record';
        });

        //自定义版权修改状态
        form.on('switch(copyright)', function (obj) {
            arr = {
                'method': 'adminCopyright/' + this.value,
                'type': 'put',
                'data': {copyright: obj.elem.checked ? 1 : 0}
            };
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg(sucMsg.put);
            }
        });

        /*动态添加单选框 订单套餐*/
        function getGroups() {
            arr = {
                method: 'adminMerchantCombo?type=2&status=1',
                type: 'get'
            };
            res = getAjaxReturn(arr);
            if (res && res.data) {
                var name;
                var id;
                var order_num;
                $('.order_combo').empty().append('<input lay-filter="combo_change" type="radio" name="radio" value="0" title="免费版" checked/>');
                for (var a = 0; a < res.data.length; a++) {
                    name = res.data[a].name;
                    id = res.data[a].id;
                    order_num = res.data[a].order_number;
                    $('.order_combo').append('<input lay-filter="combo_change" type="radio" name="radio" data="' + order_num + '" value="' + id + '" title="' + name + '"/>');
                    form.render();
                }
                group_data = 1;
            }
        }

        // //订单套餐切换事件
        // form.on('radio(combo_change)', function (data) {
        //     if (data.value != '0') {
        //         $('.number_recharge').hide();
        //         $('.validity_time').show();
        //     } else {
        //         $('.number_recharge').show();
        //         $('.validity_time').hide();
        //     }
        //     form.render();
        // });

    });

    exports('users/merchant/list', {});
});