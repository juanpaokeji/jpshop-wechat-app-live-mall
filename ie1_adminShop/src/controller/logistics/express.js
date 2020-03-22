/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/9/19 10:00  一直在更新，时间随时修改
 * js 快递模板
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
        var saa_key = sessionStorage.getItem('saa_key');
        var operationId;
        var ajaxType;
        sessionStorage.removeItem('expressEdit');//清除编辑判断session
        sessionStorage.removeItem('expressId');//加载列表删除编辑的id

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantShopExpressTemplate";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var cols = [//加载的表格
            {field: 'name', title: '模板名称', width: '20%'},
            {field: 'type', title: '计费方式', width: '20%', templet: '#typeTpl'},
            {field: 'status', title: '状态', width: '20%', templet: '#statusTpl'},
            {field: 'create_time', title: '创建时间', width: '20%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '20%'}
        ];
        var groupData = 0;//是否已加载分组 是 1 否 0
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='add_edit_status']").prop('checked', true);//还原状态设置为true

            /*diy设置开始*/
            $("input[name='app_id']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            form.render();//还原后需要重置表单
            ajaxType = 'post';//设置类型为新增
            //下拉请求接口必须，未请求过，则请求接口并保存，已请求过，获取保存的信息，减少加载时间
            if (!groupData) {
                getGroups(0);
            } else {
                var category = document.getElementById('unfixedSelects');
                category.options[0].selected = true;
            }
            /*diy设置结束*/

            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: $('#add_edit_form'),
                shade: 0,
                offset: '100px',
                area: ['400px', 'auto'],
            })
        })

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var status = 0;
            var subData;
            var ajaxUrl = url;
            if ($('input[name=add_edit_status]:checked').val()) {
                status = 1;
            }
            if (ajaxType == 'post') {
                successMsg = sucMsg.post;
            } else if (ajaxType == 'put') {
                ajaxUrl = url + '/' + operationId;
                successMsg = sucMsg.put;
            }

            /*diy设置开始*/
            subData = {
                name: $('input[name=name]').val(),
                category_id: $('select[name=category_name]').val(),
                detail_info: $('textarea[name=detail_info]').val(),
                type: $('input[name=applicationType]:checked').val(),
                status: status,
                key: saa_key
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
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg(successMsg);
                    layer.close(openIndex);
                    $("#add_edit_form")[0].reset();//表单重置
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
            operationId = data.id;
            if (layEvent === 'edit') {//修改
                sessionStorage.setItem('expressId', operationId);
                return location.hash = "/logistics/add";
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    $.ajax({
                        url: url + '/' + data.id,
                        data: {key: saa_key},
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
                                if (res.status != 204) {
                                    layer.msg(res.message);
                                }
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
                url: baseUrl + '/merchantPost' + key,
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
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
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
                            $('select[name=unfixedSelects]').append("<option value=" + id + selected + ">" + name + "</option>");
                        } else {
                            $('select[name=unfixedSelects]').append("<option value=" + id + ">" + name + "</option>");
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
            url: url + key,
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
                    if (res.status != 204) {
                        layer.msg(res.message);
                    }
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
        $(document).off('click', '.status_open').on('click', '.status_open', function () {
            $.ajax({
                url: baseUrl + "/merchantShopExpressTemplates/" + $(this).attr('data'),
                type: 'put',
                async: false,
                data: {
                    status: 1,
                    key: saa_key
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
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg(sucMsg.put, {icon: 1, time: 1000});
                    layer.close(openIndex);
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

    })
    exports('logistics/express', {})
});
