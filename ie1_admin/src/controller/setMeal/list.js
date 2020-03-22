/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2018/5/17
 * Update DateTime: 2019/3/9  一直在更新，时间随时修改
 * js 套餐列表
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
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单

        /*diy设置开始*/
        form.render();

        //页面不同属性
        var ajax_method = 'adminMerchantCombo';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'pic_url', title: '套餐图片', templet: '#imgTpl'},
            {field: 'name', title: '套餐名称'},
            {field: 'sms_number', title: '短信数量'},
            {field: 'order_number', title: '订单数量'},
            {
                field: 'money', title: '金额', templet: function (d) {
                    return parseFloat(d.money);
                }
            },
            {
                field: 'type', title: '套餐类型', templet: function (d) {
                    if (d.type === '1') {
                        return '短信';
                    } else if (d.type === '2') {
                        return '订单';
                    } else if (d.type === '5') {
                        return '组合';
                    } else {
                        return '类型错误';
                    }
                }
            },
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'format_create_time', title: '创建时间'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        var filePut = '';//base64图片

        $("#addImgPut").change(function () {//加载图片至img
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    filePut = e.target.result;
                    $("#image").attr("src", e.target.result);
                };
            }
            file = null;
        });
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            /*diy设置开始*/
            $("input[name='type']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            $("#image").attr('src', '');
            $('.number').hide();
            $('.sms_number').show();
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

        //弹窗点击套餐类型执行事件
        form.on('radio(type)', function (data) {
            $('.number').hide();
            if (data.value === '1') {
                $('.sms_number').show();
            } else if (data.value === '2') {
                $('.order_number').show();
            } else if (data.value === '5') {
                $('.sms_number').show();
                $('.order_number').show();
            } else {
                layer.msg('类型错误', {icon: 1, time: 2000});
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
            var type = $('input[name=type]:checked').val();
            arr = {
                method: method,
                type: ajax_type,
                data: {
                    name: $('input[name=name]').val(),
                    pic_url: filePut,
                    money: $('input[name=money]').val(),
                    type: type,
                    detail_info: $('textarea[name=detail_info]').val(),
                    validity_time: $('input[name=validity_time]').val(),
                    status: status,
                }
            };
            if (type === '1') {
                $('input[name=order_number]').removeAttr('lay-verify');
                $('input[name=sms_number]').attr('lay-verify', 'required|number');
                if ($('input[name=sms_number]').val() === '') {
                    layer.msg('请填写短信数量', {icon: 1, time: 2000});
                    return;
                }
                arr.data.sms_number = $('input[name=sms_number]').val();
            } else if (type === '2') {
                $('input[name=sms_number]').removeAttr('lay-verify');
                $('input[name=order_number]').attr('lay-verify', 'required|number');
                if ($('input[name=order_number]').val() === '') {
                    layer.msg('请填写订单数量', {icon: 1, time: 2000});
                    return;
                }
                arr.data.order_number = $('input[name=order_number]').val();
            } else if (type === '5') {
                $('input[name=sms_number]').attr('lay-verify', 'required|number');
                $('input[name=order_number]').attr('lay-verify', 'required|number');
                if ($('input[name=sms_number]').val() === '') {
                    layer.msg('请填写短信数量', {icon: 1, time: 2000});
                    return;
                }
                if ($('input[name=order_number]').val() === '') {
                    layer.msg('请填写订单数量', {icon: 1, time: 2000});
                    return;
                }
                arr.data.sms_number = $('input[name=sms_number]').val();
                arr.data.order_number = $('input[name=order_number]').val();
            }
            var res = getAjaxReturn(arr);
            if (res) {
                layer.msg(success_msg, {icon: 1, time: 2000});
                layer.close(open_index);
                add_edit_form[0].reset();//表单重置
                add_edit_form.hide();
                render.reload();//表格局部刷新
            }
        })

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'edit') {//修改
                filePut = '';
                ajax_type = 'put';
                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get',
                };
                var res = getAjaxReturn(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    $("input[name=name]").val(res.data.name);
                    $("#image").attr("src", res.data.pic_url);
                    $("input[name=sms_number]").val(res.data.sms_number);
                    $("input[name=order_number]").val(res.data.order_number);
                    $("input[name=money]").val(parseFloat(res.data.money));
                    $("input[name='type'][value='" + res.data.type + "']").prop("checked", true);
                    $("input[name=validity_time]").val(res.data.validity_time);
                    $('.number').hide();
                    if (res.data.type === '1') {
                        $('.sms_number').show();
                    } else if (res.data.type === '2') {
                        $('.order_number').show();
                    } else if (res.data.type === '5') {
                        $('.sms_number').show();
                        $('.order_number').show();
                    } else {
                        layer.msg('套餐类型错误', {icon: 1, time: 2000});
                    }
                    $("textarea[name=detail_info]").val(res.data.detail_info);
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
                    if (getAjaxReturn(arr)) {
                        layer.msg(sucMsg.delete);
                        obj.del();
                    }
                })
            } else {
                layer.msg(setter.errorMsg);
            }
        })

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method,//请求的 api 接口方法
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
            if (getAjaxReturn(arr)) {
                layer.msg(sucMsg.put);
                layer.close(open_index);
            }
        });

        //图片点击显示大图
        $(document).off('click', '.pic_url').on('click', '.pic_url', function () {
            imgClickEvent($(this)[0]);
        });

        //类型查询
        form.on('select(searchType)', function (data) {
            console.log(data.value); //得到被选中的值
            render.reload({
                where: {searchName: $("input[name=searchName]").val(), type: data.value},
                page: {curr: 1}
            });
        });

    });
    exports('setMeal/list', {})
});
