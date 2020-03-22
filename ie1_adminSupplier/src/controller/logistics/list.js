/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/9/19 10:00  一直在更新，时间随时修改
 * js 物流列表
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
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');
        var operationId;

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantShopExpress";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var cols = [//加载的表格
            {field: 'name', title: '快递名称', width: '20%'},
            {field: 'remarks', title: '备注', width: '20%'},
            {field: 'status', title: '状态', width: '20%', templet: '#statusTpl'},
            {field: 'create_time', title: '创建时间', width: '20%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '20%'}
        ];
        var ruleCols = [//加载的表格
            {checkbox: true},
            {field: 'name', title: '快递名称', width: "12%"},
            {field: 'simple_name', title: '缩写', width: "12%"},
        ];
        var expressArr = [];//保存的快递数组
        var expressList = '';//所有快递列表
        /*diy设置结束*/

        // //显示新增窗口
        // form.on('submit(showAdd)', function () {
        //     /*diy设置开始*/
        //     table.render({
        //         elem: '#expressTable',
        //         url: baseUrl + '/merchantShopExpressCompany',
        //         limit: 1000,
        //         where: {},
        //         loading: true,
        //         cols: [ruleCols],
        //         response: {
        //             statusName: 'status', //数据状态的字段名称，默认：code
        //             statusCode: "200", //成功的状态码，默认：0
        //             dataName: 'data' //数据列表的字段名称，默认：data
        //         },
        //         headers: headers,
        //         done: function (res) {
        //             if (res.status == timeOutCode) {
        //                 layer.msg(timeOutMsg);
        //                 admin.exit();
        //                 return false;
        //             }
        //             if (res.status != 200) {
        //                 layer.msg(res.message);
        //                 return false;
        //             }
        //             // checkbox 默认选中 需要获取当前商户已选择的快递 expressArr 和快递组 res，循环 res 判断 expressArr 是否存在
        //             $.ajax({
        //                 url: url + key,
        //                 data: {},
        //                 type: 'get',
        //                 async: false,
        //                 headers: headers,
        //                 beforeSend: function () {
        //                     loading = layer.load(loadType, loadShade);//显示加载图标
        //                 },
        //                 success: function (expressRes) {
        //                     if (expressRes.status == timeOutCode) {
        //                         layer.msg(timeOutMsg);
        //                         admin.exit();
        //                         return false;
        //                     }
        //                     layer.close(loading);//关闭加载图标
        //                     if (expressRes.status != 200) {
        //                         layer.msg(expressRes.message);
        //                         return false;
        //                     }
        //                     var length = expressRes.data.length;
        //                     var express = [];
        //                     for (var i = 0; i < length; i++) {
        //                         express.push(expressRes.data[i].system_express_id)
        //                     }
        //                     var tr = document.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        //                     expressArr = [];
        //                     //设置该 checkbox 选中
        //                     for (var i = 0; i < tr.length; i++) {
        //                         var id = res.data[i].id;//当前标签对应的 id
        //                         if (express.indexOf(id) > -1) {
        //                             //表示在数组内，设置该样式
        //                             tr[i].getElementsByTagName('td')[0].getElementsByTagName('div')[1].className = 'layui-unselect layui-form-checkbox layui-form-checked';
        //                             tr[i].getElementsByTagName('td')[0].getElementsByTagName('input')[0].checked = true;
        //                             expressArr.push(id);
        //                         }
        //                     }
        //                     expressList = res;
        //                 },
        //                 error: function () {
        //                     layer.msg(errorMsg);
        //                     layer.close(loading);
        //                 }
        //             })
        //             expressList = res;
        //         }
        //     });
        //     /*diy设置结束*/
        //
        //     form.render();//设置完值需要刷新表单
        //     openIndex = layer.open({
        //         type: 1,
        //         title: '新增',
        //         content: $('#add_edit_form'),
        //         shade: 0,
        //         offset: '100px',
        //         area: ['400px', '60vh'],
        //     })
        // })

        //显示新增窗口
        var expressRender;
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            expressArr = [];//新增的时候将保存的快递列表清空
            /*diy设置开始*/
            expressRender = table.render({
                elem: '#expressTable',
                url: baseUrl + '/merchantShopExpressCompany' + key,
                limit: 1000,
                loading: true,
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
                }
            });
            /*diy设置结束*/

            form.render();//设置完值需要刷新表单
            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: $('#add_edit_form'),
                shade: 0,
                offset: '100px',
                area: ['20vw', '60vh'],
                cancel: function () {
                    $('#add_edit_form').hide();
                }
            })
        })

        //搜索弹窗中的快递列表
        form.on('submit(findExpress)', function (data) {
            expressRender.reload({
                where: {
                    searchName: data.field.expressName
                },
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
                    expressList = res;
                }
            })
        })

        //点击 checkbox 执行事件
        table.on('checkbox(expressTable)', function (obj) {
            if (obj.type == 'all') {
                //点击全选执行
                expressArr = [];
                if (obj.checked == true) {
                    //将所有数据存入数组 expressList 为权限的请求数组
                    for (var i = 0; i < expressList['count']; i++) {
                        expressArr.push(expressList['data'][i]['id']);
                    }
                }
            } else {
                //选择单条执行
                if (obj.checked == true) {
                    //将该选择数据存入数组
                    expressArr.push(obj.data.id);
                } else {
                    //删除该选择元素
                    var arrIndex = expressArr.indexOf(obj.data.id);
                    if (arrIndex > -1) {
                        expressArr.splice(arrIndex, 1);
                    }
                }
            }
        });

        //执行快递选择
        form.on('submit(save)', function () {
            if (expressArr == false) {
                expressArr = '';
            }

            $.ajax({
                url: url + key,
                type: 'post',
                async: false,
                data: {
                    express: expressArr,
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
                        layer.msg(res.message);
                        return false;
                    }
                    layer.msg(sucMsg.put);
                    layer.close(openIndex);
                    $('#add_edit_form').hide();
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

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var layEvent = obj.event;
            operationId = obj.data.id;
            if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    $.ajax({
                        url: url + '/' + obj.data.id,
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
                data: {
                    status: statusCode,
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
    exports('logistics/list', {})
});
