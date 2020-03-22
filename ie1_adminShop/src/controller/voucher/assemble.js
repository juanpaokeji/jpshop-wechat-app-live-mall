/**
 * Created by 卷泡
 * author: wjr <272074691@qq.com>
 * Created DateTime: 2019/8/27 14:15
 * 拼团
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
        var saa_key = sessionStorage.getItem('saa_key');
        var arr, res;

        //进入营销菜单必须执行方法，获取该应用的自定义版权状态，如果为1则显示自定义版权，为0则需要隐藏
        //之前写在layout里，太消耗性能，所以写在营销菜单下的所有页面里
        arr = {
            method: 'merchantCopyright',
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data && res.data.copyright && res.data.copyright === '1') {
            if ($('.copyright_li').length <= 0) {
                $('.voucher_ul').append('<li class="copyright_li"><a lay-href="voucher/copyright">自定义版权</a></li>');
            }
        } else {
            $('.copyright_li').remove();
        }

        //加载商品列表
        var key = '?key=' + saa_key;
        var cols = [//加载的表格
            {field: 'goods_id', title: '商品ID',width: '10%'},
            {field: 'name', title: '商品名称'},
            {field: 'format_time', title: '时间',width: '20%'},
            {field: 'status', title: '状态',templet: '#statusTpl',width: '10%'}
        ];
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable1',//需要加载的 table 表格对应的 id
            method: "merchantAssembleGoods" + key,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols],//加载的表格字段
        };
        var render = getTableRender(arr);//变量名对应 arr 中的 name

        //监听Tab切换
        var tabId = 1;
        layui.element.on('tab(assemble_list)', function (data) {
            tabId = this.getAttribute('lay-id');
            if (tabId == '1') {
                cols = [//加载的表格
                    {field: 'goods_id', title: '商品ID',width: '10%'},
                    {field: 'name', title: '商品名称'},
                    {field: 'format_time', title: '时间',width: '20%'},
                    {field: 'status', title: '状态',templet: '#statusTpl',width: '10%'}
                ];
                arr = {
                    name: 'render',//可操作的 render 对象名称
                    elem: '#pageTable1',//需要加载的 table 表格对应的 id
                    method: "merchantAssembleGoods" + key,//请求的 api 接口方法和可能携带的参数 key
                    cols: [cols],//加载的表格字段
                };
                var render = getTableRender(arr);//变量名对应 arr 中的 name
                //商品搜索
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
                });
            } else if (tabId == '2') {
                cols = [//加载的表格
                    {field: 'order_sn', title: '订单ID',width: '20%'},
                    {field: 'goodsname', title: '商品名称'},
                    {field: 'nickname', title: '买家昵称',width: '10%'},
                    {field: 'type', title: '拼团类型',templet: '#statusTp2',width: '10%'},
                    {field: 'format_create_time', title: '加入时间',width: '15%'},
                    {field: 'assemble_status', title: '状态',templet: '#statusTp3',width: '10%'}
                ];
                arr = {
                    name: 'render',//可操作的 render 对象名称
                    elem: '#pageTable2',//需要加载的 table 表格对应的 id
                    method: "merchantAssembleOrder" + key,//请求的 api 接口方法和可能携带的参数 key
                    cols: [cols],//加载的表格字段
                };
                var render = getTableRender(arr);//变量名对应 arr 中的 name

                //订单搜索
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
                });

            } else if (tabId == '3') {
                cols = [//加载的表格
                    {field: 'id', title: 'ID',width: '5%'},
                    {field: 'goodsname', title: '商品名称'},
                    {field: 'nickname', title: '团长昵称',width: '10%'},
                    {field: 'format_create_time', title: '开团时间',width: '15%'},
                    {field: 'son_num', title: '已参团人数',width: '10%', templet: function (d) {
                        return "<div class='son_num' data-id='" + d.id + "' lay-event='click'>" + d.son_num + "</div>";
                    }},
                    {field: 'number', title: '成团人数',width: '10%'},
                    {field: 'assemble_status', title: '状态',templet: '#statusTp4',width: '10%'}
                ];
                arr = {
                    name: 'render',//可操作的 render 对象名称
                    elem: '#pageTable3',//需要加载的 table 表格对应的 id
                    method: "merchantAssembleAssemble" + key,//请求的 api 接口方法和可能携带的参数 key
                    cols: [cols],//加载的表格字段
                };
                var render = getTableRender(arr);//变量名对应 arr 中的 name
                //管理搜索
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
                });
            }
        });

        //商品搜索
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
        });

        //点击已成团事件
        var count_form = $('#count_form');
        $(document).off('click','.son_num').on('click','.son_num',function () {
            var id = $(this).attr("data-id");
            cols = [//加载的表格
                {field: 'nickname', title: '昵称'},
                {field: 'name', title: '姓名'},
                {field: 'phone', title: '手机号'},
                {field: 'assemble_status', title: '订单状态',templet: '#statusTp5'},
                {field: 'format_create_time', title: '下单时间'},
            ];
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTableCount',//需要加载的 table 表格对应的 id
                method: 'merchantAssembleAssemble/' + id + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
                cols: [cols],//加载的表格字段
            };
            getTableRender(arr);//变量名对应 arr 中的 name
            layer.open({
                type: 1,
                title: '参团人员信息',
                content: count_form,
                shade: 0,
                offset: '100px',
                area: ['800px', '600px'],
                cancel: function () {
                    count_form.hide();
                }
            });
        })

    });
    exports('voucher/assemble', {})
});
