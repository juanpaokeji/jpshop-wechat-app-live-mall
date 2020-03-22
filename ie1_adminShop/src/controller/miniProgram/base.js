/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/11/16
 * 小程序 基本配置
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$
        var form = layui.form
        var setter = layui.setter//配置
        var sucMsg = setter.successMsg//成功提示 数组
        var openIndex;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var cert_path;
        var key_path;
        var arr;
        var res;
        var pay_switch = 1;
        form.render();

        //获取该应用对应的商户的支付开关，如果开，则不需要操作，如果关则需要将微信支付 radio 隐藏
        arr = {
            method: 'merchantInfo',
            type: 'get',
            async: false
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data) {
            if (res.data.pay_switch === '0') {
                pay_switch = 0;
            }
        }

        //获取小程序支付信息
        arr = {
            method: 'merchantCon',
            type: 'get',
            async: false
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data) {
            if (res.data.miniprogram.app_id == null) {
                $("#authorizeText").prepend('<i class="layui-icon layui-icon-close"></i>').css('color', 'red');
                $(".authorization").text('点击授权');
                $("#authorizeText span").text('未授权');
            } else {
                $("#authorizeText").prepend('<i class="layui-icon layui-icon-auz"></i>').css('color', 'limegreen');
                $(".authorization").text('重新授权');
                $("#authorizeText span").text('已授权');
                if (res.data.wx_pay_type === '1' && res.data.miniprogram_pay !== null) {
                    $("input[name=app_id]").val(res.data.miniprogram_pay.app_id ? res.data.miniprogram_pay.app_id : '');
                    $(".wechat input[name=mch_id]").val(res.data.miniprogram_pay.mch_id ? res.data.miniprogram_pay.mch_id : '');
                    $(".wechat input[name=pay_key]").val(res.data.miniprogram_pay.pay_key ? res.data.miniprogram_pay.pay_key : '');
                }
            }
            $("input[name='pay'][value='" + res.data.wx_pay_type + "']").prop("checked", true);
            if (res.data.wx_pay_type === '2' && res.data.saobei) {
                $('.sao').css('display', 'block');
                $('.wechat').css('display', 'none');
                $("input[name=app_id]").val(res.data.saobei.app_id);
                $(".sao input[name=merchant_no]").val(res.data.saobei.merchant_no);
                $(".sao input[name=terminal_id]").val(res.data.saobei.terminal_id);
                $(".sao input[name=saobei_access_token]").val(res.data.saobei.saobei_access_token);
            } else {
                if (res.data.miniprogram_pay) {
                    if (res.data.miniprogram_pay.cert_path && res.data.miniprogram_pay.key_path) {
                        if (Trim(res.data.miniprogram_pay.cert_path) != '') {
                            $('.cert_path').text('证书CERT已上传');
                        }
                        if (Trim(res.data.miniprogram_pay.key_path) != '') {
                            $('.key_path').text('证书KEY已上传');
                        }
                    }
                }
            }
            //当支付开关关闭的时候，只显示扫呗的配置和按钮
            if (pay_switch === 0) {
                $('.sao').css('display', 'block');
                $('.wechat').remove();
                $('.wechat_radio').next().remove();
                $('.wechat_radio').remove();
                //获取支付方式第一条并选中
                $("input[name='pay']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            }
            form.render();
        } else {
            $("#authorizeText").prepend('<i class="layui-icon layui-icon-close"></i>').css('color', 'red');
            $(".authorization").text('点击授权');
            $("#authorizeText span").text('未授权');
        }

        $(document).off('click', 'input[name=pay][value=1]+div').on('click', 'input[name=pay][value=1]+div', function () {
            $('.wechat').css('display', 'block')
            $('.sao').css('display', 'none')
        });

        $(document).off('click', 'input[name=pay][value=2]+div').on('click', 'input[name=pay][value=2]+div', function () {
            $('.sao').css('display', 'block')
            $('.wechat').css('display', 'none')
        });

        //获取扫码授权需要参数
        arr = {
            method: 'wechat/officialAccount/openplat',
            type: 'get',
            data: {type: 'adminShop'},
            async: false
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data) {
            $('.authorization').attr('href', res.data)
            form.render()
        }

        //监听提交
        form.on('submit(sub)', function () {
            var json;
            $("input[name=pay]:checked").val() === '1' ? json = {
                app_id: $("input[name=app_id]").val(),
                mch_id: $(".wechat input[name=mch_id]").val(),
                pay_key: $(".wechat input[name=pay_key]").val(),
                config_type: 'miniprogrampay',
                key: saa_key,
                wx_pay_type: $("input[name=pay]:checked").val()
            } : json = {
                app_id: $("input[name=app_id]").val(),
                config_type: 'miniprogrampay',
                key: saa_key,
                wx_pay_type: $("input[name=pay]:checked").val(),
                merchant_no: $(".sao input[name=merchant_no]").val(),
                saobei_access_token: $(".sao input[name=saobei_access_token]").val(),
                terminal_id: $(".sao input[name=terminal_id]").val()
            };
            json.cert_path = cert_path ? cert_path : '';
            json.key_path = key_path ? key_path : '';
            arr = {
                method: 'merchantConfig',
                type: 'put',
                data: json,
                async: false
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(sucMsg.put);
                layer.close(openIndex);
            }
        });

        //指定允许上传的文件类型
        $("#cert_path").change(function () {//加载图片至img
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    cert_path = e.target.result;
                };
            }
            file = null;
        });

        $("#key_path").change(function () {//加载图片至img
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    key_path = e.target.result;
                };
            }
            file = null;
        });

        //复制网址
        $(document).off('click', '.green').on('click', '.green', function () {
            getCopy($(this).prev().text())
            layer.msg('复制成功', {icon: 1, time: 2000})
        });

        //解绑操作
        $(document).off('click', '.untying').on('click', '.untying', function () {
            layer.confirm('确定要解绑吗?', function (index) {
                layer.close(index);
                arr = {
                    method: 'openPlatRemove',
                    type: 'delete',
                    data: {type: 'miniprogram'}
                };
                res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    layer.msg(res.message, {
                        'icon': 1,
                        'time': 2000
                    }, function () {
                        location.reload()
                    })
                }
            })
        })
    });
    exports('miniProgram/base', {})
});
