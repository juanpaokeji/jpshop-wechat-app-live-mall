/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/5/13
 * js 供货商商品
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单

        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'merchantGoods';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'username', title: '供应商'},
            {field: 'pic_urls', title: '商品图片', templet: '#imgTpl'},
            {field: 'name', title: '商品名称'},
            {field: 'price', title: '商品价格'},
            {field: 'stocks', title: '商品库存'},
            {field: 'is_check', title: '审核状态', templet: '#is_checkTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        /*diy设置结束*/

        //执行审核通过或不通过操作
        form.on('submit(sub)', function (e) {
            var examine = $(e.elem).attr('data');
            var is_check = 2;
            var price_str = '';
            if (examine === '1') {
                is_check = 1;
                var prices = $('input[name=price]');
                var p_len = prices.length;
                for (var i = 0; i < p_len; i++) {
                    if (Trim($(prices[i]).val()) === '') {
                        layer.msg('请填写价格', {icon: 1, time: 2000});
                        return;
                    }
                    if (i === 0) {
                        price_str += $(prices[i]).attr('class') + ':' + $(prices[i]).val();
                    } else {
                        price_str += ',' + $(prices[i]).attr('class') + ':' + $(prices[i]).val();
                    }
                }
            }
            arr = {
                method: 'merchantGoodAudit/' + operation_id,
                type: 'put',
                params: 'audit=3',
                data: {
                    is_check: is_check,
                    price_str: price_str
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('请求成功', {icon: 1, time: 2000}, function () {
                    location.reload();
                });
            }
        });

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            $('.examine_div').hide();
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'show') {//查看详情
                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get',
                    params: 'audit=3'
                };
                var res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    //判断该状态是否待审核，如果是显示审核按钮并且价格可填写，如果不是，删除审核按钮并且价格只展示
                    if (res.data.is_check === '0') {
                        $('.examine_div').show();
                    } else {
                        $('.examine_div').hide();
                    }
                    /*diy设置开始*/
                    $(".name").html(res.data.name);
                    $(".short_name").html(res.data.short_name);
                    $(".code").html(res.data.code);
                    var pic_urls_arr = res.data.pic_urls.split(',');
                    if (pic_urls_arr.length > 0) {
                        var p_u_a_l = pic_urls_arr.length
                        for (var i = 0; i < p_u_a_l; i++) {
                            if (pic_urls_arr[i]) {
                                $('.pic_urls').append('<img src="' + pic_urls_arr[i] + '">');
                            }
                        }
                    }
                    $(".m_category_id").html(res.data.m_category_name);//
                    $(".label").html(res.data.label.split(',').join(' '));
                    $(".simple_info").html(res.data.simple_info);
                    $(".detail_info").html(res.data.detail_info);//
                    $(".price").html(parseFloat(res.data.price));
                    $(".line_price").html(parseFloat(res.data.line_price));
                    $(".stocks").html(res.data.stocks);
                    $('.stock').empty();
                    if (res.data.stock.length > 0) {
                        var stock = res.data.stock;
                        var stock_len = stock.length;
                        for (var s = 0; s < stock_len; s++) {
                            var price_div = '价格：' + stock[s].price + '<br/>';
                            if (res.data.is_check === '0') {
                                price_div = '价格：<input class="' + stock[s].id + '" name="price"><br/>';
                            }
                            $('.stock').append(stock[s].property1_name + '，' +
                                stock[s].property2_name + '，' +
                                '原价：' + parseFloat(stock[s].cost_price) + '，' +
                                '库存：' + stock[s].number + ' ' +
                                '商品编码：' + stock[s].code + '<br/>' +
                                price_div
                            );
                        }
                    }
                    /*diy设置结束*/

                    form.render();//设置完值需要刷新表单
                    open_index = layer.open({
                        type: 1,
                        title: '编辑',
                        content: add_edit_form,
                        shade: 0,
                        offset: '100px',
                        area: ['600px', 'auto'],
                        cancel: function () {
                            add_edit_form.hide();
                        }
                    })
                }
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method + '?key=' + saa_key + '&audit=3',//audit 0未审核 1审核通过 2审核不通过 3全部
            cols: [cols],//加载的表格字段
        };
        var render = getTableRender(arr);//变量名对应 arr 中的 name

        //搜索
        form.on('submit(find)', function (data) {//查询
            render.reload({
                where: {searchName: data.field.searchName},
                page: {curr: 1}
            });
        });

    });
    exports('goods/supplierGoods', {})
});
