/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/8/10 18:00  一直在更新，时间随时修改
 * js 评价管理
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'admin', 'setter', 'form'], function () {
        var table = layui.table;
        var $ = layui.$;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var form = layui.form;
        var baseUrl = setter.baseUrl;
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');
        var operationId;

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantComment";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var cols = [//加载的表格
            {field: 'pic_url', title: '商品图片', templet: '#goodsImgTpl', width: '6%'},
            {field: 'name', title: '商品标题', width: '28%'},
            {field: 'nickname', title: '买家名称', width: '10%'},
            {field: 'content', title: '评论内容', width: '20%'},
            {
                field: 'pics_url', title: '评论图片', templet: function (d) {
                    if (d.pics_url) {
                        var pics_url = d.pics_url.split(',');
                        var pics_url_len = pics_url.length;
                        var return_str = '';
                        for (var p = 0; p < pics_url_len; p++) {
                            if (pics_url != '') {
                                return_str += '<img class="layui-upload-img imgClickEvent" style="width:40px; height:40px; cursor: pointer; margin-right: 0.5vw" src="' + pics_url[p] + '">'
                            }
                        }
                        return return_str;
                    } else {
                        return '';
                    }
                }, width: '16%'
            },
            {field: 'status', title: '状态', templet: '#statusTpl', width: '10%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '10%'}
        ];
        /*diy设置结束*/

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operationId = data.id;
            if (layEvent === 'del') {
                layer.confirm('确定要删除这条评价么?', function (index) {
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

        // //搜索
        // form.on('submit(find)', function (data) {
        //     render.reload({
        //         where: {
        //             searchName: data.field.searchName
        //         },
        //         page: {
        //             curr: 1
        //         }
        //     })
        // })

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
                    layer.close(loading);//关闭加载图标
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
                    layer.msg(sucMsg.put);
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
        });

        //点击图片打开预览
        $(document).off('click', '.imgClickEvent').on('click', '.imgClickEvent', function () {
            imgClickEvent(this);
        })

    })
    exports('order/evaluate', {})
});
