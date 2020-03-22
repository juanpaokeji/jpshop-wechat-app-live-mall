/**
 * Created by 卷泡
 * author: wjr <272074691@qq.com>
 * Created DateTime: 2019/10/6
 * 砍价
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
        var arr, res, cols;
        var saa_id = sessionStorage.getItem('saa_id');

        var pic_urls = '';//最终存入数据库的 图片地址字符串
        var random;
        var open_index;//定义弹出层，方便关闭

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

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }

        //加载砍价配置
        var key = '?key=' + saa_key;

        function getConfig() {
            arr = {
                method: 'merchantAppInfo/' + saa_id,
                type: 'get'
            };
            res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var data = res.data;
                //轮播图图展示
                if (data.bargain_rotation !== '') {
                    var bargain_rotation = data.bargain_rotation.substr(0, data.bargain_rotation.length - 1).split(',');
                    $('#images').empty();
                    for (var p in bargain_rotation) {
                        random = Math.round(Math.random() * 1e9);
                        $('#images').append('<div class="images"><img src="' + bargain_rotation[p] + '" alt="gu' + random + '" class="layui-upload-img bargainImg"><i class="deleteIcon layui-icon-close layui-icon"></i></div>')
                    }
                }
                $('#image1').empty();
                if (data.bargain_poster === '') {
                    $('#image1').append('<img src="https://api2.juanpao.com/uploads/bargain_poster.jpg" width="150px" height="150px">');
                } else {
                    $('#image1').append('<img src="' + data.bargain_poster + '" width="150px" height="150px">');
                }
            }
        }

        getConfig();

        //监听Tab切换
        var tabId = 1;
        var render;
        layui.element.on('tab(assemble_list)', function (data) {
            tabId = this.getAttribute('lay-id');
            if (tabId === '1') {
                getConfig();
            } else if (tabId === '2') {
                cols = [//加载的表格
                    {
                        field: 'pic_urls', title: '商品图片', templet: function (d) {
                            var pic_urls_1 = d.pic_urls.split(',')[0];
                            return '<img class="imgClickEvent" src="' + pic_urls_1 + '" style="width: 80px">';
                        }
                    },
                    {field: 'name', title: '商品名称'},
                    {field: 'format_bargain_start_time', title: '活动开始时间'},
                    {field: 'format_bargain_end_time', title: '活动结束时间'},
                    {
                        field: 'is_buy_alone', title: '是否支持单独购买', templet: function (d) {
                            return d.is_buy_alone === '1' ? '是' : '否';
                        }
                    },
                    {field: 'fictitious_initiate_bargain', title: '虚拟发起砍价人数量'},
                    {field: 'fictitious_help_bargain', title: '虚拟帮砍人数量'},
                    {field: 'bargain_price', title: '砍价最低价'},
                    {field: 'help_number', title: '帮砍次数'},
                    {field: 'bargain_limit_time', title: '砍价时间限制(小时)'},
                    {field: 'operations_goods', title: '操作', toolbar: '#operations_goods'}
                ];
                arr = {
                    name: 'render',//可操作的 render 对象名称
                    elem: '#pageTable1',//需要加载的 table 表格对应的 id
                    method: "merchantBargain" + key,//请求的 api 接口方法和可能携带的参数 key
                    cols: [cols]//加载的表格字段
                };
                render = getTableRender(arr);//变量名对应 arr 中的 name

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
            } else if (tabId === '3') {
                //发起砍价记录
                cols = [//加载的表格
                    {field: 'pic_url', title: '商品图片', templet: '#imgTpl'},
                    {field: 'goods_name', title: '商品名称'},
                    {field: 'cost_price', title: '商品价格'},
                    {field: 'price', title: '商品当前价格'},
                    {field: 'format_create_time', title: '创建时间'},
                    {field: 'operations', title: '操作', toolbar: '#operations'}
                ];
                arr = {
                    name: 'render1',//可操作的 render 对象名称
                    elem: '#pageTable2',//需要加载的 table 表格对应的 id
                    method: "merchantBargainInfo" + key,//请求的 api 接口方法和可能携带的参数 key
                    cols: [cols],//加载的表格字段
                };
                getTableRender(arr);//变量名对应 arr 中的 name
            } else if (tabId === '4') {
                //砍价订单
                cols = [//加载的表格
                    {field: 'order_sn', title: '订单ID'},
                    {field: 'goodsname', title: '商品名称'},
                    {field: 'payment_money', title: '购买时价格'},
                    {field: 'name', title: '买家昵称'},
                    {field: 'format_create_time', title: '下单时间'},
                    {field: 'status', title: '状态',templet: '#statusTp3'}
                ];
                arr = {
                    name: 'render2',//可操作的 render 对象名称
                    elem: '#pageTable3',//需要加载的 table 表格对应的 id
                    method: 'merchantBargainOrder' + key,//请求的 api 接口方法和可能携带的参数 key
                    cols: [cols]//加载的表格字段
                };
                getTableRender(arr);//变量名对应 arr 中的 name
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

        //活动管理显示新增商品窗口
        var goods_div = $('.goods_div');//常用的表单
        form.on('submit(showAdd)', function () {
            //获取可选择的商品列表
            var cols = [//加载的表格
                {field: 'radio', title: '单选', templet: function (d) {
                        return '<input type="radio" name="goods_radio" data="' + d.id + '">'
                    }, width: '4%'},
                {
                    field: 'pic_urls', title: '图片', templet: function (d) {
                        var pic_url_one = d.pic_urls.split(',')[0];
                        return '<img src="' + pic_url_one + '">';
                    }, width: '8%'
                },
                {field: 'name', title: '商品名称', width: '30%'},
                {field: 'price', title: '价格', width: '6%'}
            ];

            table.render({
                elem: '#pageTableGoods',
                url: baseUrl + '/merchantBargainGoods?key=' + saa_key,
                page: true, //开启分页
                limit: 10,
                limits: [10, 20, 30],
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
                    if (res.status !== 200) {
                        if (res.status !== 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    open_index = layer.open({
                        type: 1,
                        title: '新增',
                        content: goods_div,
                        shade: 0,
                        offset: '100px',
                        area: ['800px', '600px'],
                        cancel: function () {
                            goods_div.hide();
                        }
                    })
                }
            });
        });

        var goods_info = $('#goods_info');//常用的表单
        //点击填写砍价详情事件
        form.on('submit(add_goods_info)', function () {
            operation_goods_id = $('input[name=goods_radio]:checked').attr('data');
            if ($('input[name=goods_radio]:checked').length < 1) {
                layer.msg('请选择商品', {icon: 1, time: 2000});
                return;
            }
            layer.close(open_index);
            goods_div.hide();
            $('.bargain_info').empty().append(
                '<div class="layui-form-item">\n' +
                '                    <label class="layui-form-label"><span class="asterisk">*</span>活动时间</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" lay-verify="required" name="bargain_start_time"\n' +
                '                               id="bargain_start_time">\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" lay-verify="required" name="bargain_end_time"\n' +
                '                               id="bargain_end_time">\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '                <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label">支持单独购买</label>\n' +
                '                    <div class="layui-input-inline" style="width: 100px">\n' +
                '                        <input type="checkbox" name="is_buy_alone" lay-skin="switch" lay-text="是|否">\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '                <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label"><span class="asterisk">*</span>虚拟发起砍价数</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" name="fictitious_initiate_bargain" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '                <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label"><span class="asterisk">*</span>虚拟帮砍人数</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" name="fictitious_help_bargain" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '                <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label"><span class="asterisk">*</span>砍价最终底价</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" name="bargain_price" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 300px">\n' +
                '                        <span>必须大于0</span>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '                <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label"><span class="asterisk">*</span>好友帮砍次数</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" name="help_number" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 300px">\n' +
                '                        <span>限制该用户对当前商品最多可以帮砍次数</span>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '                <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label"><span class="asterisk">*</span>砍价时间限制</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" name="bargain_limit_time" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 300px">\n' +
                '                        <span>发起砍价后，最多砍价小时数，只能填写整数</span>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '                <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label">砍价规则</label>\n' +
                '                </div>\n' +
                '                <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label"><span class="asterisk">*</span>金额大于</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" name="bargain_list_price" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <label class="layui-form-label"><span class="asterisk">*</span>每次砍价</label>\n' +
                '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                '                        <input type="text" class="layui-input" name="bargain_min" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 20px;">\n' +
                '                        到\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                '                        <input type="text" class="layui-input" name="bargain_max" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <a href="javascript: void(0)" class="bargain_add" style="color: limegreen;">添加</a>\n' +
                '                </div>\n' +
                '                <div class="bargain_rule_list"></div>'
            );
            layDate.render({
                elem: '#bargain_start_time',
                type: 'datetime',
                trigger: 'click'
            });
            layDate.render({
                elem: '#bargain_end_time',
                type: 'datetime',
                trigger: 'click'
            });
            form.render();
            open_index = layer.open({
                type: 1,
                title: '砍价信息',
                content: goods_info,
                shade: 0,
                offset: '100px',
                area: ['800px', '600px'],
                cancel: function () {
                    goods_info.hide();
                }
            })
        });

        //添加砍价规则
        $(document).off('click', '.bargain_add').on('click', '.bargain_add', function () {
            $('.bargain_rule_list').append(
                '               <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label">金额大于</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" name="bargain_list_price" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <label class="layui-form-label">每次砍价</label>\n' +
                '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                '                        <input type="text" class="layui-input" name="bargain_min" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 20px;">\n' +
                '                        到\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                '                        <input type="text" class="layui-input" name="bargain_max" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <a href="javascript: void(0)" class="bargain_del" style="color: red;">删除</a>\n' +
                '                </div>'
            );
        });

        //删除砍价规则
        $(document).off('click', '.bargain_del').on('click', '.bargain_del', function () {
            $(this).parent().remove();
        });

        //保存编辑的商品 id
        var operation_goods_id = 0;
        //砍价详情保存事件
        form.on('submit(sub_goods)', function () {
            var subData = {};
            subData.is_bargain = 1;
            subData.bargain_start_time = $('input[name=bargain_start_time]').val();
            subData.bargain_end_time = $('input[name=bargain_end_time]').val();
            var is_buy_alone = 0;
            if ($('input[name=is_buy_alone]:checked').val()) {
                is_buy_alone = 1;
            }
            subData.is_buy_alone = is_buy_alone;
            subData.fictitious_initiate_bargain = $('input[name=fictitious_initiate_bargain]').val();
            subData.fictitious_help_bargain = $('input[name=fictitious_help_bargain]').val();
            if (parseFloat($('input[name=bargain_price]').val()) <= 0) {
                layer.msg('砍价最终底价必须大于0', {icon: 1, time: 2000});
            }
            subData.bargain_price = $('input[name=bargain_price]').val();
            subData.help_number = $('input[name=help_number]').val();
            subData.bargain_limit_time = $('input[name=bargain_limit_time]').val();
            //获取砍价规则对应的数据
            //砍价规则中的 金额大于
            var bargain_list_price = [];
            $('input[name=bargain_list_price]').each(function (i, j) {
                bargain_list_price.push(j.value);
            });
            //砍价规则中的 金额大于
            var bargain_min = [];
            $('input[name=bargain_min]').each(function (i, j) {
                bargain_min.push(j.value);
            });
            //砍价规则中的 金额大于
            var bargain_max = [];
            $('input[name=bargain_max]').each(function (i, j) {
                bargain_max.push(j.value);
            });
            subData.bargain_rule = {
                bargain_price: bargain_list_price,
                bargain_min: bargain_min,
                bargain_max: bargain_max
            };

            //请求接口
            arr = {
                method: 'merchantBargainGoods/' + operation_goods_id,
                type: 'put',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('保存成功', {icon: 1, time: 2000});
                layer.close(open_index);
                goods_info.hide();
                render.reload();//表格局部刷新
            }
        });

        //活动管理表格操作点击事件
        table.on('tool(pageTable1)', function (obj) {
            var data_obj = obj.data;
            var layEvent = obj.event;
            operation_goods_id = data_obj.goods_id;
            if (layEvent === 'edit') {//发起砍价记录
                //获取到帮砍记录，作为列表显示
                arr = {
                    method: 'merchantBargainGoods/' + data_obj.goods_id,
                    type: 'get'
                };
                res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    var data = res.data;
                    //判断是否开启砍价
                    var is_bargain_edit = data.is_bargain;
                    is_bargain = parseInt(is_bargain_edit);
                    if (is_bargain_edit === '1') {
                        $("input[name=is_bargain]").prop('checked', true);
                        var is_buy_alone = '';
                        if (data.is_buy_alone === '1') {
                            is_buy_alone = 'checked'
                        }
                        //循环设置砍价规则
                        var bargain_rule = data.bargain_rule;
                        var bargain_list_price = bargain_rule.bargain_price;
                        var bargain_min = bargain_rule.bargain_min;
                        var bargain_max = bargain_rule.bargain_max;
                        var bargain_rule_list = '';
                        for (var bl = 0; bl < bargain_list_price.length; bl++) {
                            if (bl === 0) {
                                bargain_rule_list += '<div class="layui-form-item">\n' +
                                    '                    <label class="layui-form-label"><span class="asterisk">*</span>金额大于</label>\n' +
                                    '                    <div class="layui-input-inline">\n' +
                                    '                        <input type="text" value="' + bargain_list_price[bl] + '" class="layui-input" name="bargain_list_price" lay-verify="required|number">\n' +
                                    '                    </div>\n' +
                                    '                    <label class="layui-form-label"><span class="asterisk">*</span>每次砍价</label>\n' +
                                    '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                                    '                        <input type="text" value="' + bargain_min[bl] + '" class="layui-input" name="bargain_min" lay-verify="required|number">\n' +
                                    '                    </div>\n' +
                                    '                    <div class="layui-input-inline" style="width: 20px;">\n' +
                                    '                        到\n' +
                                    '                    </div>\n' +
                                    '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                                    '                        <input type="text" value="' + bargain_max[bl] + '" class="layui-input" name="bargain_max" lay-verify="required|number">\n' +
                                    '                    </div>\n' +
                                    '                    <a href="javascript: void(0)" class="bargain_add" style="color: limegreen;">添加</a>\n' +
                                    '                </div>';
                            } else {
                                bargain_rule_list +=  '<div class="layui-form-item">\n' +
                                    '                    <label class="layui-form-label"><span class="asterisk">*</span>金额大于</label>\n' +
                                    '                    <div class="layui-input-inline">\n' +
                                    '                        <input type="text" value="' + bargain_list_price[bl] + '" class="layui-input" name="bargain_list_price" lay-verify="required|number">\n' +
                                    '                    </div>\n' +
                                    '                    <label class="layui-form-label"><span class="asterisk">*</span>每次砍价</label>\n' +
                                    '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                                    '                        <input type="text" value="' + bargain_min[bl] + '" class="layui-input" name="bargain_min" lay-verify="required|number">\n' +
                                    '                    </div>\n' +
                                    '                    <div class="layui-input-inline" style="width: 20px;">\n' +
                                    '                        到\n' +
                                    '                    </div>\n' +
                                    '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                                    '                        <input type="text" value="' + bargain_max[bl] + '" class="layui-input" name="bargain_max" lay-verify="required|number">\n' +
                                    '                    </div>\n' +
                                    '                    <a href="javascript: void(0)" class="bargain_del" style="color: red;">删除</a>\n' +
                                    '                </div>';
                            }
                        }
                        $('.bargain_info').empty().append(
                            '                <div class="layui-form-item">\n' +
                            '                    <label class="layui-form-label"><span class="asterisk">*</span>活动时间</label>\n' +
                            '                    <div class="layui-input-inline">\n' +
                            '                        <input type="text" class="layui-input" lay-verify="required" name="bargain_start_time"\n' +
                            '                               id="bargain_start_time" value="' + data.format_bargain_start_time + '">\n' +
                            '                    </div>\n' +
                            '                    <div class="layui-input-inline">\n' +
                            '                        <input type="text" class="layui-input" lay-verify="required" name="bargain_end_time"\n' +
                            '                               id="bargain_end_time" value="' + data.format_bargain_end_time + '">\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="layui-form-item">\n' +
                            '                    <label class="layui-form-label">支持单独购买</label>\n' +
                            '                    <div class="layui-input-inline" style="width: 100px">\n' +
                            '                        <input type="checkbox" name="is_buy_alone" ' + is_buy_alone + ' lay-skin="switch" lay-text="是|否">\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="layui-form-item">\n' +
                            '                    <label class="layui-form-label"><span class="asterisk">*</span>虚拟发起砍价数</label>\n' +
                            '                    <div class="layui-input-inline">\n' +
                            '                        <input type="text" value="' + data.fictitious_initiate_bargain + '" class="layui-input" name="fictitious_initiate_bargain" lay-verify="required|number">\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="layui-form-item">\n' +
                            '                    <label class="layui-form-label"><span class="asterisk">*</span>虚拟帮砍人数</label>\n' +
                            '                    <div class="layui-input-inline">\n' +
                            '                        <input type="text" value="' + data.fictitious_help_bargain + '" class="layui-input" name="fictitious_help_bargain" lay-verify="required|number">\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="layui-form-item">\n' +
                            '                    <label class="layui-form-label"><span class="asterisk">*</span>砍价最终底价</label>\n' +
                            '                    <div class="layui-input-inline">\n' +
                            '                        <input type="text" value="' + parseFloat(data.bargain_price) + '" class="layui-input" name="bargain_price" lay-verify="required|number">\n' +
                            '                    </div>\n' +
                            '                    <div class="layui-input-inline" style="width: 300px">\n' +
                            '                        <span>必须大于0</span>\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="layui-form-item">\n' +
                            '                    <label class="layui-form-label"><span class="asterisk">*</span>好友帮砍次数</label>\n' +
                            '                    <div class="layui-input-inline">\n' +
                            '                        <input type="text" value="' + data.help_number + '" class="layui-input" name="help_number" lay-verify="required|number">\n' +
                            '                    </div>\n' +
                            '                    <div class="layui-input-inline" style="width: 300px">\n' +
                            '                        <span>限制该用户对当前商品最多可以帮砍次数</span>\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="layui-form-item">\n' +
                            '                    <label class="layui-form-label"><span class="asterisk">*</span>砍价时间限制</label>\n' +
                            '                    <div class="layui-input-inline">\n' +
                            '                        <input type="text" value="' + data.bargain_limit_time + '" class="layui-input" name="bargain_limit_time" lay-verify="required|number">\n' +
                            '                    </div>\n' +
                            '                    <div class="layui-input-inline" style="width: 300px">\n' +
                            '                        <span>发起砍价后，最多砍价小时数，只能填写整数</span>\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="layui-form-item">\n' +
                            '                    <label class="layui-form-label">砍价规则</label>\n' +
                            '                </div>\n' +
                            '                <div class="bargain_rule_list">\n' +
                            bargain_rule_list +
                            '                </div>'
                        );
                        form.render();
                        layDate.render({
                            elem: '#bargain_start_time',
                            type: 'datetime',
                            trigger: 'click'
                        });
                        layDate.render({
                            elem: '#bargain_end_time',
                            type: 'datetime',
                            trigger: 'click'
                        });
                        open_index = layer.open({
                            type: 1,
                            title: '新增',
                            content: goods_info,
                            shade: 0,
                            offset: '100px',
                            area: ['800px', '600px'],
                            cancel: function () {
                                goods_info.hide();
                            }
                        })
                    } else {
                        layer.msg(res.message, {icon: 1, time: 2000});
                    }
                }
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    arr = {
                        method: 'merchantBargainGoods/' + data_obj.goods_id,
                        type: 'put',
                        data: {is_bargain: 0}
                    };
                    if (getAjaxReturnKey(arr)) {
                        layer.msg(sucMsg.delete, {icon: 1, time: 2000});
                        obj.del();
                    }
                })
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        //表格操作点击事件
        var pageTable2_form = $('#pageTable2_form');//常用的表单
        table.on('tool(pageTable2)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            if (layEvent === 'record') {//发起砍价记录
                //获取到帮砍记录，作为列表显示
                table.render({
                    elem: '#pageTable21'
                    , cols: [[ //标题栏
                        {field: 'avatar', title: '头像', templet: '#avatarImg'},
                        {field: 'nickname', title: '昵称'},
                        {field: 'price', title: '帮砍金额'},
                        {field: 'format_create_time', title: '帮砍时间'}
                    ]]
                    , data: data.bargin
                    , page: true
                });
                open_index = layer.open({
                    type: 1,
                    title: '新增',
                    content: pageTable2_form,
                    shade: 0,
                    offset: '100px',
                    area: ['600px', 'auto'],
                    cancel: function () {
                        pageTable2_form.hide();
                    }
                })
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        //上传图片现方法
        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '#putAddBtn').on('click', '#putAddBtn', function () {
            sessionStorage.setItem('images_common_div', '#images');
            sessionStorage.setItem('images_common_div_info', '<div class="images"><img src="" alt="gu' + Math.round(Math.random() * 1e9) + '" class="layui-upload-img bargainImg"><i class="deleteIcon layui-icon-close layui-icon"></i></div>');
            var num = 0;
            $('.goodsImg').each(function () {
                num++;
            });
            if (num < 5) {
                sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
                images_open_index_fun();
            } else {
                layer.msg('最多上传 5 张图片', {icon: 1});
            }
        });

        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.addImgPut').on('click', '.addImgPut', function () {
            sessionStorage.setItem('images_common_div', '#image1');
            sessionStorage.setItem('images_common_div_info', '<img width="150px" height="150px">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

        //删除图片按钮点击事件
        $(document).on("click", '.deleteIcon', function () {
            var parentNode = this.parentNode;
            layer.open({
                title: '删除',
                content: '确认删除这张图片吗',
                yes: function (index) {
                    //获取需要删除的 img
                    parentNode.remove();//删除页面显示图片的元素
                    layer.close(index);//关闭弹出窗
                }
            });
        });

        form.on('submit(sub_config)', function () {
            $('.bargainImg').each(function () {
                pic_urls += $(this).attr('src') + ',';
            });
            if (pic_urls === '') {
                layer.msg('请添加轮播图');
                return;
            }
            var subData = {
                bargain_rotation: pic_urls,
                bargain_poster: $('#image1 img').attr('src')
            };
            //提交修改
            arr = {
                method: 'merchantBargain/' + saa_id,
                type: 'put',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
            }
        });

        //点击图片打开预览
        $(document).off('click', '.imgClickEvent').on('click', '.imgClickEvent', function () {
            imgClickEvent(this)
        })

    });
    exports('voucher/bargain', {})
});
