/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/7/3 9:50
 * 商户后台 应用管理
 */

layui.define(function (exports) {
    layui.use(['jquery', 'admin', 'setter', 'form'], function () {
        var $ = layui.$;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var form = layui.form;
        var baseUrl = setter.baseUrl;
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性

        //页面不同属性
        $('.aliPay').attr('src', baseUrl + '/uploads/aliPay.png');
        $('.wxPay').attr('src', baseUrl + '/uploads/wxPay.png');
        var payId = sessionStorage.getItem("payId");

        //支付宝支付点击事件
        $(document).off('click', '.aliPay').on('click', '.aliPay', function () {
            window.open(baseUrl + '/merchantPayCombo/' + payId+'?type=ali&merchant_id=' + sessionStorage.getItem('merchant_id'), '_blank', 'width=800,height=700,menubar=no,toolbar=no,status=no,scrollbars=yes');
            // window.showModalDialog(baseUrl + '/alipay/' + payId, "", "dialogWidth=800px;dialogHeight=700px;status=no;help=no;scrollbars=yes");
        });

        //微信支付点击事件
        var save_res;
        $(document).off('click', '.wxPay').on('click', '.wxPay', function () {
            $.ajax({
                url: baseUrl + '/merchantPayCombo/' + payId +'?type=wechat&merchant_id=' + sessionStorage.getItem('merchant_id'),
                type: "get",
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    if (typeof res == 'string') {
                        res = eval('(' + res + ')');
                    }
                    layer.close(loading);
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    if (res.status !== 200) {
                        layer.msg(res.message);
                        return false;
                    }

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
						cancel: function () {
						    $('#wxCode').hide()
						},
                        success: function (i, j) {
                            if (i.length === 0) {
                                $("#layui-layer-shade" + j).remove();
                            }
                        }
                    })
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

        //点击完成支付执行事件
        form.on('submit(sub)', function () {
            $.ajax({
                url: baseUrl + '/merchantPayComboWxQuery/' + save_res.out_trade_no,
                type: 'get',
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    layer.close(loading);
                    if (res.status == 200 && res.data.trade_state == 'SUCCESS') {
                        layer.msg('付款成功', {icon: 1}, function () {
                            layer.close(openIndex);
                            location.hash = '/user/package';
                        })
                    } else {
                        layer.confirm('未完成付款，确定取消该订单吗？', function (index1) {
                            layer.confirm('订单取消后无法恢复，是否确定取消该订单？', function (index2) {
                                layer.close(openIndex);
                                layer.close(index2);
                                layer.close(index1);
                                location.hash = '/user/package';
                            });
                        });
                    }
                },
                error: function () {
                    layer.msg(errorMsg);
                }
            })
        })

    })
    exports('app/add/packagePay', {})
});
