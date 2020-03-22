/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/5/17 9:50
 * 员工管理
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
        var baseUrl = setter.baseUrl;
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var successMsg;//成功提示，仅用于判断新增编辑
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量

        //页面不同属性
        var url = baseUrl + "/voutypes";//当前页面主要使用 url
        var cols = [//加载的表格
            {field: 'name', title: '类型名称', width: '10%'},
            {field: 'price', title: '面值', width: '5%'},
            {field: 'full_price', title: '满减金额', width: '10%'},
            {field: 'count', title: '已发放总量', width: '10%'},
            {field: 'days', title: '有效期', width: '10%'},
            {field: 'act_id', title: '活动名称', width: '10%'},
            {field: 'from_date', title: '开始时间', width: '10%'},
            {field: 'to_date', title: '结束时间', width: '10%'},
            {field: 'set_online_date', title: '上次上线时间', width: '10%'},
            {field: 'status', title: '状态', width: '5%', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '10%'}
        ];
        var groupData = 0;//是否已加载活动 是 1 否 0

        //有效期 时间插件 到年月日
        layDate.render({
            elem: '#from_to_date',
            type: 'date',
            range: true,
            done: function (value) {
                console.log(value); //得到日期生成的值，如：2017-08-18
            }
        });

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置
            $("input[name='add_edit_status']").prop('checked', true);//还原状态设置为true
            form.render();
            $('input[name=add_edit_type]').val('add');//设置类型为新增
            if (!groupData) {
                getGroups(0);
            } else {
                var selectionBox = document.getElementById('act_id');
                selectionBox.options[0].selected = true;
            }
            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: $('#add_edit_form'),
                shade: 0,
                offset: '100px',
                area: ['400px', 'auto'],
                cancel: function () {
                    $('#add_edit_form').hide();
                }
            })
        })

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var type = $("input[name='add_edit_type']").val();
            var status;
            var subData;
            var ajaxType;
            var ajaxUrl = url;
            if ($('input[name=add_edit_status]:checked').val()) {
                status = 1;
            } else {
                status = 0;
            }
            if (type == 'add') {
                ajaxType = 'post';
                successMsg = sucMsg.post;
            } else if (type == 'edit') {
                ajaxUrl = url + '/' + $("input[name='operationId']").val();
                ajaxType = 'put';
                successMsg = sucMsg.put;
            }
            var form_to_date = $('input[name=from_to_date]').val();
            form_to_date = form_to_date.split(' - ');
            subData = {
                name: $('input[name=name]').val(),
                price: $('input[name=price]').val(),
                full_price: $('input[name=full_price]').val(),
                count: $('input[name=count]').val(),
                days: $('input[name=days]').val(),
                act_id: $('#act_id').val(),
                from_date: form_to_date[0],
                to_date: form_to_date[1],
                status: status,
            }
            // console.log(subData);return;
            $.ajax({
                url: ajaxUrl,
                data: subData,
                type: ajaxType,
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);//关闭加载图标
                    if (res.status != 200) {
                        layer.msg(res.message);
                        return false;
                    }
                    layer.msg(successMsg);
                    layer.close(openIndex);
                    $("#add_edit_form")[0].reset();//表单重置
                    $('#add_edit_form').hide();
                    render.reload();//表格局部刷新
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        })

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            $("input[name='operationId']").val(data.id);
            if (layEvent === 'edit') {//修改
                $("input[name='add_edit_type']").val('edit');
                $.ajax({
                    url: url + '/' + data.id,
                    type: 'get',
                    async: false,
                    headers: headers,
                    success: function (res) {
                        if (res.status == timeOutCode) {
                            layer.msg(timeOutMsg);
                            admin.exit();
                            return false;
                        }
                        layer.close(loading);//关闭加载图标
                        if (res.status != 200) {
                            layer.msg(res.message)
                            return false;
                        }

                        /*diy设置开始*/
                        $("input[name=name]").val(res.data.name);
                        $("input[name=price]").val(res.data.price);
                        $("input[name=full_price]").val(res.data.full_price);
                        $("input[name=count]").val(res.data.count);
                        $("input[name=days]").val(res.data.days);
                        if (!groupData) {
                            getGroups(res.data.act_id);
                        } else {
                            $("#act_id").val(res.data.act_id);
                        }
                        $("input[name=from_to_date]").val(res.data.from_date + " - " + res.data.to_date);
                        if (res.data.status == 1) {
                            $("input[name=add_edit_status]").prop('checked', true);
                        } else {
                            $("input[name=add_edit_status]").removeAttr('checked');
                        }
                        /*diy设置结束*/

                        form.render();//设置完值需要刷新表单
                        openIndex = layer.open({
                            type: 1,
                            title: '编辑',
                            content: $('#add_edit_form'),
                            shade: 0,
                            offset: '100px',
                            area: ['400px', 'auto'],
                            cancel: function () {
                                $('#add_edit_form').hide();
                            }
                        })
                    },
                    error: function () {
                        layer.msg(errorMsg);
                        layer.close(loading);//关闭加载图标
                    },
                    beforeSend: function () {
                        loading = layer.load(loadType, loadShade);//显示加载图标
                    }
                })
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    $.ajax({
                        url: url + '/' + data.id,
                        type: 'delete',
                        async: false,
                        headers: headers,
                        beforeSend: function () {
                            loading = layer.load(loadType, loadShade);//显示加载图标
                        },
                        success: function (res) {
                            if (res.status == timeOutCode) {
                                layer.msg(timeOutMsg);
                                admin.exit();
                                return false;
                            }
                            layer.close(loading);
                            if (res.status != 200) {
                                layer.msg(res.message);
                                return false;
                            }
                            layer.msg(sucMsg.delete);
                            obj.del();
                        },
                        error: function () {
                            layer.msg(errorMsg);
                            layer.close(loading);
                        }
                    })
                })
            } else {
                layer.msg(errorMsg);
            }
        })

        /*动态添加单选框 应用分组*/
        function getGroups(group_id) {
            $.ajax({
                url: baseUrl + '/vouacts',
                type: "get",
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);
                    if (res.status !== 200) {
                        layer.msg(res.message);
                        return false;
                    }
                    for (var a = 0; a < res.data.length; a++) {
                        var name = res.data[a].act_name;
                        var id = res.data[a].id;
                        if (group_id) {
                            var selected = '';
                            if (group_id == id) {
                                selected = ' selected ';
                            }
                            $('select[name=act_id]').append("<option value=" + id + selected + ">" + name + "</option>");
                        } else {
                            $('select[name=act_id]').append("<option value=" + id + ">" + name + "</option>");
                        }
                        form.render();
                    }
                    groupData = 1;
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }

        //加载列表
        var render = table.render({
            elem: '#pageTable',
            url: url,
            page: true, //开启分页
            limit: limit,
            limits: limits,
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
                if (res.status != 200) {
                    layer.msg(res.message);
                    return false;
                }
            }
        });

        //搜索
        form.on('submit(find)', function (data) {
            var searchName = data.field.searchName;
            render.reload({
                where: {
                    searchName: searchName
                },
                page: {
                    curr: 1
                }
            })
        })

        //修改状态
        form.on('switch(status)', function (obj) {
            var statusCode;
            if (obj.elem.checked) {/*将禁用改为启用*/
                statusCode = 1
            } else if (!obj.elem.checked) {//将启用改为禁用
                statusCode = 0
            }
            $.ajax({
                url: url + "/" + this.value,
                type: 'put',
                async: false,
                data: {
                    status: statusCode
                },
                headers: headers,
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);//关闭加载图标
                    if (res.status != 200) {
                        layer.msg(res.message);
                        return false;
                    }
                    layer.msg(sucMsg.put);
                    layer.close(openIndex);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                },
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                }
            })
        });
    })
    exports('voucher/type', {})
});
