/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/4/12
 * js 代理申请
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var openIndex;//定义弹出层，方便关闭
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var arr = {};//全局ajax请求参数
        var add_edit_form = $('#add_edit_form');//常用的表单
        var all_area = {};//获取到的所有省市区数组
        var all_area_len = 0;
        var group_data = 0;//是否已初始化级联 是 1 否 0
        var merchant_id = 0;//获取当前登陆的商户id

        /*diy设置开始*/
        //页面不同属性
        var ajax_method = 'merchantTuanVipUser';//新ajax需要的参数 method
        /*diy设置结束*/

        var is_area = 1;//是否有区的判断依据，点击市后如果没有区，则该值为 0，保存时候用来判断
        var first_province = 0;//获取的第一个省，用于获取市
        var first_city = 0;//获取的第一个市，用于获取区
        var vip_id = 0;//申请审核通过后，选择的购买的vip对应的id
        //获取该商户对应的vip信息
        arr = {
            method: ajax_method,
            type: 'get',
        };
        var res = getAjaxReturn(arr);
        if (res && res.data) {
            //有数据，需要判断状态 1审核通过待支付 0审核中  2=审核失败 3=支付成功并开通vip
            var status = res.data.status;
            if (status === '0') {
                //审核中，只需要显示审核中
                $('.in_audit').show();
            } else if (status === '1') {
                //审核通过，展示vip列表？下拉？，选择vip进行购买，微信，支付宝
                arr = {
                    method: 'merchantTuanVip?status=1',
                    type: 'get',
                };
                var vip_list = getAjaxReturn(arr);
                if (vip_list && vip_list.data) {
                    merchant_id = vip_list.merchant_id;
                    //循环添加vip等级到页面
                    var content = '';
                    var vip_data = vip_list.data;
                    var vip_data_len = vip_list.data.length;
                    for (var i = 0; i < vip_data_len; i++) {
                        content = '<div class="layui-col-md2 appList" id="' + vip_data[i].id + '">\n' +
                            '<div class="layui-row detail">\n' +
                            '<p class="name">' + vip_data[i].name + '</p>\n' +
                            '<img class="app_pic_url" src="' + vip_data[i].pic_url + '"/>\n' +
                            '<p class="money">' + parseInt(vip_data[i].money) + '</p>\n' +
                            '<p class="detail_info">' + vip_data[i].detail_info + '</p>\n' +
                            '</div>';
                        //循环添加数据
                        $('.vip').append(content);
                    }
                }
                $('.vip_list').show();
            } else if (status === '2') {
                var data = res.data;
                //展示之前填写的信息，需要重新提交审核
                //设置省市区级联 获取省级，开始做级联
                getGroups(1, 0, data.province_code);
                getGroups(2, data.province_code, data.city_code);
                getGroups(3, data.city_code, data.area_code);
                $('input[name=addr]').val(data.addr);
                $('input[name=company_name]').val(data.company_name);
                $('input[name=telephone]').val(data.telephone);
                $('input[name=qq]').val(data.qq);
                $('input[name=email]').val(data.email);
                $(".status").html('申请失败');
                $('.sub_form').show();
                $('.sub_btn').show();
            } else if (status === '3') {
                var vipInfo = res.vipinfo;
                //支付成功，已成为vip，是否可以升级？
                $(".vip_name").html(vipInfo.name);
                $(".vip_discount").html(parseFloat(vipInfo.discount));
                $(".vip_pic_url").attr('src', vipInfo.pic_url);
                $(".vip_detail_info").html(vipInfo.detail_info);
                form.render();
            }
        } else {
            //没有数据，显示添加页面
            $('.sub_form').show();
            $('.sub_btn').show();
            getGroups(1, 0, 0);//首先获取省列表
            getGroups(2, first_province, 0);//通过获取到的第一个省获取市列表
            getGroups(3, first_city, 0);//通过获取到的第一个市获取区列表
            form.render();
        }

        //省市切换事件
        form.on('select(region)', function (data) {
            var type = $(data.elem).attr('id');
            if (type === 'province') {
                //选择省事件，清空市区，循环获取市
                $('#city').empty();
                $('#area').empty();
                getGroups(2, data.value);
                getGroups(3, first_city);
            } else if (type === 'city') {
                $('#area').empty();
                //选择市事件，清空区，循环获取区
                getGroups(3, data.value);
            }
        });

        //执行添加或编辑
        form.on('submit(sub)', function () {
            arr = {
                method: ajax_method,
                type: 'post',
                data: {
                    province_code: $('#province').val(),
                    city_code: $('#city').val(),
                    area_code: $('#area').val(),
                    addr: $('input[name=addr]').val(),
                    company_name: $('input[name=company_name]').val(),
                    telephone: $('input[name=telephone]').val(),
                    qq: $('input[name=qq]').val(),
                    email: $('input[name=email]').val(),
                }
            };
            var res = getAjaxReturn(arr);
            if (res) {
                layer.msg('提交成功', {icon: 1, time: 2000});
                location.reload();
            }
        });

        //获取省市区级联 type 1 省 2 市 3 区，name option需要添加的class，group_id 需要默认选中的值
        function getGroups(type, parent_id, group_id) {
            var this_method = 'address';
            if (type !== 1) {
                this_method += '?keywords=' + parent_id;
            }
            var class_name = '';
            if (type === 1) {
                class_name = 'province';
            } else if (type === 2) {
                class_name = 'city';
            } else if (type === 3) {
                class_name = 'area';
            }
            arr = {
                method: this_method,
                type: 'get',
            };
            var res = getAjaxReturn(arr);
            is_area = 1;
            if (res && res.data && res.data.districts && res.data.districts[0].districts) {
                var districts = res.data.districts[0].districts;
                var len = districts.length;
                var name;
                var code;
                for (var a = 0; a < len; a++) {
                    if (districts[a].level !== 'street') {
                        name = districts[a].name;
                        code = districts[a].adcode;
                        if (a === 0) {
                            if (type === 1) {
                                first_province = code;
                            } else if (type === 2) {
                                first_city = code;
                            }
                        }
                        if (group_id) {
                            var selected = '';
                            if (group_id === code) {
                                selected = ' selected ';
                            }
                            $('select[name=' + class_name + ']').append("<option value=" + code + selected + ">" + name + "</option>");
                        } else {
                            $('select[name=' + class_name + ']').append("<option value=" + code + ">" + name + "</option>");
                        }
                    } else {
                        is_area = 0;
                        break;
                    }
                }
                form.render();
            } else {
                if (type === 3) {
                    is_area = 0;
                }
            }
        }

        //点击vip列表事件
        $(document).off('click', '.appList').on('click', '.appList', function () {
            vip_id = $(this).attr('id');
            //点击后清除所有class为list的样式，将该点击加上边框样式
            var list = document.getElementsByClassName("appList");
            $(".detail").css("border", "none");
            for (var y = 0, j = list.length; y < j; y++) {
                list[y].style.border = "none";
                list[y].style.color = "#66667a";
                list[y].style.background = "#fff";
            }
            // this.style.border = "2px solid dodgerblue";

            this.style.border = '1px solid #1E90FF';
            this.style.color = "#fff";
            this.style.background = "-webkit-gradient(linear, 0 0, 0 100%, from(#1ba2e8), to(#36eae8))";
        });

        //点击vip列表下一步 显示微信和支付宝购买
        $(document).off('click', '.next').on('click', '.next', function () {
            if (!vip_id) {
                layer.msg('请选择vip类型', {icon: 1, time: 2000});
                return;
            }

            $('.aliPay').attr('src', baseUrl + '/uploads/aliPay.png');
            $('.wxPay').attr('src', baseUrl + '/uploads/wxPay.png');
            $('.vip_list').hide();
            $('.pay_type').show();
        });

        //点击vip列表上一步 显示vip列表
        $(document).off('click', '.previous').on('click', '.previous', function () {
            $('.pay_type').hide();
            $('.vip_list').show();
        });

        //支付宝支付点击事件
        $(document).off('click', '.aliPay').on('click', '.aliPay', function () {
            if (!vip_id) {
                layer.msg('未获取到vip_id', {icon: 1, time: 2000});
                return;
            }
            window.open(baseUrl + '/merchantPayVip?type=ali&id=' + vip_id + '&merchant_id=' + merchant_id, '_blank', 'width=800,height=700,menubar=no,toolbar=no,status=no,scrollbars=yes');
        });

        //微信支付点击事件
        var save_res;
        $(document).off('click', '.wxPay').on('click', '.wxPay', function () {
            if (!vip_id) {
                layer.msg('未获取到vip_id', {icon: 1, time: 2000});
                return;
            }
            arr = {
                method: 'merchantPayVip?type=wechat&id=' + vip_id,
                type: 'get',
            };
            var res = getAjaxReturn(arr);
            if (res && res.data) {
                $('.wxCode').attr('src', res.data);
                save_res = res;

                openIndex = layer.open({
                    type: 1,
                    title: '微信二维码',
                    content: $('#wxCode'),
                    shade: 0.1,
                    shadeClose: false,
                    offset: '100px',
                    area: ['420px', 'auto'],
                });
            }
        });

        //点击完成支付执行事件
        form.on('submit(sub)', function () {
            arr = {
                method: 'wxQuery/' + save_res.out_trade_no,
                type: 'get',
            };
            var res = getAjaxReturn(arr);
            if (res) {
                layer.close(loading);
                if (res.status == 200 && res.data.trade_state == 'SUCCESS') {
                    layer.msg('付款成功', {icon: 1}, function () {
                        layer.close(openIndex);
                        location.hash = '/app/list';
                    })
                } else {
                    layer.confirm('未完成付款，确定取消该订单吗？', function (index1) {
                        layer.confirm('订单取消后无法恢复，是否确定取消该订单？', function (index2) {
                            layer.close(openIndex);
                            layer.close(index2);
                            layer.close(index1);
                            location.hash = '/user/proxy';
                        });
                    });
                }
            }
        });

    });
    exports('user/proxy', {})
});
