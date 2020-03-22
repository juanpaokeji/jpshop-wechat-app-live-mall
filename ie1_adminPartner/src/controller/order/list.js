/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/9/19 10:00  一直在更新，时间随时修改
 * js 订单列表
 */

var shansong = 0;//是否开启闪送，1 开启 0 未开启

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laydate', 'element', 'upload', 'laypage'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var layDate = layui.laydate;
        var element = layui.element;
        var upload = layui.upload;//上传图片
        var layPage = layui.laypage;
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var refuseRefundOpenIndex;//定义退款弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');
        var operationId;//保存操作的列表id
        var order_sn;//保存操作的订单编号
        var data_arr;
        var groupData = 0;//是否已加载分组 是 1 否 0
        var pageLimit = 10;
        form.render();

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/partnerOrder";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var tabId = '';//获取当前选项卡 默认全部
        var tabPage = '1';//获取当前分页的页数
        /*diy设置结束*/

        tabPage = '1';
        getList();
        //默认列表分页
        getPage();
        form.render();

        //日期
        layDate.render({
            elem: '#start_time',
            type: 'datetime'
        });
        layDate.render({
            elem: '#end_time',
            type: 'datetime'
        });

        var curDate = new Date();//未格式化时间
        var todayTime = new Date().format("yyyy-MM-dd hh:mm:ss");//今天时间
        var sevenDayAgo = new Date(curDate.getTime() - 7 * 24 * 36E5).format("yyyy-MM-dd hh:mm:ss");//7天前时间
        var thirtyDayAgo = new Date(curDate.getTime() - 30 * 24 * 36E5).format("yyyy-MM-dd hh:mm:ss");//30天前时间
        //按钮选择日期事件 7天
        $(document).off('click', '.seven_day').on('click', '.seven_day', function (e) {
            $('input[name=start_time]').val(sevenDayAgo);
            $('input[name=end_time]').val(todayTime);
        });

        //按钮选择日期事件 30天
        $(document).off('click', '.thirty_day').on('click', '.thirty_day', function (e) {
            $('input[name=start_time]').val(thirtyDayAgo);
            $('input[name=end_time]').val(todayTime);
        });

        //监听Tab切换
        element.on('tab(tab)', function () {
            tabId = this.getAttribute('lay-id');
            $("select[name=status]").val(tabId);
            tabPage = '1';
            getList();
            //默认列表分页
            getPage();
            form.render();
        });

        //通过调用获取 render
        var total_count = 0;

        var is_page = 0;

        //获取订单列表方法
        function getList() {
            total_count = 0;
            var getData = {
                searchNameType: $('#searchNameType').val(),
                searchName: $('input[name=searchName]').val(),
                user_id: sessionStorage.getItem('orderId') ? sessionStorage.getItem('orderId') : $('input[name=user_id]').val(),
                goods_name: $('input[name=goods_name]').val(),
                status: $('#status').val(),
                after_sale: $('#after_sale').val(),
                start_time: $('input[name=start_time]').val(),
                end_time: $('input[name=end_time]').val(),
                page: tabPage,
                limit: pageLimit
            };
            // var group_id = $('input[name=group_id]').val();
            // if (Trim(group_id) !== '') {
            //     getData.leader_uid = group_id;
            // }
            $.ajax({
                url: url,
                type: 'get',
                data: getData,
                async: false,
                headers: headers,
                success: function (res) {
                    layer.close(loading);//关闭加载图标
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    //当主订单数据存在时进行循环添加到页面
                    $('tbody').empty();
                    if (res.status != 200) {
                        if (res.status != 204) {
                            layer.msg(res.message)
                        }
                        return false;
                    }
                    total_count = res.count;
                    shansong = res.shansong;
                    if (!res.data) {
                        layer.msg('没有订单');
                        return;
                    }
                    var data = res.data;
                    //将数据添加到js中保存，后面操作会用到（例如 退款详情）
                    data_arr = data;

                    for (var i = 0; i < data.length; i++) {
                        var orderGroupHtml = getOrderGroup(data[i]);
                        $('tbody').append(orderGroupHtml);
                    }
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);//关闭加载图标
                },
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                }
            });
            sessionStorage.removeItem('orderId')
        }

        //默认列表分页
        function getPage() {
            layPage.render({
                elem: 'page' //注意，这里的 test1 是 ID，不用加 # 号
                , count: total_count //数据总数，从服务端得到
                , prev: '<'
                , next: '>'
                , limit: pageLimit
                , limits: limits
                , layout: ['prev', 'page', 'next', 'refresh', 'skip', 'limit']
                , jump: function (obj, first) {
                    //obj包含了当前分页的所有参数，比如：
                    // console.log(obj.curr); //得到当前页，以便向服务端请求对应页的数据。
                    // console.log(obj.limit); //得到每页显示的条数
                    pageLimit = obj.limit;
                    is_page = 1;
                    tabPage = obj.curr;
                    //首次不执行
                    if (!first) {
                        getList();
                    }
                }
            });
        }

        //搜索
        form.on('submit(find)', function (data) {
            //只设置当前选中订单状态对应的 tab ，不重复请求
            $('.layui-tab').find('li').each(function () {
                //先删除所有 li 标签的 class ，然后添加指定 li 的 class
                $(this).removeAttr('class');
                tabId = this.getAttribute('lay-id');
                if (tabId === data.field.status) {
                    $(this).attr('class', 'layui-this');
                }
            });
            tabPage = '1';
            getList();
            getPage();
        });

    });
    exports('order/list', {})
});

//获取总订单 div
function getOrderGroup(data) {
    var status;
    var back_color = '#f0ad4e';
    var operation;
    var shop_price = data.total_price - data.express_price;
    var show_order = '<a class="layui-btn layui-btn-xs show_order" style="display: none;">查看</a>\n';//查看按钮，暂时先隐藏
    var order_status = '';
    if (data.status == '0') {
        status = '待付款';
        back_color = '#fd0b0b';
        operation = show_order +
            '    <a class="layui-btn layui-btn-xs layui-btn-danger cancel_order">取消订单</a>\n' +
            '    <a class="layui-btn layui-btn-xs layui-btn-danger update_price">修改价格</a>';
    } else if (data.status == '1') {
        status = '待发货';
        back_color = '#1588fe';
        var shansong_send = '';
        if (shansong === '1') {
            shansong_send = '<a class="layui-btn layui-btn-xs shansong">闪送</a>';
        }
        operation = show_order +
            '    <a class="layui-btn layui-btn-xs send_order" data="' + data.express_type + '">发货</a>\n' + shansong_send +
            '    <a class="layui-btn layui-btn-xs refund_order">一键退款</a>';
    } else if (data.status == '2') {
        status = '已取消';
        back_color = '#b8b9b8';
        operation = show_order;
    } else if (data.status == '3') {
        status = '已发货';
        back_color = '#fbd039';
        operation = show_order +
            '    <a class="layui-btn layui-btn-xs refund_order">一键退款</a>';
    } else if (data.status == '4') {
        status = '已退款';
        operation = show_order;
    } else if (data.status == '5') {
        status = '退款中';
        back_color = '#fd0b0b';
        operation = show_order +
            '    <a class="layui-btn layui-btn-xs show_after_sale">退款详情</a>\n' +
            '    <a class="layui-btn layui-btn-xs refund_order">一键退款</a>';
    } else if (data.status == '6') {
        status = '待评价';
        operation = show_order;
    } else if (data.status == '7') {
        status = '已完成';
        back_color = '#02d20c';
        operation = show_order;
    } else if (data.status == '8') {
        status = '已删除';
        operation = show_order;
    } else if (data.status == '9') {
        status = '退款成功';
        operation = show_order;
    } else if (data.status == '11') {
        status = '拼团中';
        operation = show_order;
        order_status = '<b style="color: #009688;">拼团</b>';
    } else {
        status = '类型错误';
        operation = '';
    }
    //判断是否拼团商品
    if (data.is_assemble === '1') {
        order_status = '<b style="color: #009688;">拼团</b>';
    }
    //判断是否拼团商品
    if (data.is_bargain === '1') {
        order_status = '<b style="color: #009688;">砍价</b>';
    }
    //如果已存在备注，则不同颜色显示
    if (data.group_admin_remark && Trim(data.group_admin_remark) !== '') {
        operation += '<a class="layui-btn layui-btn-xs write_remark" style="background-color: limegreen">已备注</a>';
    } else {
        operation += '<a class="layui-btn layui-btn-xs write_remark">备注</a>';
    }
    var status_div = '<span class="label" style="background-color: ' + back_color + '">' + status + '</span>';//状态加颜色显示
    //自提点信息
    var realname = data.realname ? data.realname : '';
    var leader_phone = data.leader_phone ? data.leader_phone : '';
    var area_name = data.area_name ? data.area_name : '';
    var pcaa = (data.province ? data.province : '') + (data.city ? data.city : '') + (data.area ? data.area : '') + (data.addr ? data.addr : '');
    //如果存在自提点，那么表示可以修改自提点
    if (data.realname && data.leader_phone && (data.status === '0' || data.status === '1')) {
        operation += '<a class="layui-btn layui-btn-xs update_group">修改自提点</a>';
    }
    operation += '<a class="layui-btn layui-btn-xs print">打印</a>';
    //送货方式
    var express_type = '类型错误';
    if (data.express_type === '0') {
        express_type = '快递' + '<br/>';
        express_type += '<div>' + (data.express_name ? data.express_name : "") + '</div>';
        express_type += '<div>' + (data.express_number ? data.express_number : "") + '</div>';
    } else if (data.express_type === '1') {
        express_type = '自提';
    } else if (data.express_type === '2') {
        express_type = '团长送货';
    }
    //将子订单数据循环添加到页面
    var orders = '';
    if (!data.order) {
        layer.msg('没有订单', {icon: 1, time: 2000});
        orders = '暂无订单';
        return;
    }
    var order = data.order;
    for (var j = 0; j < order.length; j++) {
        orders += getOrder(order[j]);
    }
    //截取字符串，去除邮编
    var address = data.address;
    var last_index = address.lastIndexOf('-');
    var address_show = address.substring(0, last_index);
    return '                <tr class="child-title">\n' +
        '                    <td colspan="8" class="child-title-td">\n' +
        '                        <span style="margin-right: 20px; font-size: 14px">' + order_status + '</span>\n' +
        '                        订单号: <span style="font-weight:bolder">' + data.order_sn + '</span>\n' +
        '                        创建时间: <span style="font-weight:bolder">' + data.create_time + '</span>\n' +
        '                        <span style="margin-left: 10px">自提点信息' +
        '                            姓名：<span style="font-weight:bolder">' + realname + '</span>' +
        '                            电话：<span style="font-weight:bolder">' + leader_phone + '</span>' +
        '                            小区：<span style="font-weight:bolder">' + area_name + '</span>' +
        '                            地址：<span style="font-weight:bolder">' + pcaa + '</span>' +
        '                        </span>\n' +
        '                    </td>\n' +
        '                </tr>\n' +
        '                <tr>\n' +
        '                    <td class="td-choose" style="width:35px;">\n' +
        '                        <div style="text-align:center;" class="hiMallDatagrid-cell-check ">\n' +
        '                            <input type="checkbox">\n' +
        '                        </div>\n' +
        '                    </td>\n' +
        '                    <td style="width:40px;">\n' +
        '                        <div style="text-align:center;" class="hiMallDatagrid-cell">' + data.id + '</div>\n' +
        '                    </td>\n' +
        '                    <td style="width:140px;">\n' +
        '                        <div class="hiMallDatagrid-cell p_order">' + orders + '</div>\n' +
        '                    </td>\n' +
        '                    <td style="width:140px;">\n' +
        '                        <div style="text-align:center;" class="hiMallDatagrid-cell ">\n' +
        '                            <div class="img-list">\n' +
        '                                <div><span class="img-list-frist">昵称:</span><span>' + data.nickname + '</span> [<span>' + data.user_id + '</span>]\n' +
        '                                </div>\n' +
        '                                <div><span class="img-list-two">姓名:</span>' + data.name + '</div>\n' +
        '                                <div><span class="img-list-three">电话:</span>' + data.phone + '</div>\n' +
        '                                <div><span class="img-list-four">地址:</span>' + address_show + '</div>\n' +
        '                                <div>\n' +
        '                                    <span class="img-list-five">买家留言:</span>\n' +
        '                                    <span class="overflow-ellipsis" title="remark">' + data.remark + '</span>\n' +
        '                                </div>\n' +
        '                            </div>\n' +
        '                        </div>\n' +
        '                    </td>\n' +
        '                    <td style="width:70px;">\n' +
        '                        <div class="hiMallDatagrid-cell ">' + express_type + '</div>\n' +
        '                    </td>\n' +
        '                    <td style="width:30px;">\n' +
        '                        <div class="hiMallDatagrid-cell ">\n' +
        '                            <!-- <span class="label label-success ">微信支付</span> -->\n' +
        '                            <div style="margin-top:5px;"><span>商品总价:</span>' + shop_price + '</div>\n' +
        '                            <div><span>配送费:</span>' + data.express_price + '</div>\n' +
        '                            <div><span>订单总价:</span>' + data.total_price + '</div>\n' +
        '                            <div><span>总优惠:</span>' + (data.total_price - data.payment_money) + '</div>\n' +
        '                            <span class="ftx-04" style="text-align:left;position:relative">实付金额:￥' + data.payment_money + '</span>\n' +
        '                        </div>\n' +
        '                    </td>\n' +
        '                    <td style="width:70px;">\n' +
        '                        <div class="hiMallDatagrid-cell ">' + status_div + '</div>\n' +
        '                    </td>\n' +
        '                    <td class="td-operate td-lg" style="width:150px;">\n' +
        '                        <div class="hiMallDatagrid-cell ">\n' +
        '                            <span class="order_id_hide" style="display: none;">' + data.id + '</span>\n' +
        '                            <span class="order_sn_hide" style="display: none;">' + data.order_sn + '</span>\n' +
        '                            <span class="btn-a operation">' + operation + '</span>\n' +
        '                        </div>\n' +
        '                    </td>\n' +
        '                </tr>';
}

//获取子订单 div
function getOrder(order) {
    return '                        <div class="img-list c_order">\n' +
        '                                <img src="' + order.pic_url + '">\n' +
        '                                <span class="overflow-ellipsis" style="width:220px">\n' +
        '                                        <span title="' + order.name + '">' + order.name + '</span>\n' +
        '                                    </span>\n' +
        '                                <p>\n' +
        '                                    <span>\n' +
        '                                        ￥<span>' + order.price + '</span>\n' +
        '                                        &nbsp;x&nbsp;\n' +
        '                                        <span>' + order.number + '</span>\n' +
        '                                    </span>\n' +
        '                                    <span style="color:red;padding-left:20px;">\n' +
        '                                        小计:￥<span>' + order.total_price + '</span>\n' +
        '                                    </span>\n' +
        '                                    <br/><span>\n' +
        '                                        <span>' + order.property1_name + '</span>\n' +
        '                                    </span>\n' +
        '                                    <span style="padding-left:20px;">\n' +
        '                                        <span>' + order.property2_name + '</span>\n' +
        '                                    </span>\n' +
        '                                </p>\n' +
        '                            </div>';
}
