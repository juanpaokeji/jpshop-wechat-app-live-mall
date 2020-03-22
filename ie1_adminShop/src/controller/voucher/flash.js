/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/5/17
 * js 插件-秒杀
 */

layui.define(function (exports) {
    /**
     * use 首参简单解释
     *
     * jquery 必须 很多地方那个用到，必须定义
     * setter 必须 获取config 配置，但不必定义
     * admin 必须 若未用到则不必定义
     * table 不必须 若表格渲染，若无表格操作点击事件，可不必定义
     * form 不必须 表单操作，一般用于页面有新增和编辑
     * laydate 不必须 日期选择器
     */
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var open_index3;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//秒杀列表数据表格操作需要用到单条 id
        var operation_goods_id;//已选择的商品数据表格操作需要用到单条 id
        var arr, res;//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        var flash_list = [];//获取到的秒杀数据列表

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

        form.render();
        /*diy设置开始*/
        var save_goods_ids = [];//最终需要提交的商品 id

        //页面不同属性
        var ajax_method = 'merchantFlashSale';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'id', title: '编号', width: '6%'},
            {field: 'name', title: '活动名称', width: '20%'},
            {field: 'detail_info', title: '描述', width: '17%'},
            {field: 'format_create_time', title: '创建时间', width: '12%'},
            {
                field: 'start_time', title: '活动时间', templet: function (d) {
                    return d.start_time + ' -- ' + d.end_time;
                }, width: '20%'
            },
            {
                field: 'state', title: '状态', width: '10%', templet: function (d) {
                    var view_status = '';
                    if (d.state === 1) {
                        view_status = '未开始';
                    } else if (d.state === 2) {
                        view_status = '进行中';
                    } else if (d.state === 3) {
                        view_status = '已结束';
                    } else if (d.state === 4) {
                        view_status = '未启动';
                    } else {
                        view_status = '类型错误';
                    }
                    return view_status;
                }
            },
            {field: 'operations', title: '操作', toolbar: '#operations', width: '15%'}
        ];

        //选择日期
        layDate.render({
            elem: '#start_time',
            type: 'datetime',
        });
        layDate.render({
            elem: '#end_time',
            type: 'datetime',
        });
        layDate.render({
            elem: '#send_time',
            type: 'datetime',
        });
        /*diy设置结束*/

        //获取秒杀总开关设置
        arr = {
            method: 'merchantSpike',
            type: 'get',
        };
        res = getAjaxReturnKey(arr);
        var sign_in_id = 0;
        if (res && res.data) {
            sign_in_id = res.data.id;
            if (res.data.is_open === '1') {
                $("input[name=sign_in_status]").prop('checked', true);
            } else {
                $("input[name=sign_in_status]").removeAttr('checked');
            }
            form.render();
        }
        //签到总开关
        form.on('switch(sign_in_status)', function (obj) {
            arr = {
                method: 'merchantSpike/' + sign_in_id,
                type: 'put',
                data: {
                    is_open: obj.elem.checked ? 1 : 0,
                },
            };
            if (getAjaxReturnKey(arr)) {
                layer.msg(sucMsg.put);
                layer.close(open_index);
            }
        });

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            save_goods_ids = [];
            flash_info = {};
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("#goods_list_form")[0].reset();//表单重置  必须
            $("#set_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            /*diy设置开始*/
            $("input[name='app_id']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            $("#image").attr('src', '');
            form.render();//还原后需要重置表单
            ajax_type = 'post';//设置类型为新增
            table.render({
                elem: '#orderTable'
                , cols: []
                , data: []
            });
            /*diy设置结束*/

            open_index = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0.1,
                offset: '100px',
                area: ['800px', '600px'],
                cancel: function () {
                    add_edit_form.hide();
                }
            })
        });

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var success_msg;
            var method = ajax_method;
            var start_time = $('input[name=start_time]').val();
            var end_time = $('input[name=end_time]').val();
            var send_time = $('input[name=end_time]').val();
            if (start_time >= end_time) {
                layer.msg('结束时间不能早于开始时间', {icon: 1, time: 2000});
                return;
            }
            if (start_time >= send_time) {
                layer.msg('发货时间不能早于开始时间', {icon: 1, time: 2000});
                return;
            }
            var goods_list_info = [];
            var num = 0;
            $('input[name=hide_value]').each(function () {
                if (Trim($(this).val()) === '') {
                    num++;
                } else {
                    goods_list_info.push(JSON.parse($(this).val()));
                }
            });
            if (ajax_type === 'post') {
                success_msg = sucMsg.post;
            } else if (ajax_type === 'put') {
                method += '/' + operation_id;
                success_msg = sucMsg.put;
            }
            if (num > 0) {
                layer.msg('请设置秒杀属性', {icon: 1, time: 2000});
                return;
            }
            if (goods_list_info.length <= 0) {
                layer.msg('请选择秒杀商品', {icon: 1, time: 2000});
                return;
            }
            for (var i = 0; i < goods_list_info.length; i++) {
                var f_p_arr = goods_list_info[i].flash_price;
                if (f_p_arr.length <= 0) {
                    layer.msg('请设置秒杀属性', {icon: 1, time: 2000});
                    return;
                }
            }
            arr = {
                method: method,
                type: ajax_type,
                data: {
                    name: $('input[name=name]').val(),
                    detail_info: $('input[name=detail_info]').val(),
                    start_time: start_time,
                    end_time: end_time,
                    send_time: send_time,
                    goods_list: goods_list_info
                }
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(success_msg, {icon: 1, time: 1000}, function () {
                    location.reload();
                });
                layer.close(open_index);
                add_edit_form.hide();
            }
        });

        var o_goods_info = {};//当前点击的秒杀活动对应的商品列表数据
        var data_list_form = $('#data_list_form');//常用的表单
        //秒杀表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            if (flash_list.length === 0 && result && result.data) {
                flash_list = result;
            }
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            for (var i = 0; i < flash_list.data.length; i++) {
                if (flash_list.data[i].id === data.id) {
                    o_goods_info = flash_list.data[i];
                }
            }

            if (layEvent === 'edit') {//修改
                ajax_type = 'put';
                /*diy设置开始*/
                $("input[name=name]").val(o_goods_info.name);
                $("input[name=detail_info]").val(o_goods_info.detail_info);
                $("input[name=start_time]").val(o_goods_info.start_time);
                $("input[name=end_time]").val(o_goods_info.end_time);
                $("input[name=send_time]").val(o_goods_info.send_time);
                form.render();//设置完值需要刷新表单
                /*diy设置结束*/
                //展示已知数据
                table.render({
                    elem: '#orderTable'
                    , cols: [[ //标题栏
                        {
                            field: 'goods_id', title: '商品id', sort: true, templet: function (d) {
                                var show_id;
                                if (d.copy_id != '0') {
                                    show_id = d.copy_id;
                                } else {
                                    show_id = d.goods_id;
                                }
                                return show_id;
                            }
                        }
                        , {
                            field: 'pic_urls', title: '商品主图', templet: function (d) {
                                return '<img src="' + d.pic_urls + '">'
                            }
                        }
                        , {field: 'name', title: '商品名称'}
                        , {
                            field: 'goods_id', title: '设置秒杀属性', templet: function (d) {
                                return '<a data="' + d.goods_id + '" class="layui-btn layui-btn-xs set_flash">设置</a><input name="hide_value" style="display: none;">';
                            }
                        }
                        , {field: 'operations_order', title: '操作', toolbar: '#operations_order'}
                    ]]
                    , data: o_goods_info.sale
                });

                //等待一秒后，设置列表隐藏框的值
                setTimeout(setGoodsListHideValue, 500);

                open_index = layer.open({
                    type: 1,
                    title: '编辑',
                    content: add_edit_form,
                    shade: 0.1,
                    offset: '100px',
                    area: ['800px', '600px'],
                    cancel: function () {
                        add_edit_form.hide();
                    }
                })
            } else if (layEvent === 'suspend') {//0 暂停 1启动
                arr = {
                    method: 'merchantFlashSaleGroup/' + data.id,
                    type: 'put',
                    data: {
                        status: '0'
                    }
                };
                res = getAjaxReturnKey(arr);
                if (res) {
                    layer.msg('活动已暂停', {icon: 1, time: 1000}, function () {
                        render.reload();//表格局部刷新
                    })
                }
            } else if (layEvent === 'start') {//0 暂停 1启动
                arr = {
                    method: 'merchantFlashSaleGroup/' + data.id,
                    type: 'put',
                    data: {
                        status: '1'
                    }
                };
                res = getAjaxReturnKey(arr);
                if (res) {
                    layer.msg('活动已开始', {icon: 1, time: 1000}, function () {
                        render.reload();//表格局部刷新
                    })
                }
            } else if (layEvent === 'data') {//展示数据
                //获取该秒杀对应的数据，据说是购买列表
                //默认加载列表
                arr = {
                    name: 'render',//可操作的 render 对象名称
                    elem: '#dataTable',//需要加载的 table 表格对应的 id
                    method: ajax_method + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
                    cols: [cols],//加载的表格字段
                };
                var render = getTableRender(arr);//变量名对应 arr 中的 name
                open_index3 = layer.open({
                    type: 1,
                    title: '编辑',
                    content: data_list_form,
                    shade: 0.1,
                    offset: '100px',
                    area: ['800px', '600px'],
                    cancel: function () {
                        data_list_form.hide();
                    }
                })
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    arr = {
                        method: ajax_method + '/' + data.id,
                        type: 'delete'
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

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols],//加载的表格字段
        };
        var render = getTableRender(arr);//变量名对应 arr 中的 name

        //搜索秒杀
        form.on('submit(find)', function (data) {//查询
            render.reload({
                where: {searchName: data.field.searchName, status: data.field.status},
                page: {curr: 1}
            });
        });

        var goods_arr = [];//获取到的商品列表（原始数据）
        var goods_arr_len = 0;
        var goods_arr_use = [];//新增秒杀活动使用到的商品列表
        var goods_arr_unuse = [];//新增秒杀活动未使用到的商品列表
        var open_index1;//添加商品的打开窗口
        var goods_list_form = $('#goods_list_form');//常用的表单
        var goodListTable;
        //点击新增窗口中添加商品事件
        $(document).off('click', '.add_goods').on('click', '.add_goods', function () {
            goods_list_form[0].reset();
            if (goods_arr.length <= 0) {
                //没有数据，获取商品列表
                arr = {
                    method: 'merchantGoods',
                    type: 'get',
                    data: {limit: 10000}
                };
                res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    goods_arr = res.data;
                }
            }
            goods_arr_len = goods_arr.length;
            goods_arr_use = [];
            goods_arr_unuse = [];
            for (var g = 0; g < goods_arr_len; g++) {
                var g_a_u = {
                    id: goods_arr[g].id,
                    pic_url: goods_arr[g].pic_urls,
                    name: goods_arr[g].name,
                    stocks: goods_arr[g].stocks
                };
                if (save_goods_ids.indexOf(goods_arr[g].id) !== -1) {
                    goods_arr_use.push(g_a_u);
                } else {
                    goods_arr_unuse.push(g_a_u);
                }
            }
            // if (save_goods_ids.length > 0) {
            //     for (var i = 0; i < goods_arr_use.length; i++) {
            //         var g_a_u_id = goods_arr_use[i].id;
            //         if (save_goods_ids.indexOf(g_a_u_id) !== -1) {
            //             goods_arr_use[i].LAY_CHECKED = 'true';
            //         }
            //     }
            // }
            //展示已知数据
            goodListTable = table.render({
                elem: '#goodsListTable'
                , cols: [[ //标题栏
                    {type: 'checkbox', width: '30%'}
                    , {field: 'id', title: '商品id', sort: true}
                    , {field: 'name', title: '商品名称'}
                    , {field: 'stocks', title: '商品数量'}
                ]]
                , data: goods_arr_unuse
                , page: true
            });
            open_index1 = layer.open({
                type: 1,
                title: '选择商品',
                content: goods_list_form,
                shade: 0.1,
                offset: '100px',
                area: ['600px', 'auto'],
                cancel: function () {
                    goods_list_form.hide();
                }
            });
        });

        //添加商品新增窗口点击复选框事件
        table.on('checkbox(goodsListTable)', function (obj) {
            if (obj.type == 'all') {
                //点击全选执行
                save_goods_ids = [];
                if (obj.checked == true) {
                    //将所有数据存入数组 ruleList 为权限的请求数组
                    for (var i = 0; i < goods_arr.length; i++) {
                        save_goods_ids.push(goods_arr[i]['id']);
                    }
                }
            } else {
                //选择单条执行
                if (obj.checked == true) {
                    //将该选择数据存入数组
                    save_goods_ids.push(obj.data.id);
                } else {
                    //删除该选择元素
                    var arrIndex = save_goods_ids.indexOf(obj.data.id);
                    if (arrIndex > -1) {
                        save_goods_ids.splice(arrIndex, 1);
                    }
                }
            }
        });

        //添加商品新增窗口点击保存事件
        form.on('submit(save)', function () {//查询
            if (save_goods_ids.length <= 0) {
                layer.msg('未选择商品', {icon: 1, time: 2000});
                return;
            }
            goods_arr_use = [];
            goods_arr_unuse = [];
            for (var g = 0; g < goods_arr_len; g++) {
                var g_a_u_id = goods_arr[g].id;
                var g_a_u = {
                    id: goods_arr[g].id,
                    pic_url: goods_arr[g].pic_urls,
                    name: goods_arr[g].name,
                    stocks: goods_arr[g].stocks
                };
                if (save_goods_ids.indexOf(g_a_u_id) !== -1) {
                    goods_arr_use.push(g_a_u);
                } else {
                    goods_arr_unuse.push(g_a_u);
                }
            }
            //获取已设置的 hide_value 值
            var hide_values = [];
            $('input[name=hide_value]').each(function () {
                if ($($(this)[0]).val() != '') {
                    hide_values.push(JSON.parse($($(this)[0]).val()));
                }
            });
            goods_list_form.hide();
            layer.close(open_index1);
            //新增窗口显示列表
            //展示已知数据
            table.render({
                elem: '#orderTable'
                , cols: [[ //标题栏
                    {field: 'id', title: '商品id', sort: true}
                    , {
                        field: 'pic_url', title: '商品主图', templet: function (d) {
                            var pic_url = Trim(d.pic_url) !== '' ? d.pic_url.split(",")[0] : '';
                            return '<img src="' + pic_url + '">'
                        }
                    }
                    , {field: 'name', title: '商品名称'}
                    , {
                        field: 'id', title: '设置秒杀属性', templet: function (d) {
                            return '<a data="' + d.id + '" class="layui-btn layui-btn-xs set_flash">设置</a><input name="hide_value" style="display: none;">';
                        }
                    }
                    , {field: 'operations_order', title: '操作', toolbar: '#operations_order'}
                ]]
                , data: goods_arr_use
            });
            //循环设置原有的隐藏值
            $('input[name=hide_value]').each(function () {
                var that = $(this)[0];
                var id = $($(that).prev()[0]).attr('data');
                for (var i = 0; i < hide_values.length; i++) {
                    if (id == hide_values[i].id) {
                        $(that).val(JSON.stringify(hide_values[i]));
                    }
                }
            })
        });

        //添加商品新增窗口点击查询事件
        form.on('submit(findGoods)', function (data) {//查询
            var searchNameGoods = data.field.searchNameGoods;
            var gau_len = goods_arr_unuse.length;
            var search_goods_arr = [];
            for (var i=0; i<gau_len;i++) {
                var id = goods_arr_unuse[i].id;
                var name = goods_arr_unuse[i].name;
                if (id === searchNameGoods || name.indexOf(searchNameGoods) !== -1) {
                    search_goods_arr.push(goods_arr_unuse[i])
                }
            }
            goodListTable = table.render({
                elem: '#goodsListTable'
                , cols: [[ //标题栏
                    {type: 'checkbox', width: '30%'}
                    , {field: 'id', title: '商品id', sort: true}
                    , {field: 'name', title: '商品名称'}
                    , {field: 'stocks', title: '商品数量'}
                ]]
                , data: search_goods_arr
                , page: true
            });
        });

        //已选择商品表格操作点击事件
        table.on('tool(orderTable)', function (obj) {
            var layEvent = obj.event;
            if (layEvent === 'del') {
                //需要将 save_goods_ids 对应的 id 删除
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    obj.del();
                    var delete_id;
                    if (ajax_type === 'post') {
                        delete_id = obj.data.id;
                    } else if (ajax_type === 'put') {
                        if (obj.data.copy_id) {
                            delete_id = obj.data.copy_id;
                        } else {
                            delete_id = obj.data.id;
                        }
                    }
                    deleteSpecifiedElement(save_goods_ids, delete_id);
                })
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        var flash_info = {};//设置商品秒杀信息时需要存入文本框中的信息
        var open_index2;//已添加商品的设置功能打开窗口
        var set_form = $('#set_form');//常用的表单
        //点击设置按钮执行事件    获取商品信息，并存入变量中
        $(document).off('click', '.set_flash').on('click', '.set_flash', function () {
            var id = $(this).attr('data');
            operation_goods_id = id;
            arr = {
                method: 'merchantGoods/' + id,
                type: 'get',
            };
            res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var data = res.data;
                var hide_input_val = $($(this).next()[0]).val();
                if (hide_input_val !== '' && hide_input_val != '0') {
                    flash_info = JSON.parse(hide_input_val);
                } else {
                    //如果为空，则判断是否有编辑的数据，如果有，设置为 flash_info
                    var flash_id = [];
                    var flash_original_price = [];
                    var flash_price = [];
                    var flash_number = [];
                    var flash_property1_name = [];
                    var flash_property2_name = [];
                    if (o_goods_info.id) {
                        var sale = [];
                        var properties = [];
                        for (var j = 0; j < o_goods_info.sale.length; j++) {
                            if (id == o_goods_info.sale[j].goods_id) {
                                sale = o_goods_info.sale[j];
                                flash_info.sale_id = sale.id;
                                properties = sale.property.split('-');
                                if (properties.length > 0) {
                                    for (var p = 0; p < properties.length; p++) {
                                        var property = JSON.parse(properties[p]);
                                        flash_id.push(property.stock_id);
                                        flash_original_price.push(property.flash_original_price);
                                        flash_price.push(property.flash_price);
                                        flash_number.push(property.stocks);
                                        flash_property1_name.push(property.property1_name);
                                        flash_property2_name.push(property.property2_name);
                                    }
                                }
                                break;
                            }
                        }
                    }
                    flash_info.flash_id = flash_id;
                    flash_info.flash_original_price = flash_original_price;
                    flash_info.flash_price = flash_price;
                    flash_info.flash_number = flash_number;
                    flash_info.property1_name = flash_property1_name;
                    flash_info.property2_name = flash_property2_name;
                }

                flash_info.id = id;
                flash_info.pic_url = Trim(data.pic_urls) !== '' ? data.pic_urls.split(",")[0] : '';
                flash_info.name = data.name;
                flash_info.is_top = data.is_top;
                var flash_original_price_arr = [];
                var flash_price_arr = [];
                var flash_number_arr = [];
                if (o_goods_info.id) {
                    for (var o = 0; o < o_goods_info.sale.length; o++) {
                        if (id == o_goods_info.sale[o].goods_id) {
                            flash_info.sale_id = o_goods_info.sale[o].id;
                            break;
                        }
                    }
                    // flash_price_arr = flash_info.flash_price;
                    // flash_number_arr = flash_info.flash_number;
                }
                //判断隐藏框是否为空，如果为空，则未设置过，如果不为空则已设置
                if (hide_input_val !== '' && flash_info.flash_price.length > 0) {
                    flash_original_price_arr = flash_info.flash_original_price;
                    flash_price_arr = flash_info.flash_price;
                    flash_number_arr = flash_info.flash_number;
                }
                //获取规格，循环添加到页面
                var stock_len = data.stock.length;
                var already_choice_list = $('.already_choice_list');
                already_choice_list.empty();
                for (var i = 0; i < stock_len; i++) {
                    already_choice_list.append(getFlashDiv(
                        data.stock[i],
                        flash_original_price_arr.length === stock_len ? flash_original_price_arr[i] : 0,
                        flash_price_arr.length === stock_len ? flash_price_arr[i] : 0,
                        flash_number_arr.length === stock_len ? flash_number_arr[i] : 0
                    ));//循环添加规格属性文本框
                }
                $('input[name=hide_value]').each(function () {
                    if (operation_goods_id == $($(this).prev()[0]).attr('data')) {
                        $($(this)[0]).val(JSON.stringify(flash_info));
                    }
                });
                //打开新窗口，显示规格列表
                open_index2 = layer.open({
                    type: 1,
                    title: '设置',
                    content: set_form,
                    shade: 0.1,
                    offset: '100px',
                    area: ['600px', 'auto'],
                    cancel: function () {
                        set_form.hide();
                    }
                });
            }
        });

        //商品秒杀信息设置点击保存事件
        form.on('submit(set_save)', function () {//查询
            var save_set_value = {};
            $('input[name=hide_value]').each(function () {
                if (operation_goods_id == $($(this).prev()[0]).attr('data')) {
                    save_set_value = JSON.parse($($(this)[0]).val());
                }
            });
            // var save_set_value = JSON.parse($(set_div).val());
            var flash_id = [];
            var flash_name = [];
            var property1_name = [];
            var property2_name = [];
            var flash_original_price = [];
            var flash_price = [];
            var flash_number = [];
            $('input[name=flash_id]').each(function () {
                flash_id.push($(this).val());
            });
            $('input[name=flash_name]').each(function () {
                flash_name.push($(this).val());
            });
            $('input[name=property1_name]').each(function () {
                property1_name.push($(this).val());
            });
            $('input[name=property2_name]').each(function () {
                property2_name.push($(this).val());
            });
            var original_price_num = 0;
            $('input[name=flash_original_price]').each(function () {
                if ($(this).val() < 0) {
                    original_price_num++;
                } else {
                    flash_original_price.push($(this).val());
                }
            });
            if (original_price_num > 0) {
                layer.msg('原价必须大于等于零', {icon: 1, time: 2000});
                return;
            }
            var price_num = 0;
            $('input[name=flash_price]').each(function () {
                if ($(this).val() < 0) {
                    price_num++;
                } else {
                    flash_price.push($(this).val());
                }
            });
            if (price_num > 0) {
                layer.msg('秒杀金额必须大于等于零', {icon: 1, time: 2000});
                return;
            }
            var number_num = 0;
            $('input[name=flash_number]').each(function () {
                if ($(this).val() <= 0) {
                    number_num++;
                } else {
                    flash_number.push($(this).val());
                }
            });
            if (number_num > 0) {
                layer.msg('秒杀数量必须大于零', {icon: 1, time: 2000});
                return;
            }
            save_set_value.flash_id = flash_id;
            save_set_value.flash_name = flash_name;
            save_set_value.property1_name = property1_name;
            save_set_value.property2_name = property2_name;
            save_set_value.flash_price = flash_price;
            save_set_value.flash_original_price = flash_original_price;
            save_set_value.flash_number = flash_number;
            //获取到当前点击设置的这一条数据的隐藏框赋值
            $('input[name=hide_value]').each(function () {
                if (operation_goods_id == $($(this).prev()[0]).attr('data')) {
                    $($(this)[0]).val(JSON.stringify(save_set_value));
                }
            });
            layer.close(open_index2);
            set_form.hide();
        });

        //文本框点击后内容全选事件
        $(document).off('click', '.flash_input').on('click', '.flash_input', function () {
            $(this).select();
        });


        function setGoodsListHideValue() {
            if (o_goods_info.id) {
                save_goods_ids = [];
                for (var j = 0; j < o_goods_info.sale.length; j++) {
                    var sale = [];
                    var flash_id = [];
                    var flash_original_price = [];
                    var flash_price = [];
                    var flash_number = [];
                    var flash_property1_name = [];
                    var flash_property2_name = [];
                    var properties = [];
                    sale = o_goods_info.sale[j];
                    // flash_info.sale_id = sale.id;
                    properties = sale.property;
                    if (properties.length > 0) {
                        for (var p = properties.length - 1; p >= 0; p--) {
                            // var property = JSON.parse(properties[p]);
                            var property = properties[p];
                            flash_id.push(property.stock_id);
                            flash_original_price.push(property.flash_original_price);
                            flash_price.push(property.flash_price);
                            flash_number.push(property.stocks);
                            flash_property1_name.push(property.property1_name);
                            flash_property2_name.push(property.property2_name);
                        }
                    }
                    flash_info.flash_id = flash_id;
                    flash_info.flash_original_price = flash_original_price;
                    flash_info.flash_price = flash_price;
                    flash_info.flash_number = flash_number;
                    flash_info.property1_name = flash_property1_name;
                    flash_info.property2_name = flash_property2_name;

                    flash_info.id = sale.goods_id;
                    flash_info.copy_id = sale.copy_id;
                    flash_info.pic_url = sale.pic_urls;
                    flash_info.name = sale.name;
                    flash_info.is_top = sale.is_top;
                    $($('input[name=hide_value]')[j]).val(JSON.stringify(flash_info));//将对象转成json格式字符串并存入隐藏框

                    save_goods_ids.push(sale.copy_id);//保存当前商品的copy_id，否则查询商品列表会出现当前商品
                }
            }
        }

    });
    exports('voucher/flash', {})
})
;

//设置新增窗口中获取规格div
function getFlashDiv(d, flash_original_price, flash_price, flash_number) {
    return '        <div class="layui-form-item">\n' +
        '                <div class="layui-input-block">\n' +
        '                    <span>' + d.property1_name + '  ' + d.property2_name + '</span>\n' +
        '                    <span>价格：' + parseFloat(d.price) + '</span>\n' +
        '                    <span>数量：' + parseFloat(d.number) + '</span>\n' +
        '                    <br/>\n' +
        '                    <input name="flash_id" value="' + d.id + '" style="display: none;">\n' +
        '                    <input name="property1_name" value="' + d.property1_name + '" style="display: none;">\n' +
        '                    <input name="property2_name" value="' + d.property2_name + '" style="display: none;">\n' +
        '                    <span>原价：<input class="flash_input" name="flash_original_price" value="' + flash_original_price + '" lay-verify="required|number"></span>\n' +
        '                    <span>秒杀价格：<input class="flash_input" name="flash_price" value="' + flash_price + '" lay-verify="required|number"></span>\n' +
        '                    <span>秒杀数量：<input class="flash_input" name="flash_number" value="' + flash_number + '" lay-verify="required|number"></span>\n' +
        '                </div>\n' +
        '            </div>';
}
