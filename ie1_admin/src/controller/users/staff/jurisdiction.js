/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/9 9:00
 * 管理员权限管理
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
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
        var url = baseUrl + "/rules";//当前页面主要使用 url
        var cols = [//加载的表格
            {field: 'name', title: '权限名称', width: "20%"},
            {field: 'title', title: '权限标题', width: "20%"},
            {field: 'condition', title: '触发条件', width: "20%"},
            {field: 'type', title: '类型', width: "10%", templet: '#typeTpl'},
            {field: 'status', title: '状态', width: "10%", templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: "20%"}
        ];
        var menu = '<div class="layui-form-item">\n' +
            '                <label class="layui-form-label">菜单路由地址</label>\n' +
            '                <div class="layui-input-inline">\n' +
            '                    <input name="menu_url" required lay-verify="required" placeholder="请输入菜单路由地址" class="layui-input">\n' +
            '                </div>\n' +
            '            </div>\n' +
            '            <div class="layui-form-item">\n' +
            '                <label class="layui-form-label">菜单名称</label>\n' +
            '                <div class="layui-input-inline">\n' +
            '                    <input name="menu_name" required lay-verify="required" placeholder="请输入菜单名称" class="layui-input">\n' +
            '                </div>\n' +
            '            </div>';

        //选择类型 下拉事件
        form.on('select(rule_type)', function (data) {
            var typeValue = data.value;
            if (typeValue != 1) {
                $('.rule_type').empty();//清空div
            } else {
                $('.rule_type').append(menu);//显示div
            }
        });

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $('.rule_type').empty();
            $("input[name='add_edit_type']").prop('checked', true);//还原规则表达式设置为true
            $("input[name='add_edit_status']").prop('checked', true);//还原状态设置为true
            $("input[name='rule_type']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            form.render();//还原后需要重置表单
            $('input[name=add_edit_type]').val('add');//设置类型为新增
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
            var status = 0;
            var subData;
            var ajaxType;
            var ajaxUrl = url;
            if ($('input[name=add_edit_status]:checked').val()) {
                status = 1;
            }
            if (type == 'add') {
                ajaxType = 'post';
                successMsg = sucMsg.post;
            } else if (type == 'edit') {
                ajaxUrl = url + '/' + $("input[name='operationId']").val();
                ajaxType = 'put';
                successMsg = sucMsg.put;
            }
            subData = {
                name: $('input[name=name]').val(),
                title: $('input[name=title]').val(),
                type: 1,
                rule_type: $('select[name=rule_type]').val(),
                condition: $('input[name=condition]').val(),
                icon: $('input[name=icon]').val(),
                menu_url: $('input[name=menu_url]').val(),
                menu_name: $('input[name=menu_name]').val(),
                sort: $('input[name=sort]').val(),
                status: status,
            }
            //如果类型为页面，则下面两条数据不需要传递到后台
            if ($('select[name=rule_type]').val() != 1) {
                delete subData.menu_url;
                delete subData.menu_name;
            }
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
                $('.rule_type').empty();
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
                        $("input[name=title]").val(res.data.title);
                        var typeValue = res.data.rule_type;
                        $("select[name=rule_type]").val(typeValue);
                        if (typeValue == 1) {
                            $('.rule_type').append(menu);//显示div
                            $("input[name=menu_url]").val(res.data.menu_url);
                            $("input[name=menu_name]").val(res.data.menu_name);
                        } else {
                            $('.rule_type').empty();//清空div
                        }
                        $("input[name=condition]").val(res.data.condition);
                        $("input[name=icon]").val(res.data.icon);
                        $("input[name=sort]").val(res.data.sort);
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

        //以下基本不动
        //加载列表
        var render = table.render({
            elem: '#pageTable',
            url: url,
            page: true, //开启分页
            limit: limit,
            limits: limits,
            where: {type: 1},
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
            render.reload({
                where: {
                    searchName: data.field.searchName, type: 1
                },
                page: {
                    curr: 1
                }
            })
        })

        //修改状态
        form.on('switch(statusTpl)', function (obj) {
            var statusCode = obj.elem.checked ? 1 : 0;
            $.ajax({
                url: url + "/" + this.value,
                type: 'put',
                async: false,
                data: {status: statusCode},
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
    exports('users/staff/jurisdiction', {})
});
