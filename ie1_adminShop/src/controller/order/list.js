/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/9/19 10:00  一直在更新，时间随时修改
 * js 订单列表
 */

var shansong = 0;//是否开启闪送，1 开启 0 未开启
var uu_is_open = 0;//是否开启UU跑腿，1 开启 0 未开启

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
        var arr, res;
        form.render();

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantOrder";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var tabId = '';//获取当前选项卡 默认全部
        var tabPage = '';//获取当前分页的页数
        /*diy设置结束*/

        //用于外部跳转请求，切换选项卡
        var order_list_tab_id = sessionStorage.getItem('order_list_tab_id');
        if (order_list_tab_id) {
            //order_list_tab_id 为当前请求对应的选项卡的id，不是订单状态
            tabId = order_list_tab_id;
            element.tabChange('tab', tabId);
            $("select[name=status]").val(tabId);
            sessionStorage.setItem('order_list_tab_id', '');//清除该session，否则每次进来都停留在当前选项卡
        } else {
            //刷新默认加载全部
            tabId = '';
        }
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

        //闪送日期
        layDate.render({
            elem: '#info_hi',
            type: 'time',
            trigger: 'click'//解决闪现问题
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

        var after_admin_imgs = '';//最终存入数据库的 图片地址字符串
        var urlsKey = [];
        var urlsValue = [];
        var urlsArr = {};
        var random;
        //多图片上传
        var uploadFlag = 1;//是否上传结束
        var uploadNum = 0;//已上传数量
        upload.render({
            elem: '#putAddBtn',
            url: baseUrl + '/merchantOrderImg',
            headers: headers,
            multiple: true,
            before: function (obj) {
                uploadFlag = 0;
                //预读本地文件示例，不支持ie8
                obj.preview(function (index, file, result) {
                    random = Math.round(Math.random() * 1e9);
                    urlsKey.push(random);
                    $('#images').append('<div class="images"><img src="' + result + '" alt="gu' + random + '" class="layui-upload-img goodsImg"><span class="deleteIcon ">&times;</span></div>')
                });
            },
            done: function (res) {
                //上传完毕
                if (res.code == 200) {
                    uploadNum++;
                    urlsValue.push(res.data.src);
                    var len = urlsKey.length;
                    for (var i = 0; i < len; i++) {
                        urlsArr[urlsKey[i]] = urlsValue[i];
                    }
                    if (uploadNum == len) {
                        uploadFlag = 1;
                    }
                }
            }
        });

        //删除图片按钮点击事件
        $(document).on("click", '.deleteIcon', function () {
            var parentNode = this.parentNode;
            var ran = $(this).prev('img').attr('alt');
            layer.open({
                title: '删除',
                content: '确认删除这张图片吗',
                yes: function (index) {
                    //获取需要删除的 img
                    ran = ran.substr(2, ran.length);//去除开头定义的两个字母
                    delete (urlsArr[ran]);//删除最终保存数组中对应的数据
                    parentNode.remove();//删除页面显示图片的元素
                    layer.close(index);//关闭弹出窗
                }
            });
        });

        //表格操作点击事件
        var is_send_after_sale;//是否已发货申请退款或退货 拒绝退款的时候用到
        var after_sale_type = 0;//退款类型 同意退款的时候用到

        //发货
        $(document).off('click', '.send_order').on('click', '.send_order', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            //如果是自提的发货，只需要请求接口将订单状态改为已发货
            if ($(this).attr('data') === '1') {
                layer.confirm('该订单无需填写物流可直接发货，确认发货吗?', function (index) {
                    layer.close(index);
                    var subData = {
                        order_sn: order_sn,
                        key: saa_key
                    };
                    $.ajax({
                        url: baseUrl + '/merchantSend',
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
                                if (res.status != 204) {
                                    layer.msg(res.message);
                                }
                                return false;
                            }
                            layer.msg('发货成功');
                            layer.close(openIndex);
                            $("#send_form")[0].reset();//表单重置
                            $('#send_form').hide();
                            getList();

                            //更新好物圈订单信息
                            getAjaxReturnKey({method: 'shopCircleOrder/' + order_sn, type: 'put'});
                        },
                        error: function () {
                            layer.msg(errorMsg);
                            layer.close(loading);
                        }
                    });
                });
                return;
            }

            //获取快递列表
            if (!groupData) {
                getGroups();
            }
            openIndex = layer.open({
                type: 1,
                title: '填写物流',
                content: $('#send_form'),
                shade: 0,
                offset: '20vw',
                area: ['25vw', 'auto'],
                cancel: function () {
                    $('#send_form').hide();
                }
            })
        });

        //闪送
        $(document).off('click', '.shansong').on('click', '.shansong', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            openIndex = layer.open({
                type: 1,
                title: '填写闪送发货信息',
                content: $('#shansong_form'),
                shade: 0,
                offset: '5vw',
                area: ['500px', '600px'],
                cancel: function () {
                    $('#shansong_form').hide();
                }
            })
        });

        //UU跑腿
        var uu_arr = {key: saa_key};//UU跑腿下单所需要的数据
        $(document).off('click', '.uuSend').on('click', '.uuSend', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            uu_arr.order_sn = order_sn;

            //计算订单价格
            $.ajax({
                url: baseUrl + '/merchantUuGetorderprice',
                data: {key: saa_key, order_sn: order_sn},
                type: 'post',
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
                            layer.msg(res.message, {icon: 1, time: 2000});
                        }
                        return;
                    }
                    uu_arr.price_token = res.data.price_token;
                    uu_arr.order_price = res.data.total_money;
                    uu_arr.balance_paymoney = res.data.need_paymoney;
                    $("input[name=uu_AccountMoney]").val(res.data.AccountMoney);
                    $("input[name=uu_total_money]").val(res.data.total_money);
                    $("input[name=uu_need_paymoney]").val(res.data.need_paymoney);

                    openIndex = layer.open({
                        type: 1,
                        title: '填写UU跑腿发货信息',
                        content: $('#uuSend_form'),
                        shade: 0,
                        offset: '5vw',
                        area: ['500px', '600px'],
                        cancel: function () {
                            $('#uuSend_form').hide();
                        }
                    })

                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

        //取消订单
        $(document).off('click', '.cancel_order').on('click', '.cancel_order', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            layer.confirm('确定要取消该订单吗?', function (index) {
                layer.close(index);
                $.ajax({
                    url: baseUrl + '/merchantOrderCancel/' + operationId,
                    data: {key: saa_key},
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
                        layer.msg(res.message);
                        getList();
                    },
                    error: function () {
                        layer.msg(errorMsg);
                        layer.close(loading);
                    }
                })
            })
        });

        //修改价格
        $(document).off('click', '.update_price').on('click', '.update_price', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号

            openIndex = layer.open({
                type: 1,
                title: '修改价格',
                content: $('#update_price_form'),
                shade: 0,
                offset: '20vw',
                area: ['25vw', 'auto'],
                cancel: function () {
                    $('#update_price_form').hide();
                }
            })
        });

        //退款详情
        $(document).off('click', '.show_after_sale').on('click', '.show_after_sale', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            //获取该订单发货状态
            var refund_status = 1;//是否显示发货按钮 1 显示 0 不显示
            $.ajax({
                url: baseUrl + '/merchantSuborder' + key + '&order_group_sn=' + order_sn,
                type: 'get',
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
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
                    if (res['data'].length <= 0) {
                        layer.msg('订单错误，子订单不存在');
                        return;
                    }
                    is_send_after_sale = 0;
                    if (res['data'][0]['express_id'] != null) {
                        is_send_after_sale = 1;
                    }
                    //判断商户是否已确认收货
                    if (res.refund != null && res.refund != '') {
                        refund_status = 0;
                    }
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            });
            //获取保存在js中的当前页面的所有数据，通过当前操作的订单id获取到该订单的其他信息
            var data = data_arr;
            for (var d = 0; d < data.length; d++) {
                if (data[d].id == operationId) {
                    data = data[d];
                }
            }

            after_sale_type = data['after_type'];
            var after_type_str = '';
            if (after_sale_type == '1') {
                after_type_str = '退款退货';
            } else if (after_sale_type == '2') {
                after_type_str = '仅退款';
            } else {
                after_type_str = '类型错误';
            }
            $('#after_type').text(after_type_str);
            $('#after_remark').text(data['after_remark']);
            //图片数组
            if (data['after_imgs']) {
                var after_imgs_arr = Trim(data['after_imgs']).split(',');
                //设置最终添加到图片标签中的标签
                var after_imgs = '';
                for (var i = 0; i < after_imgs_arr.length; i++) {
                    after_imgs += '<img style="width: 100px;height: 100px" src="' + after_imgs_arr[i] + '" />';
                }
                $('#after_imgs').empty();//清空图片，否则会重复添加
                $('#after_imgs').append(after_imgs);
            } else {
                $('#after_imgs').empty();
            }
            data['after_express_number'] ? $('#after_express_number').text(data['after_express_number']) : $('#after_express_number').text('无')

            //默认都隐藏
            $('.after_sale_agree_info').hide();
            $('.after_sale_operation').hide();
            $('.after_sale_refuse_info').hide();
            //判断，如果卖家已同意或者拒绝退款，则只显示，没有操作
            if (data['after_sale'] == 1) {//同意退款
                if (is_send_after_sale) {//已发货
                    if (refund_status) {//商家是否已确认收货
                        $('.after_sale_agree_info').show();
                    } else {
                        $('.after_sale_agree_info').hide();
                    }
                }
            } else if (data['after_sale'] == 2) {//拒绝退款，显示拒绝理由和图片
                $('#after_admin_remark').text(data['after_admin_remark']);
                //图片数组
                var after_admin_imgs_arr = Trim(data['after_admin_imgs']).split(',');
                //设置最终添加到图片标签中的标签
                var after_admin_imgs = '';
                for (var i = 0; i < after_admin_imgs_arr.length; i++) {
                    after_admin_imgs += '<img style="width: 100px;height: 100px" src="' + after_admin_imgs_arr[i] + '" />';
                }
                $('#after_admin_imgs').empty();//情况图片，否则会重复添加
                $('#after_admin_imgs').append(after_admin_imgs);
                $('.after_sale_refuse_info').show();
            } else {//其他情况显示操作按钮
                $('.after_sale_operation').show();
            }

            openIndex = layer.open({
                type: 1,
                title: '退款详情',
                content: $('#after_sale_form'),
                shade: 0,
                offset: '12vw',
                area: ['30vw', 'auto'],
                cancel: function () {
                    $('#after_sale_form').hide();
                }
            })
        });

        //一键退款
        $(document).off('click', '.refund_order').on('click', '.refund_order', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            layer.confirm('一键退款可能引起买家投诉，请协商后谨慎操作', function () {
                $.ajax({
                    url: baseUrl + '/merchantOrderRefund/' + operationId,
                    type: 'put',
                    data: {
                        key: saa_key
                    },
                    async: false,
                    headers: headers,
                    beforeSend: function () {
                        loading = layer.load(loadType, loadShade);//显示加载图标
                    },
                    success: function (res) {
                        //更新好物圈订单信息
                        getAjaxReturnKey({method: 'shopCircleOrder/' + order_sn, type: 'put'});

                        layer.close(loading);//关闭加载图标
                        if (res.status == timeOutCode) {
                            layer.msg(timeOutMsg);
                            admin.exit();
                            return false;
                        }
                        layer.msg(res.message, {icon: 1});
                        if (res.status != 200) {
                            return false;
                        }
                        getList();
                    },
                    error: function () {
                        layer.msg(errorMsg);
                        layer.close(loading);
                    }
                })
            })
        });

        //打印
        $(document).off('click', '.print').on('click', '.print', function () {
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            layer.confirm('是否确认打印', function () {
                arr = {
                    method: 'merchantPrint',
                    type: 'get',
                    data: {order_sn: order_sn}
                };
                res = getAjaxReturnKey(arr);
                if (res) {
                    layer.msg('打印成功', {icon: 1, time: 2000});
                }
            })
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
            var group_id = $('input[name=group_id]').val();
            if (Trim(group_id) !== '') {
                getData.leader_uid = group_id;
            }
            $.ajax({
                url: url + key,
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
                    uu_is_open = res.uu_is_open;
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

        /*动态添加单选框 应用分组*/
        function getGroups() {
            $.ajax({
                url: baseUrl + '/merchantElectronics' + key,
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
                        var name = res.data[a].express_name;
                        var id = res.data[a].express_id;
                        $('select[name=express_id]').append("<option value=" + id + ">" + name + "</option>");
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

        //执行填写快递操作
        form.on('submit(expressSub)', function () {
            var subData = {
                order_sn: order_sn,
                express_id: $('select[name=express_id]').val(),
                express_number: $('input[name=express_number]').val(),
                key: saa_key
            };
            $.ajax({
                url: baseUrl + '/merchantSend',
                data: subData,
                type: 'put',
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    //更新好物圈订单信息
                    getAjaxReturnKey({method: 'shopCircleOrder/' + order_sn, type: 'put'});

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
                    layer.msg('发货成功');
                    layer.close(openIndex);
                    $("#send_form")[0].reset();//表单重置
                    $('#send_form').hide();
                    getList();
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

        var map;
        var sender_lat;//发件人纬度
        var sender_lng;//发件人经度
        var receiver_lat;//收件人纬度
        var receiver_lng;//收件人经度
        //发件地址填写搜索地图列表事件
        $("input[name=sender_addr]").bind('input propertychange', function () {
            // 百度地图API功能
            map = new BMap.Map("map");// 创建Map实例
            map.centerAndZoom(new BMap.Point(119.228145, 34.602267), 11);//中心点，连云港市政府
            var options = {
                onSearchComplete: function (results) {
                    // 判断状态是否正确
                    if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                        var s = [];
                        for (var i = 0; i < results.getCurrentNumPois(); i++) {
                            s.push('<div class="map_search_list" data_type="sender" data_lng="' + results.getPoi(i).point.lng + '"  data_lat="' + results.getPoi(i).point.lat + '">' + results.getPoi(i).title + ", " + results.getPoi(i).address + '</div>');
                        }
                        document.getElementById("sender_result").innerHTML = s.join("");
                    }
                }
            };
            var local = new BMap.LocalSearch(map, options);
            local.search($("input[name=sender_addr]").val());
        });

        // //发件地址文本框失去焦点事件
        // $('input[name=sender_addr]').blur(function () {
        //     $('#sender_result').empty();
        // });

        //收件地址填写搜索地图列表事件
        $("input[name=receiver_addr]").bind('input propertychange', function () {
            // 百度地图API功能
            map = new BMap.Map("map");// 创建Map实例
            map.centerAndZoom(new BMap.Point(119.228145, 34.602267), 11);//中心点，连云港市政府
            var options = {
                onSearchComplete: function (results) {
                    // 判断状态是否正确
                    if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                        var s = [];
                        for (var i = 0; i < results.getCurrentNumPois(); i++) {
                            s.push('<div class="map_search_list" data_type="receiver" data_lng="' + results.getPoi(i).point.lng + '"  data_lat="' + results.getPoi(i).point.lat + '">' + results.getPoi(i).title + ", " + results.getPoi(i).address + '</div>');
                        }
                        document.getElementById("receiver_result").innerHTML = s.join("");
                    }
                }
            };
            var local = new BMap.LocalSearch(map, options);
            local.search($("input[name=receiver_addr]").val());
        });

        // //收件地址文本框失去焦点事件
        // $('input[name=receiver_addr]').blur(function () {
        //     $('#receiver_result').empty();
        // });

        //地图搜索列表点击事件
        $(document).off('click', '.map_search_list').on('click', '.map_search_list', function () {
            var data_type = $(this).attr('data_type');
            if (data_type === 'sender') {
                sender_lat = $(this).attr('data_lat');//发件人纬度
                sender_lng = $(this).attr('data_lng');//发件人经度
                $("input[name=sender_addr]").val($(this).html());
                $('#sender_result').empty();
            } else if (data_type === 'receiver') {
                receiver_lat = $(this).attr('data_lat');//收件人纬度
                receiver_lng = $(this).attr('data_lng');//收件人经度
                $("input[name=receiver_addr]").val($(this).html());
                $('#receiver_result').empty();
            } else {
                layer.msg('你干了什么。。', {icon: 1, time: 2000});
            }
        });

        //闪送重量加减以及重量input值变化事件
        var info_weight_init = 5;
        //闪送重量加事件
        $(document).off('click', '.weight-add').on('click', '.weight-add', function () {
            if (info_weight_init === 5) {
                $('.less_than_sign').hide();
            }
            info_weight_init++;
            $('input[name=info_weight]').val(info_weight_init);
        });

        //闪送重量减事件
        $(document).off('click', '.weight-subtraction').on('click', '.weight-subtraction', function () {
            if (info_weight_init > 5) {
                info_weight_init--;
                if (info_weight_init === 5) {
                    $('.less_than_sign').show();
                }
                $('input[name=info_weight]').val(info_weight_init);
            }
        });

        //闪送重量input值变化事件
        $("input[name=info_weight]").bind('input propertychange', function () {
            info_weight_init = parseInt($('input[name=info_weight]').val());
            if (info_weight_init > 5) {
                $('.less_than_sign').hide();
            } else {
                $('.less_than_sign').show();
                $('input[name=info_weight]').val(5);
                info_weight_init = 5;
            }
        });

        //寄件时间类型切换事件
        form.on('select(info_appointmentDate)', function () {
            var info_appointmentDate = $('#info_appointmentDate').val();
            if (info_appointmentDate === '1') {
                $('.info_appointment_date').show();
                //获取今天和明天的日期，存入 id 为 info_ymd 的下拉框中
                var today_date = new Date().format("yyyy-MM-dd");//今天日期
                var tomorrow_date = new Date(curDate.getTime() + 24 * 36E5).format("yyyy-MM-dd");//明天日期
                $('#info_ymd').empty().append('<option value="' + today_date + '">' + today_date + '</option><option value="' + tomorrow_date + '">' + tomorrow_date + '</option>');
                form.render();
            } else {
                $('.info_appointment_date').hide();
            }
        });

        //执行闪送发货查询费用操作
        form.on('submit(shansongSearchSub)', function () {
            var info_appointmentDate = $('#info_appointmentDate').val();
            var appointTime = '';//寄件时间
            if (info_appointmentDate === '1') {
                //获取用户选择的日期和时间
                appointTime = $('#info_ymd').val() + ' ' + $('#info_hi').val();
            }
            var subData = {
                order: {
                    orderNo: order_sn,
                    addition: $('#info_additionFee').val(),
                    // goods: 0,//商品名称（示例上是分组名称）
                    weight: $('input[name=info_weight]').val(),
                    appointTime: appointTime,
                    remark: $('textarea[name=info_remark]').val(),
                    receiverList: [{
                        addr: $('input[name=receiver_addr]').val(),
                        addrDetail: $('input[name=receiver_addrDetail]').val(),
                        city: '连云港市',
                        lat: receiver_lat,
                        lng: receiver_lng,
                        mobile: $('input[name=receiver_mobile]').val(),
                        // subNumber: 123,//收件人分机号（选填）
                        name: $('input[name=receiver_name]').val()
                    }],
                    sender: {
                        addr: $('input[name=sender_addr]').val(),
                        addrDetail: $('input[name=sender_addrDetail]').val(),
                        city: '连云港市',
                        lat: sender_lat,
                        lng: sender_lng,
                        mobile: $('input[name=sender_mobile]').val(),
                        name: $('input[name=sender_name]').val()
                    }
                }
            };
            arr = {
                method: 'merchantFlashCalc',
                type: 'post',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('本次订单费用为 ' + res.data.amount + '元', {icon: 1, time: 3000});
            }
        });

        //执行闪送发货操作
        form.on('submit(shansongSub)', function () {
            var info_appointmentDate = $('#info_appointmentDate').val();
            var appointTime = '';//寄件时间
            if (info_appointmentDate === '1') {
                //获取用户选择的日期和时间
                appointTime = $('#info_ymd').val() + ' ' + $('#info_hi').val();
            }
            var subData = {
                order: {
                    orderNo: order_sn,
                    addition: $('#info_additionFee').val(),
                    // goods: 0,//商品名称（示例上是分组名称）
                    weight: $('input[name=info_weight]').val(),
                    appointTime: appointTime,
                    remark: $('textarea[name=info_remark]').val(),
                    receiverList: [{
                        addr: $('input[name=receiver_addr]').val(),
                        addrDetail: $('input[name=receiver_addrDetail]').val(),
                        city: '连云港市',
                        lat: receiver_lat,
                        lng: receiver_lng,
                        mobile: $('input[name=receiver_mobile]').val(),
                        // subNumber: 123,//收件人分机号（选填）
                        name: $('input[name=receiver_name]').val()
                    }],
                    sender: {
                        addr: $('input[name=sender_addr]').val(),
                        addrDetail: $('input[name=sender_addrDetail]').val(),
                        city: '连云港市',
                        lat: sender_lat,
                        lng: sender_lng,
                        mobile: $('input[name=sender_mobile]').val(),
                        name: $('input[name=sender_name]').val()
                    }
                }
            };
            arr = {
                method: 'merchantFlashSave',
                type: 'post',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                //更新好物圈订单信息
                getAjaxReturnKey({method: 'shopCircleOrder/' + order_sn, type: 'put'});
                layer.msg('发货成功');
                layer.close(openIndex);
                $("#shansong_form")[0].reset();//表单重置
                $('#shansong_form').hide();
                getList();
            }
        });

        //执行UU跑腿发货操作
        form.on('submit(uuSendSub)', function () {
            uu_arr.special_type = $('input[name=special_type]:checked').val();
            uu_arr.callme_withtake = $('input[name=callme_withtake]:checked').val();
            arr = {
                method: 'merchantUuAddorder',
                type: 'post',
                data: uu_arr
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                //更新好物圈订单信息
                getAjaxReturnKey({method: 'shopCircleOrder/' + order_sn, type: 'put'});
                layer.msg('发货成功');
                layer.close(openIndex);
                $("#uuSend_form")[0].reset();//表单重置
                $('#uuSend_form').hide();
                getList();
            }
        });

        //执行修改价格操作
        form.on('submit(update_price)', function () {
            var subData = {
                order_sn: order_sn,
                payment_money: $('input[name=payment_money]').val(),
                key: saa_key
            };
            $.ajax({
                url: url + '/' + operationId,
                type: 'put',
                data: subData,
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
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg(sucMsg.put);
                    $("#update_price_form")[0].reset();//表单重置
                    $('#update_price_form').hide();
                    layer.close(openIndex);
                    element.render('tab', 'tab');
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);//关闭加载图标
                },
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                }
            })
        });

        //同意退款操作
        form.on('submit(agree_refund)', function () {
            //申请退款类型 after_sale_type 1 退款退货 2 只退款
            //只需要判断是否申请退货，其他情况同意则交易关闭
            layer.confirm('确定同意该订单的退款请求吗?', function (index) {
                layer.close(index);
                $('#after_sale_form').hide();
                //同意退款 修改状态 交易关闭
                if (after_sale_type == 1) {//退款退货
                    //获取该商户对应的收货地址和电话
                    $.ajax({
                        url: baseUrl + "/merchantAfterInfo" + key,
                        type: 'get',
                        async: false,
                        headers: headers,
                        beforeSend: function () {
                            loading = layer.load(loadType, loadShade);//显示加载图标
                        },
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
                            if (res['data'].length <= 0) {
                                layer.msg('未查询到收货信息，请新创建');
                                return;
                            }
                            var subData = {
                                status: 1,
                                after_phone: res['data'][0]['after_phone'],
                                after_addr: res['data'][0]['after_addr'],
                                key: saa_key
                            }
                            $.ajax({
                                url: baseUrl + '/merchantOrderAfter/' + operationId,
                                data: subData,
                                type: 'put',
                                async: false,
                                headers: headers,
                                beforeSend: function () {
                                    loading = layer.load(loadType, loadShade);//显示加载图标
                                },
                                success: function (res) {
                                    //更新好物圈订单信息
                                    getAjaxReturnKey({method: 'shopCircleOrder/' + order_sn, type: 'put'});

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
                                    layer.msg('同意退款成功');
                                    layer.close(openIndex);
                                    $("#after_sale_form")[0].reset();//表单重置
                                    $("#after_sale_form").hide();
                                },
                                error: function () {
                                    layer.msg(errorMsg);
                                    layer.close(loading);
                                }
                            })
                        },
                        error: function () {
                            layer.msg(errorMsg);
                            layer.close(loading);
                        }
                    })
                } else {//只退款
                    $.ajax({
                        url: baseUrl + '/merchantOrderAfter/' + operationId,
                        data: {
                            status: 1,//同意只退款
                            key: saa_key
                        },
                        type: 'put',
                        async: false,
                        headers: headers,
                        beforeSend: function () {
                            loading = layer.load(loadType, loadShade);//显示加载图标
                        },
                        success: function (res) {
                            //更新好物圈订单信息
                            getAjaxReturnKey({method: 'shopCircleOrder/' + order_sn, type: 'put'});

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
                            layer.msg('同意退款成功');
                            layer.close(openIndex);
                            $("#after_sale_form")[0].reset();//表单重置
                            $("#after_sale_form").hide();
                        },
                        error: function () {
                            layer.msg(errorMsg);
                            layer.close(loading);
                        }
                    })
                }
                getList();
            })
        });

        //拒绝退款
        form.on('submit(refuse_refund)', function () {
            layer.close(openIndex);
            $('#after_sale_form').hide();
            //判断该订单是否发货 is_send_after_sale
            if (is_send_after_sale) { //已发货
                refuseRefundOpenIndex = layer.open({
                    type: 1,
                    title: '退款详情',
                    content: $('#refuse_form'),
                    shade: 0,
                    offset: '12vw',
                    area: ['30vw', 'auto'],
                    cancel: function () {
                        $('#refuse_form').hide();
                    }
                })
            } else { //未发货
                //获取快递列表
                if (!groupData) {
                    getGroups();
                }
                openIndex = layer.open({
                    type: 1,
                    title: '填写物流',
                    content: $('#send_form'),
                    shade: 0,
                    offset: '20vw',
                    area: ['25vw', 'auto'],
                    cancel: function () {
                        $('#send_form').hide();
                    }
                })
            }
        });

        //确认拒绝执行操作
        form.on('submit(refuse)', function (data) {
            if (!uploadFlag) {
                layer.msg('请等待图片上传');
                return;
            }
            after_admin_imgs = '';
            for (var i in urlsArr) {
                after_admin_imgs += urlsArr[i] + ',';
            }
            var subData = {
                status: 2,
                after_admin_remark: data.field.after_admin_remark,
                after_admin_imgs: after_admin_imgs,
                key: saa_key
            };

            $.ajax({
                url: baseUrl + '/merchantOrderAfter/' + operationId,
                type: 'put',
                data: subData,
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
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg(sucMsg.put);
                    layer.close(refuseRefundOpenIndex);
                    $("#after_sale_form")[0].reset();//表单重置
                    $("#refuse_form")[0].reset();//表单重置
                    $('.layui-upload-list').empty();
                    $('#refuse_form').hide();
                    getList();
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);//关闭加载图标
                },
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                }
            })
        });

        //商户确认收货
        form.on('submit(confirmation_of_receipt)', function () {
            layer.confirm('该订单确认收货吗?', function (index) {
                $.ajax({
                    url: baseUrl + '/merchantOrderAfter/' + operationId + key,
                    type: 'put',
                    data: {
                        status: 1,
                        key: saa_key
                    },
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
                            if (res.status != 204) {
                                layer.msg(res.message);
                            }
                            return false;
                        }
                        layer.msg('确认收货成功');
                        layer.close(index);
                        layer.close(openIndex);
                        $("#after_sale_form")[0].reset();//表单重置
                        $("#after_sale_form").hide();
                        getList();
                    },
                    error: function () {
                        layer.msg(errorMsg);
                        layer.close(loading);//关闭加载图标
                    },
                    beforeSend: function () {
                        loading = layer.load(loadType, loadShade);//显示加载图标
                    }
                })
            })
        });

        //填写备注
        var remark_form = $('#remark_form');
        $(document).off('click', '.write_remark').on('click', '.write_remark', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            if (data_arr && data_arr.length > 0) {
                for (var i = 0; i < data_arr.length; i++) {
                    if (data_arr[i].id == operationId) {
                        $('textarea[name=order_remark]').val(data_arr[i].admin_remark)
                    }
                }
            }
            openIndex = layer.open({
                type: 1,
                title: '填写备注',
                content: remark_form,
                shade: 0,
                offset: '20vw',
                area: ['25vw', 'auto'],
                cancel: function () {
                    remark_form.hide();
                }
            })
        });

        //执行填写备注操作
        form.on('submit(remarkSub)', function () {
            arr = {
                method: 'merchantOrderRemark',
                type: 'put',
                data: {remark: $('textarea[name=order_remark]').val(), order_sn: order_sn}
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('备注保存成功', {icon: 1, time: 1000}, function () {
                    getList();
                    getPage();
                });
                layer.close(openIndex);
                remark_form[0].reset();//表单重置
                remark_form.hide();
            }
        });

        //执行选择自提点按钮操作
        var get_group_list_point = 'search';//设置打开自提点列表的位置 search 搜索栏 list 订单列表
        var group_render;
        var group_list_div = $('.group_list_div');
        var open_index;
        var cols = [//加载的表格
            {field: 'id', title: 'ID', width: '100%'},
            {field: 'realname', title: '姓名', width: '150%'},
            {field: 'addr', title: '自提点地址', width: '250%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '100%'}
        ];
        form.on('submit(choice)', function () {
            get_group_list_point = 'search';
            //默认加载列表
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTableGroup',//需要加载的 table 表格对应的 id
                method: 'merchantTuanUser?type=1&is_self=1&key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
                cols: [cols]//加载的表格字段
            };
            group_render = getTableRender(arr);//变量名对应 arr 中的 name
            open_index = layer.open({
                type: 1,
                title: '选择自提点',
                content: group_list_div,
                shade: 0,
                offset: '100px',
                area: ['600px', '600px'],
                cancel: function () {
                    group_list_div.hide();
                }
            })
        });

        //搜索
        form.on('submit(find_group)', function (data) {//查询
            group_render.reload({
                where: {searchName: data.field.searchNameGroup},
                page: {curr: 1}
            });
        });

        //表格操作点击事件
        table.on('tool(pageTableGroup)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            if (layEvent === 'choice') {//修改
                if (get_group_list_point === 'search') {
                    $('input[name=group_name]').val(data.realname);
                    $('input[name=group_id]').val(data.uid);
                } else if (get_group_list_point === 'list') {
                    //请求修改自提点接口
                    arr = {
                        method: 'merchantOrderLeader',
                        type: 'put',
                        data: {leader_uid: data.uid, order_sn: order_sn}
                    };
                    res = getAjaxReturnKey(arr);
                    if (res) {
                        layer.msg('修改成功', {icon: 1, time: 2000});
                        location.reload();
                    }
                }
                layer.close(open_index);
                group_list_div.hide();
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        //修改自提点按钮点击事件
        $(document).off('click', '.update_group').on('click', '.update_group', function () {
            operationId = $($(this).parent().parent().find('.order_id_hide')[0]).text();//获取操作的订单id
            order_sn = $($(this).parent().parent().find('.order_sn_hide')[0]).text();//获取操作的订单编号
            get_group_list_point = 'list';
            //默认加载列表
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTableGroup',//需要加载的 table 表格对应的 id
                method: 'merchantTuanUser?type=1&is_self=1&key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
                cols: [cols]//加载的表格字段
            };
            group_render = getTableRender(arr);//变量名对应 arr 中的 name
            open_index = layer.open({
                type: 1,
                title: '选择自提点',
                content: group_list_div,
                shade: 0,
                offset: '100px',
                area: ['600px', '600px'],
                cancel: function () {
                    group_list_div.hide();
                }
            });
        });

    });
    exports('order/list', {})
});

//获取总订单 div
function getOrderGroup(data) {
    var status;
    var back_color = '#f0ad4e';
    var operation;
    var shop_price = parseFloat(data.total_price - data.express_price).toFixed(2);
    var show_order = '<a class="layui-btn layui-btn-xs show_order" style="display: none;">查看</a>\n';//查看按钮，暂时先隐藏
    var order_status = '';
    if (data.order_status == '0') {
        status = '待付款';
        back_color = '#fd0b0b';
        operation = show_order +
            '    <a class="layui-btn layui-btn-xs layui-btn-danger cancel_order">取消订单</a>\n' +
            '    <a class="layui-btn layui-btn-xs layui-btn-danger update_price">修改价格</a>';
    } else if (data.order_status == '1') {
        status = '待发货';
        back_color = '#1588fe';
        var shansong_send = '';
        if (shansong === '1') {
            shansong_send = '<a class="layui-btn layui-btn-xs shansong">闪送</a>';
        }
        var uu_send = '';
        if (uu_is_open === '1' && data.express_type === '0') {
            uu_send = '<a class="layui-btn layui-btn-xs uuSend">UU跑腿</a>';
        }
        operation = show_order +
            '    <a class="layui-btn layui-btn-xs send_order" data="' + data.express_type + '">发货</a>\n' + shansong_send + uu_send +
            '    <a class="layui-btn layui-btn-xs refund_order">一键退款</a>';
    } else if (data.order_status == '2') {
        status = '已取消';
        back_color = '#b8b9b8';
        operation = show_order;
    } else if (data.order_status == '3') {
        status = '已发货';
        back_color = '#fbd039';
        operation = show_order +
            '    <a class="layui-btn layui-btn-xs refund_order">一键退款</a>';
    } else if (data.order_status == '4') {
        status = '已退款';
        operation = show_order;
    } else if (data.order_status == '5') {
        status = '退款中';
        back_color = '#fd0b0b';
        operation = show_order +
            '    <a class="layui-btn layui-btn-xs show_after_sale">退款详情</a>\n' +
            '    <a class="layui-btn layui-btn-xs refund_order">一键退款</a>';
    } else if (data.order_status == '6') {
        status = '待评价';
        operation = show_order;
    } else if (data.order_status == '7') {
        status = '已完成';
        back_color = '#02d20c';
        operation = show_order;
    } else if (data.order_status == '8') {
        status = '已删除';
        operation = show_order;
    } else if (data.order_status == '9') {
        status = '退款成功';
        operation = show_order;
    } else if (data.order_status == '11') {
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
    if (data.admin_remark && Trim(data.admin_remark) !== '') {
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
    if (data.realname && data.leader_phone && (data.order_status === '0' || data.order_status === '1')) {
        operation += '<a class="layui-btn layui-btn-xs update_group">修改自提点</a>';
    }
    operation += '<a class="layui-btn layui-btn-xs print">打印</a>';
    //送货方式
    var express_type = '类型错误';
    var self_mention_point_info = '';//自提点信息
    if (data.express_type === '0') {
        express_type = '快递' + '<br/>';
        express_type += '<div>' + (data.express_name ? data.express_name : "") + '</div>';
        express_type += '<div>' + (data.express_number ? data.express_number : "") + '</div>';
    } else if (data.express_type === '1') {
        express_type = '自提';
    } else if (data.express_type === '2') {
        express_type = '团长送货';
    }
    //显示自提点信息的送货方式：自提、团长送货
    if (data.express_type === '1' || data.express_type === '2') {
        self_mention_point_info = '  <span style="margin-left: 10px">自提点信息' +
            '                            姓名：<span style="font-weight:bolder">' + realname + '</span>' +
            '                            电话：<span style="font-weight:bolder">' + leader_phone + '</span>' +
            '                            小区：<span style="font-weight:bolder">' + area_name + '</span>' +
            '                            地址：<span style="font-weight:bolder">' + pcaa + '</span>' +
            '                        </span>\n';
    }
    //将子订单数据循环添加到页面
    var orders = '';
    if (!data.order) {
        layer.msg('没有订单', {icon: 1, time: 2000});
        orders = '暂无订单';
        return;
    }
    var order = data.order;
    var total_weight = 0;
    for (var j = 0; j < order.length; j++) {
        total_weight += parseFloat(order[j].weight);
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
        '                        创建时间: <span style="font-weight:bolder">' + data.format_create_time + '</span>\n' +
        self_mention_point_info +
        '                        <span style="float: right; padding-right: 80px;">总重量： ' + total_weight + ' kg</span>\n' +
        '                    </td>\n' +
        '                </tr>\n' +
        '                <tr>\n' +
        '                    <td class="td-choose" style="width:35px;">\n' +
        '                        <div style="text-align:center;" class="hiMallDatagrid-cell-check ">\n' +
        '                            <input type="checkbox">\n' +
        '                        </div>\n' +
        '                    </td>\n' +
        '                    <td style="width:40px;">\n' +
        '                        <div style="text-align:center;" class="hiMallDatagrid-cell">' + data.group_id + '</div>\n' +
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
        '                            <span class="order_id_hide" style="display: none;">' + data.group_id + '</span>\n' +
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
