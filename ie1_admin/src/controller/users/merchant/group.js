/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/9 10:10  一直在更新，时间随时修改
 * js 角色管理
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

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/groups";//当前页面主要使用 url
        var cols = [//加载的表格
            {field: 'title', title: '角色名', width: "20%"},
            {field: 'rules', title: '权限', width: "20%",},
            {field: 'create_time', title: '创建时间', width: "20%",},
            {field: 'status', title: '状态', width: "20%", templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: "20%"}
        ];
        var ruleCols = [//加载的表格
            {checkbox: true},
            {field: 'title', title: '权限名称', width: "15%"},
            {field: 'condition', title: '触发条件', width: "20%"}
        ];
        var ruleArr = [];//保存的权限数组
        var ruleList = '';
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='add_edit_status']").prop('checked', true);//还原状态设置为true

            /*diy设置开始*/
            $('input[name=add_edit_type]').val('add');//设置类型为新增
            /*diy设置结束*/

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

            /*diy设置开始*/
            subData = {
                title: $('input[name=title]').val(),
                type: 2,
                status: status,
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
            var data = obj.data;
            var layEvent = obj.event;
            $("input[name='operationId']").val(data.id);
            if (layEvent === 'rules') {//权限
                /*diy设置开始*/
                table.render({
                    elem: '#ruleTable',
                    url: baseUrl + '/rules',
                    limit: 100,
                    where: {type: 2, status: 1},
                    loding: true,
                    cols: [ruleCols],
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
                        //checkbox 默认选中 需要获取当前角色 rules 和权限组 res，循环 res 判断 rules是否存在
                        var rules = data.rules;
                        var length = rules.length;
                        var lastStr = rules.substr(length - 1, length);
                        if (lastStr == ',') {
                            rules = rules.substr(0, length - 1);//去除最后一个
                        }
                        rules = rules.split(',');//将字符串转为数组
                        var tr = document.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                        console.log(res.data[0].id);
                        ruleArr = [];
                        //设置该 checkbox 选中
                        for (var i = 0; i < tr.length; i++) {
                            var id = res.data[i].id;//当前标签对应的 id
                            if (rules.indexOf(id) > -1) {
                                //表示在数组内，设置该样式
                                tr[i].getElementsByTagName('td')[0].getElementsByTagName('div')[1].className = 'layui-unselect layui-form-checkbox layui-form-checked';
                                tr[i].getElementsByTagName('td')[0].getElementsByTagName('input')[0].checked = true;
                                ruleArr.push(id);
                            }
                        }
                        ruleList = res;
                    }
                });
                /*diy设置结束*/

                form.render();//设置完值需要刷新表单
                openIndex = layer.open({
                    type: 1,
                    title: '权限',
                    content: $('#rule_form'),
                    shade: 0,
                    offset: '100px',
                    area: ['auto', '650px'],
                    cancel: function () {
                        $('#rule_form').hide();
                    }
                })
            } else if (layEvent === 'edit') {//修改
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
                        if (res.data.status == 1) {
                            $("input[name=add_edit_status]").prop('checked', true);
                        } else {
                            $("input[name=add_edit_status]").removeAttr('checked');
                        }
                        /*diy设置结束*/

                        form.render();//设置完值需要刷新表单
                        openIndex = layer.open({
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


        //执行权限选择
        form.on('submit(save)', function () {
            if (ruleArr == false) {
                ruleArr = '';
            }

            $.ajax({
                url: url + "/" + $("input[name='operationId']").val(),
                type: 'put',
                async: false,
                data: {rules: ruleArr},
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
                    $('#rule_form').hide();
                    render.reload();//表格局部刷新
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                },
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                }
            })
        })

        //点击 checkbox 执行事件
        table.on('checkbox(ruleTable)', function (obj) {
            if (obj.type == 'all') {
                //点击全选执行
                ruleArr = [];
                if (obj.checked == true) {
                    //将所有数据存入数组 ruleList 为权限的请求数组
                    for (var i = 0; i < ruleList['count']; i++) {
                        ruleArr.push(ruleList['data'][i]['id']);
                    }
                }
            } else {
                //选择单条执行
                if (obj.checked == true) {
                    //将该选择数据存入数组
                    ruleArr.push(obj.data.id);
                } else {
                    //删除该选择元素
                    var arrIndex = ruleArr.indexOf(obj.data.id);
                    if (arrIndex > -1) {
                        ruleArr.splice(arrIndex, 1);
                    }
                }
            }
        });

        //以下基本不动
        //加载列表
        var render = table.render({
            elem: '#pageTable',
            url: url,
            page: true, //开启分页
            limit: limit,
            limits: limits,
            where: {type: 2},
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
                    searchName: data.field.searchName, type: 2
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
    exports('users/merchant/group', {})
});
