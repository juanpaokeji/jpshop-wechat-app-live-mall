/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/6/19
 * js 商城应用用的会员卡
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var open_index_config;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        var config_form = $('#config_form');//常用的表单
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'vips';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'name', title: '会员卡名称'},
            {field: 'money', title: '会员卡金额'},
            {
                field: 'validity_time', title: '有效时间', templet: function (d) {
                    if (d.validity_time === 1) {
                        return '七天';
                    } else if (d.validity_time === 2) {
                        return '一个月';
                    } else if (d.validity_time === 3) {
                        return '三个月';
                    } else if (d.validity_time === 4) {
                        return '一年';
                    } else {
                        return '';
                    }
                }
            },
            {field: 'pay_count', title: '购买次数'},
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            /*diy设置开始*/
            form.render();//还原后需要重置表单
            ajax_type = 'post';//设置类型为新增
            /*diy设置结束*/

            open_index = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0,
                offset: '100px',
                area: ['600px', 'auto'],
                cancel: function () {
                    add_edit_form.hide();
                }
            })
        });

        //获取会员总开关设置
        arr = {
            method: 'merchantVipPlugin',
            type: 'get',
            data:{
                english_name:'Vip_payment'
            }

        };
        res = getAjaxReturnKey(arr);
        var vip_in_id = 0;
        if (res && res.data) {
            vip_in_id = res.data.id;
            if (res.data.is_open === '1') {
                $("input[name=vip_in_status]").prop('checked', true);
            } else {
                $("input[name=vip_in_status]").removeAttr('checked');
            }
            form.render();
        }

        //会员总开关
        form.on('switch(vip_in_status)', function (obj) {
            arr = {
                method: 'merchantVipPlugin/' + vip_in_id,
                type: 'put',
                data: {
                    is_open: obj.elem.checked ? 1 : 0,
                    english_name:'Vip_payment'
                },
            };
            if (obj.elem.checked){
                layer.confirm('开启付费会员，积分会员将自动关闭，确定开启吗?', {
                    btn: ['确定','取消'] //按钮
                } ,function (index) {
                    layer.close(index);
                    if (getAjaxReturnKey(arr)) {
                        layer.msg(sucMsg.put);
                        layer.close(open_index);
                    }
                    $("input[name=vip_in_status]").prop('checked', true);
                    form.render();
                })
                $("input[name=vip_in_status]").removeAttr('checked');
                form.render();
                return;
            }
            if (getAjaxReturnKey(arr)) {
                layer.msg(sucMsg.put);
                layer.close(open_index);
            }
        });

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var status = 0;
            if ($('input[name=status]:checked').val()) {
                status = 1;
            }
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
                    name: $('input[name=name]').val(),
                    money: $('input[name=money]').val(),
                    validity_time: $('select[name=validity_time]').val(),
                    pay_count: $('input[name=pay_count]').val(),
                    status: status,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(success_msg, {icon: 1, time: 2000});
                layer.close(open_index);
                add_edit_form[0].reset();//表单重置
                add_edit_form.hide();
                render.reload();//表格局部刷新
            }
        });

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'edit') {//修改
                ajax_type = 'put';
                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get',
                };
                var res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    $("input[name=name]").val(res.data.name);
                    $("input[name=money]").val(res.data.money);
                    $("select[name=validity_time]").val(res.data.validity_time);
                    $("input[name=pay_count]").val(res.data.pay_count);
                    if (res.data.status == 1) {
                        $("input[name=status]").prop('checked', true);
                    } else {
                        $("input[name=status]").removeAttr('checked');
                    }
                    /*diy设置结束*/

                    form.render();//设置完值需要刷新表单
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
                }
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    arr = {
                        method: ajax_method + '/' + data.id,
                        type: 'delete',
                    };
                    if (getAjaxReturnKey(arr)) {
                        layer.msg(sucMsg.delete, {icon: 1, time: 2000});
                        obj.del();
                    }
                })
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols],//加载的表格字段
        };
        var render = getTableRender(arr);//变量名对应 arr 中的 name

        //搜索
        form.on('submit(find)', function (data) {//查询
            render.reload({
                where: {searchName: data.field.searchName},
                page: {curr: 1}
            });
        });

        //修改状态
        form.on('switch(status)', function (obj) {
            arr = {
                method: ajax_method + '/' + this.value,
                type: 'put',
                data: {status: obj.elem.checked ? 1 : 0},
            };
            if (getAjaxReturnKey(arr)) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
                layer.close(open_index);
            }
        });

        //会员卡配置
        var config_ajax_type;
        var config_method = 'vipConfig';
        var config_operation_id;
        form.on('submit(vip_config)', function () {//查询
            arr = {
                method: config_method,
                type: 'get'
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                if (res.status === 200 && res.data) {
                    config_operation_id = res.data.id;
                    //有数据，更新
                    config_ajax_type = 'put';
                    $('input[name=discount_ratio]').val(res.data.discount_ratio);
                    $('input[name=voucher_count]').val(res.data.voucher_count);
                    getGroups(res.data.voucher_type_id);
                    $('input[name=score_times]').val(res.data.score_times);
                } else if (res.status === 204) {
                    //无数据，新增
                    config_ajax_type = 'post';
                    getGroups(0);
                } else {
                    //发生错误，直接错误提示
                    layer.msg(res.message, {icon: 1, time: 2000});
                    return;
                }
                open_index_config = layer.open({
                    type: 1,
                    title: '会员卡配置',
                    content: config_form,
                    shade: 0,
                    offset: '100px',
                    area: ['600px', 'auto'],
                    cancel: function () {
                        config_form.hide();
                    }
                })
            }
        });

        //执行配置的添加或编辑
        form.on('submit(sub_config)', function () {
            var success_msg;
            var method = config_method;
            if (config_ajax_type === 'post') {
                success_msg = sucMsg.post;
            } else if (config_ajax_type === 'put') {
                method += '/' + config_operation_id;
                success_msg = sucMsg.put;
            }
            arr = {
                method: method,
                type: config_ajax_type,
                data: {
                    discount_ratio: $('input[name=discount_ratio]').val(),
                    voucher_count: $('input[name=voucher_count]').val(),
                    voucher_type_id: $('select[name=voucher_type_id]').val(),
                    score_times: $('input[name=score_times]').val()
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(success_msg, {icon: 1, time: 2000});
                layer.close(open_index_config);
                config_form[0].reset();//表单重置
                config_form.hide();
            }
        });

        /*动态添加单选框 应用分组*/
        function getGroups(group_id) {
            arr = {
                method: 'shopVouTypes',
                type: 'get',
                data: {
                    type: 1,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var name;
                var id;
                for (var a = 0; a < res.data.length; a++) {
                    name = res.data[a].name;
                    id = res.data[a].id;
                    if (group_id) {
                        var selected = '';
                        if (group_id === id) {
                            selected = ' selected ';
                        }
                        $('select[name=voucher_type_id]').append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        $('select[name=voucher_type_id]').append("<option value=" + id + ">" + name + "</option>");
                    }
                    form.render();
                }
            }
        }

    });
    exports('voucher/vips', {})
});
