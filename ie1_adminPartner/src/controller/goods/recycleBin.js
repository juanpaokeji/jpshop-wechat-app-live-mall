/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/9 10:10  一直在更新，时间随时修改
 * js 商品回收站
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
        var operationId;//当前操作 id ,编辑时可用到

        /*diy设置开始*/
        //页面不同属性
        var arr = [];
        var url = baseUrl + "/partnerRecycle";//当前页面主要使用 url
        var cols = [//加载的表格
            {field: 'id', title: '编号', width: '5%'},
            {field: 'sort', title: '序号', width: '5%'},
            {field: 'pic_urls', title: '图片', templet: '#imgTpl', width: '6%'},
            {field: 'name', title: '名称', width: '28%'},
            {field: 'price', title: '价格', width: '10%'},
            {field: 'stocks', title: '库存', width: '10%'},
            {field: 'number', title: '总销量', width: '10%'},
            {field: 'create_time', title: '创建时间', width: '16%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '10%'}
        ];
        /*diy设置结束*/

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operationId = data.id;
            if (layEvent === 'recovery') {
                layer.confirm('确定要恢复这个商品么?', function (index) {
                    layer.close(index);
                    $.ajax({
                        url: baseUrl + "/partnerGoodReduction" + '/' + operationId,
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
                            layer.close(loading);
                            if (res.status != 200) {
                                if (res.status != 204) {
                                    layer.msg(res.message);
                                }
                                return false;
                            }
                            layer.msg('恢复成功');
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
        });

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
                    if (res.status != 204) {
                        layer.msg(res.message);
                    }
                    return false;
                }
                var len = res.data.length;
                for (var i = 0; i < len; i++) {
                    var pic_urls = res.data[i].pic_urls;
                    res.data[i].pic_urls = pic_urls.split(',')[0];
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

        //点击图片打开预览
        $(document).off('click', '.imgClickEvent').on('click', '.imgClickEvent', function () {
            imgClickEvent(this);
        })

    });
    exports('goods/recycleBin', {})
});
