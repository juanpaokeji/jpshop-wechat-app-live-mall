/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2018/5/17
 * Update DateTime: 2019/3/9  一直在更新，时间随时修改
 * js model
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'laydate', 'element'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
        var element = layui.element;
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单

        //进入营销菜单必须执行方法，获取该应用的自定义版权状态，如果为1则显示自定义版权，为0则需要隐藏
        //之前写在layout里，太消耗性能，所以写在营销菜单下的所有页面里
        arr = {
            method: 'merchantCopyright',
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data && res.data.copyright && res.data.copyright === '1') {
            if ($('.copyright_li').length <= 0) {
                $('.voucher_ul').append('<li class="copyright_li"><a lay-href="voucher/copyright">自定义版权</a></li>');
            }
        } else {
            $('.copyright_li').remove();
        }

        form.render();
        /*diy设置开始*/

        //监听Tab切换，以改变地址hash值
        var pageTableRecord;
        element.on('tab(pay)', function () {
            var lay_id = this.getAttribute('lay-id');
            if (lay_id === '1') {
                render.reload();
            } else if (lay_id === '2') {
                //加载充值记录列表
                arr = {
                    name: 'render',//可操作的 render 对象名称
                    elem: '#pageTableRecord',//需要加载的 table 表格对应的 id
                    method: 'balanceAccessLists?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
                    cols: [[//加载的表格
                        {field: 'nickname', title: '充值会员'},
                        {field: 'money', title: '支付金额'},
                        {field: 'remain_money', title: '到账金额'},
                        {
                            field: 'pay_type', title: '购买类型', templet: function (d) {
                                var pay_type = d.pay_type;
                                if (pay_type === '1') {
                                    return '微信';
                                } else if (pay_type === '2') {
                                    return '支付宝';
                                } else if (pay_type === '5') {
                                    return '扫呗';
                                } else {
                                    return '其他';
                                }
                            }
                        },
                        {field: 'pay_sn', title: '支付流水号'},
                        {field: 'transaction_id', title: '第三方支付流水号'},
                        {
                            field: 'status', title: '支付状态', templet: function (d) {
                                var status = d.status;
                                if (status === '1') {
                                    return '已支付';
                                } else if (status === '0') {
                                    return '未支付';
                                } else {
                                    return '类型错误';
                                }
                            }
                        },
                        {field: 'create_time', title: '创建时间'}
                    ]]
                };
                pageTableRecord = getTableRender(arr);//变量名对应 arr 中的 name
            } else {
                layer.msg('没有这一栏', {icon: 1, time: 2000});
            }
        });

        //页面不同属性
        var ajax_method = 'balanceRatios';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'money', title: '充值金额'},
            {field: 'remain_money', title: '到账金额'},
            {field: 'remarks', title: '备注'},
            // {
            //     field: 'type', title: '类型', templet: function (d) {
            //         var type = d.type;
            //         if (type === '0') {
            //             return '通用';
            //         } else if (type === '1') {
            //             return '微信';
            //         } else if (type === '2') {
            //             return '支付宝';
            //         } else if (type === '3') {
            //             return '银行卡';
            //         }
            //     }
            // },
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            /*diy设置开始*/
            $("input[name='type']:eq(0)").prop("checked", true);//还原类型默认选中第一个
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
                    money: $('input[name=money]').val(),
                    remain_money: $('input[name=remain_money]').val(),
                    remarks: $("textarea[name=remarks]").val(),
                    // type: $('input[name=type]:checked').val(),
                    type: 0,
                    status: status
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
                    type: 'get'
                };
                var res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    $("input[name=money]").val(res.data.money);
                    $("input[name=remain_money]").val(res.data.remain_money);
                    $("textarea[name=remarks]").val(res.data.remarks);
                    // $("input[name='type'][value='" + res.data.type + "']").prop("checked", true);
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
                        type: 'delete'
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

        //充值记录搜索
        form.on('submit(findRecord)', function (data) {//查询
            pageTableRecord.reload({
                where: {nickname: data.field.searchNameRecord},
                page: {curr: 1}
            });
        });

        //修改状态
        form.on('switch(status)', function (obj) {
            arr = {
                method: ajax_method + '/' + this.value,
                type: 'put',
                data: {status: obj.elem.checked ? 1 : 0}
            };
            if (getAjaxReturnKey(arr)) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
                layer.close(open_index);
            }
        });

    });
    exports('voucher/pay', {})
});
