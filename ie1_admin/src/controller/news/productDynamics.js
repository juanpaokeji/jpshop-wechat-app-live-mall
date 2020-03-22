/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/9 10:10  一直在更新，时间随时修改
 * js model
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
        var operationId;
        var add_edit_type;
        var uEditor_content = '';
        //实例化编辑器
        // UE.delEditor('editor');//先删除之前实例的对象
        var ue = UE.getEditor('content');//添加编辑器
        form.render();

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/adminNews";//当前页面主要使用 url
        var cols = [//加载的表格
            {field: 'sort', title: '排序', width: '20%'},
            {field: 'title', title: '标题', width: '20%'},
            {field: 'create_time', title: '创建时间', width: '20%'},
            {field: 'status', title: '状态', width: '20%', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '20%'}
        ];
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='add_edit_status']").prop('checked', true);//还原状态设置为true

            /*diy设置开始*/
            $("input[name='app_id']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            uEditor_content = '';
            form.render();//还原后需要重置表单
            add_edit_type = 'add';//设置类型为新增
            /*diy设置结束*/
            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: $('#add_edit_form'),
                shade: 0,
                offset: '100px',
                area: ['800px', 'auto'],
                cancel: function () {
                    $('#add_edit_form').hide();
                }
            })
        })

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var status = 0;
            var subData;
            var ajaxType;
            var ajaxUrl = url;
            if ($('input[name=add_edit_status]:checked').val()) {
                status = 1;
            }
            if (add_edit_type == 'add') {
                ajaxType = 'post';
                successMsg = sucMsg.post;
            } else if (add_edit_type == 'edit') {
                ajaxUrl = url + '/' + operationId;
                ajaxType = 'put';
                successMsg = sucMsg.put;
            }

            /*diy设置开始*/
            subData = {
                title: $('input[name=title]').val(),
                sort: $('input[name=sort]').val(),
                content: ue.getContent(),
                type: 1,
                status: status
            }
            /*diy设置结束*/

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
            ue = UE.getEditor('content');//添加编辑器
            var data = obj.data;
            var layEvent = obj.event;
            operationId = data.id;
            if (layEvent === 'edit') {//修改
                add_edit_type = 'edit';
                $.ajax({
                    url: url + '/' + data.id + '?type=1',
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
                        $("input[name=sort]").val(res.data.sort);

                        uEditor_content = res.data.content;
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
                            area: ['800px', 'auto'],
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
                url: baseUrl + '/adminHelpCategory?status=1',
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
                        var name = res.data[a].name;
                        var id = res.data[a].id;
                        if (group_id) {
                            var selected = '';
                            if (group_id == id) {
                                selected = ' selected ';
                            }
                            $('select[name=category_id]').append("<option value=" + id + selected + ">" + name + "</option>");
                        } else {
                            $('select[name=category_id]').append("<option value=" + id + ">" + name + "</option>");
                        }
                        form.render();
                    }
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }

        //以下基本不动
        //加载列表
        var render = table.render({
            elem: '#pageTable',
            url: url + '?type=1',
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
            render.reload({
                where: {
                    searchName: data.field.searchName
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

        $(function () {
            //判断ueditor 编辑器是否创建成功
            ue.addListener("ready", function () {
                // editor准备好之后才可以使用
                ue.setContent(uEditor_content);
            });
        });

    })
    exports('news/productDynamics', {})
});
