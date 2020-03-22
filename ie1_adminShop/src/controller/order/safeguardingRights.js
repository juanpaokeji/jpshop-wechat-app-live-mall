/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/9/19 10:00  一直在更新，时间随时修改
 * js 维权订单
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laydate', 'element', 'upload'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var layDate = layui.laydate;
        var element = layui.element;
        var upload = layui.upload;//上传图片
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
        var groupData = 0;//是否已加载分组 是 1 否 0
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
                    delete(urlsArr[ran]);//删除最终保存数组中对应的数据
                    parentNode.remove();//删除页面显示图片的元素
                    layer.close(index);//关闭弹出窗
                }
            });
        });

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantSale";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        /*diy设置结束*/

        //表格操作点击事件
        var is_send_after_sale;//是否已发货申请退款或退货 拒绝退款的时候用到
        var after_sale_type = 0;//退款类型 同意退款的时候用到
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operationId = data.id;
            order_sn = data.order_sn;
            if (layEvent === 'show') {//展示订单详情
                //设置页面值
                $('#su_nickname').text(data.su_nickname);
                $('#total_price').text(parseFloat(data.total_price));
                $('#sv_price').text(parseFloat(data.sv_price));
                $('#payment_money').text(parseFloat(data.payment_money));
                $('#create_time').text(data.create_time);
                //状态和售后需要单独处理
                var status = '';
                if (data.status == '0') {
                    status = '待付款';
                } else if (data.status == '1') {
                    status = '待发货';
                } else if (data.status == '2') {
                    status = '已取消';
                } else if (data.status == '3') {
                    status = '已发货';
                } else if (data.status == '4') {
                    status = '已退款';
                } else if (data.status == '5') {
                    status = '退款中';
                } else if (data.status == '6') {
                    status = '待评价';
                } else if (data.status == '7') {
                    status = '已完成';
                } else if (data.status == '8') {
                    status = '已删除';
                } else if (data.status == '9') {
                    status = '一键退款';
                } else {
                    status = '类型错误';
                }
                $('#order_status').text(status);
                var after_sale = '';
                if (data.after_sale == '-1') {
                    after_sale = '未退款';
                } else if (data.after_sale == '0') {
                    after_sale = '退款中';
                } else if (data.after_sale == '1') {
                    after_sale = '同意退款';
                } else if (data.after_sale == '2') {
                    after_sale = '拒绝退款';
                } else {
                    after_sale = '类型错误';
                }
                $('#order_after_sale').text(after_sale);
                $('#remark').text(data.remark);
                $('#admin_remark').text(data.admin_remark);
                openIndex = layer.open({
                    type: 1,
                    title: '子订单',
                    content: $('#suborder'),
                    shade: 0,
                    area: ['70vw', '60vh'],
                    cancel: function () {
                        $('#suborder').hide();
                    }
                })
                getSuborderRender(data.order_sn);
            } else if (layEvent === 'show_after_sale') {//退款详情
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
                })

                after_sale_type = data['after_type'];
                var after_type_str = 0;
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
            } else if (layEvent === 'refund') {//一键退款
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

        var cols = [//加载的表格
            {field: 'su_nickname', title: '购买用户', width: '6%'},
            {field: 'order_sn', title: '订单号', width: '14%'},
            {field: 'total_price', title: '订单总额', templet: '#totalPriceTpl', width: '6%'},
            {field: 'sv_price', title: '优惠金额', templet: '#svPriceTpl', width: '6%'},
            {field: 'payment_money', title: '付款总额', edit: 'text', templet: '#paymentMoneyTpl', width: '6%'},
            {field: 'after_sale', title: '售后', templet: '#afterSaleTpl', width: '6%'},
            {field: 'status', title: '订单状态', templet: '#statusTpl', width: '6%'},
            {field: 'remark', title: '备注', width: '12%'},
            {field: 'admin_remark', title: '管理员备注', edit: 'text', width: '12%'},
            {field: 'create_time', title: '创建时间', width: '11%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '15%'}
        ];

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

        //搜索
        form.on('submit(find)', function (data) {
            render.reload({
                where: {
                    searchNameType: data.field.searchNameType,
                    searchName: data.field.searchName,
                    start_time: data.field.start_time,
                    end_time: data.field.end_time,
                    goods_name: data.field.goods_name,
                    after_sale: data.field.after_sale,
                    status: data.field.status,
                    // logistics_type: data.field.logistics_type,//物流方式，只有快递发货
                    // pay_type: data.field.pay_type//支付方式，只有微信
                },
                page: {
                    curr: 1
                }
            })
        })

        var colsOrder = [//子订单加载的表格
            {field: 'pic_urls', title: '图片', templet: '#imgTpl', width: '25%'},
            {field: 'goods_name', title: '商品名称', width: '25%'},
            {field: 'number', title: '数量', width: '25%'},
            {field: 'total_price', title: '订单总额', templet: '#totalPriceTpl', width: '25%'},
            // {field: 'operations', title: '操作', toolbar: '#operationsSuborder' + tabId, width: '20%'}
        ];

        //子订单加载列表
        var renderOrder = '';

        function getSuborderRender(order_group_sn) {
            renderOrder = table.render({
                elem: '#suborderPageTable',
                url: baseUrl + '/merchantSuborder' + key + '&order_group_sn=' + order_group_sn,
                page: true, //开启分页
                limit: limit,
                limits: limits,
                loading: true,
                cols: [colsOrder],
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
        }

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
                        var name = res.data[a].name;
                        var id = res.data[a].id;
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
            }

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
                    render.reload();//表格局部刷新
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        })

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
                                    $("#send_form")[0].reset();//表单重置
                                    render.reload();//表格局部刷新
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
                            $("#send_form")[0].reset();//表单重置
                            render.reload();//表格局部刷新
                        },
                        error: function () {
                            layer.msg(errorMsg);
                            layer.close(loading);
                        }
                    })
                }
            })
        })

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
                    offset: '12vw',
                    area: ['50vw', 'auto'],
                    cancel: function () {
                        $('#send_form').hide();
                    }
                })
            }
        })

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
            }

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
                    render.reload();//表格局部刷新
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
                        render.reload();//表格局部刷新
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
        })

    })
    exports('order/safeguardingRights', {})
});
