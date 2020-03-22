/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/9/5 15:00
 * js 设计订单
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
        var operationId;
        /*diy设置开始*/
        // 页面不同属性
        var url = baseUrl + "/designOrder";//当前页面主要使用 url
        var cols = [//加载的表格
            {field: 'id', title: '设计编号', width: '10%'},
            {field: 'tb_order_id', title: '淘宝订单号', width: '10%'},
            {field: 'wangwang', title: '旺旺', width: '10%'},
            {field: 'design_img', title: '设计图片', templet: '#imgTpl', width: '5%'},
            {field: 'is_download', title: '是否下载', templet: '#isDownLoadTpl', width: '10%'},
            {field: 'remark', title: '备注', width: '10%'},
            {field: 'status', title: '状态', templet: '#statusTpl', width: '10%'},
            {field: 'create_time', title: '创建时间', width: '15%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '20%'}
        ];
        /*diy设置结束*/
        // 执行编辑
        form.on('submit(sub)', function () {
            /*diy设置开始*/
            var subData = {
                tb_order_id: $('input[name=tb_order_id]').val(),
                wangwang: $('input[name=wangwang]').val()
            }
            /*diy设置结束*/

            $.ajax({
                url: baseUrl + '/designOrderup/' + operationId,
                data: subData,
                type: 'put',
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
                    layer.msg(sucMsg.put);
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

        // 表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            operationId = data.id;
            var layEvent = obj.event;
            if (layEvent === 'pic') {


            } else if (layEvent === 'downloadd') {/*下载图片*/
                var canvas = document.createElement("canvas");
                var ctx = canvas.getContext("2d");
                var a = document.createElement('a');
                var img = new Image();
                img.crossOrigin = "Anonymous"
                img.src = data.design_img;
                canvas.width = 472;
                canvas.height = 189;
                img.onload = function () {
                    ctx.drawImage(img, 0, 0)
                    a.href = canvas.toDataURL('image/png');
                    a.download = data.id;//修改文件名
                    a.click();//文件名为默认文件名
                }
            } else if (layEvent === 'edit') {//编辑
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
                        $("span[name=id]").html(data.id);
                        $("input[name=tb_order_id]").val(res.data.tb_order_id);
                        $("input[name=wangwang]").val(res.data.wangwang);
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
            } else if (layEvent === 'del') {//删除
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

        //点击今日获取今天的设计单
        form.on('submit(today)', function () {
            render.reload({
                where: {
                    searchName: 'today'
                },
                page: {
                    curr: 1
                }
            })
        })

        //点击昨日获取昨天的设计单
        form.on('submit(yesterday)', function () {
            render.reload({
                where: {
                    searchName: 'yesterday'
                },
                page: {
                    curr: 1
                }
            })
        })

        // 以下基本不动
        // 加载列表

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

        // 搜索
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
    })
    exports('order', {})
});
