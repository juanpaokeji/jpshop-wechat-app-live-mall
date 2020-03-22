/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/7 14:30
 * js 全局配置
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
        var url = baseUrl + "/configs";//当前页面主要使用 url
        var cols = [//加载的表格
            {field: 'category_id', title: '类目', width: '10%'},
            {field: 'title', title: '标题', width: '10%'},
            {field: 'content', title: '内容', width: '20%'},
            {field: 'key', title: '键', width: '15%'},
            {field: 'value', title: '值', width: '15%'},
            {field: 'type', title: '值类型', width: '10%', templet: '#typeTpl'},
            {field: 'status', title: '状态', width: '10%', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '10%'}
        ];
        var groupData = 0;//是否已加载分组 是 1 否 0

        var elem = '<div class="layui-form-item">\n' +
            '                <label class="layui-form-label">值</label>\n' +
            '                <div class="layui-input-inline">\n' +
            '                    <input name="value" placeholder="请输入值" class="layui-input">\n' +
            '                </div>\n' +
            '            </div>';
        var elemArr = '\n' +
            '            <div class="layui-form-item">\n' +
            '                <label class="layui-form-label"></label>\n' +
            '                <div class="layui-input-inline">\n' +
            '                    <input type="text" name="valueK" required lay-verify="required" placeholder="请输入key" class="layui-input">\n' +
            '                </div>\n' +
            '                <div class="layui-input-inline">\n' +
            '                    <input type="text" name="valueV" required lay-verify="required" placeholder="请输入value" class="layui-input">\n' +
            '                </div>\n' +
            '                <div class="layui-btn deleteIcon" style="float: right;">-</div>\n' +
            '            </div>';
        //选择类型 下拉事件
        form.on('select(type)', function (data) {
            $('.elem').empty();//清空div
            $('.addIcon').hide();//隐藏+号
            var typeValue = data.value;
            //不是数组
            if (typeValue != 3) {
                $('.elem').append(elem);
            } else {
                $('.addIcon').show();//隐藏+号
                $('.elem').append(elemArr);
            }
            form.render();//重置表单
        });

        //新增按钮点击事件
        $(".addIcon").click(function () {
            $('.elem').append(elemArr);
        });

        //删除按钮点击事件
        $(document).on("click", '.deleteIcon', function () {
            var parentNode = this.parentNode;
            var length = $(".elem").children(".layui-form-item").length;
            if (length > 1) {
                parentNode.remove();
            } else {
                layer.msg('已经是最后一个啦！');
            }
        });

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $('.elem').empty();//清空div
            $('.addIcon').hide();//隐藏+号
            $('.elem').append(elem);
            $("input[name='add_edit_status']").prop('checked', true);//还原状态设置为true
            $("input[name='category_id']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            form.render();//还原后需要重置表单
            $('input[name=add_edit_type]').val('add');//设置类型为新增
            //下拉请求接口必须，未请求过，则请求接口并保存，已请求过，获取保存的信息，减少加载时间
            if (!groupData) {
                getGroups(0);
            } else {
                var category = document.getElementById('category_id');
                category.options[0].selected = true;
            }
            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: $('#add_edit_form'),
                shade: 0,
                offset: '100px',
                area: ['600px', 'auto'],
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
            var value = '';
            if ($('select[name=type]').val() == 3) {
                var vkArr = [];
                var vvArr = [];
                value = "{";
                $("input[name='valueK']").each(function (j, item) {
                    vkArr.push(item.value);
                });
                $("input[name='valueV']").each(function (j, item) {
                    vvArr.push(item.value);
                });
                for (var i = 0; i < vkArr.length; i++) {
                    value[vkArr[i]] = vvArr[i];
                    value += vkArr[i] + ":";
                    value += "\"" + vvArr[i] + "\",";
                }
                value = value.substring(0, value.length - 1);
                value += "}";
                value = eval('(' + value + ')');
            } else {
                value = $('input[name=value]').val();
            }
            subData = {
                category_id: $('select[name=category_id]').val(),
                title: $('input[name=title]').val(),
                content: $('textarea[name=content]').val(),
                key: $('input[name=key]').val(),
                type: $('select[name=type]').val(),
                value: value,
                status: status,
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
                        $("input[name=title]").val(res.data.title);
                        if (!groupData) {
                            getGroups(res.data.category_id);
                        } else {
                            $("#category_id").val(res.data.category_id);
                        }
                        $("textarea[name=content]").val(res.data.content);
                        $("input[name=key]").val(res.data.key);
                        $("select[name=type]").val(res.data.type);

                        var value = res.data.value;
                        $('.elem').empty();//清空div
                        //判断属否为数组
                        if (res.data.type == 3) {
                            $('.addIcon').show();//显示+号
                            value = eval('(' + value + ')');
                            for (var i in value) {
                                var elemArr = '<div class="layui-form-item">\n' +
                                    '                <label class="layui-form-label"></label>\n' +
                                    '                <div class="layui-input-inline">\n' +
                                    '                    <input type="text" name="valueK" value="' + i + '" required lay-verify="required" placeholder="请输入key"\n' +
                                    '                           class="layui-input">\n' +
                                    '                </div>\n' +
                                    '                <div class="layui-input-inline">\n' +
                                    '                    <input type="text" name="valueV" value="' + value[i] + '" required lay-verify="required" placeholder="请输入value"\n' +
                                    '                           class="layui-input">\n' +
                                    '                </div>\n' +
                                    '                <div class="layui-btn deleteIcon" style="float: right;">-</div>\n' +
                                    '            </div>'
                                $('.elem').append(elemArr);
                                form.render();
                            }
                        } else {
                            $('.elem').append(elem);
                            $("input[name=value]").val(value);
                        }

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
                            area: ['600px', 'auto'],
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
                url: baseUrl + '/configCategorys?status=1',
                data: {status: 1},
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
                    groupData = 1;
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
    })
    exports('set/config', {})
});
