/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/12/22
 * 小程序 上传发布
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var saa_key = sessionStorage.getItem('saa_key');
        var key = '?key=' + saa_key;
        form.render();

        var open_index;//定义弹出层，方便关闭
        var add_edit_form = $('#add_edit_form');//常用的表单
        var adminVersionNumber;//我们的版本号
        var merchantVersionNumber;//商户的小程序版本号
        var merchantVersionStatus;//流程状态 数字
        //获取该小程序上传状态,用来判断到哪一步，可执行哪一步
        $.ajax({
            url: baseUrl + '/miniProgram' + key,
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
                if (!res.data) {
                    return false;
                }
                //返回示例
                // {"status":200,"message":"请求成功","data":{
                // "adminVersionNumber":"1.0.1",//我们的版本号
                // "merchantVersionNumber":"",//商户的小程序版本号
                // "merchantVersionStatus":""//流程状态
                // }}
                // -1未上传过小程序 1提交成功 0升级中 2提交失败 3=审核中 4=审核失败 5审核成功 6发布成功 7发布失败
                adminVersionNumber = res.data.adminVersionNumber;
                merchantVersionNumber = res.data.merchantVersionNumber;
                merchantVersionStatus = res.data.merchantVersionStatus;
                $('.serverVersion').text(adminVersionNumber);
                $('.localVersion').text(merchantVersionNumber == '' ? '无' : merchantVersionNumber);
                var status = '';//提示给用户的小程序状态，默认为空
                switch (parseInt(merchantVersionStatus)) {
                    case -1:
                        status = '未上传';
                        break;
                    case 1:
                        status = '上传成功';
                        break;
                    case 2:
                        status = '上传失败';
                        break;
                    case 3:
                        status = '审核中';
                        break;
                    case 4:
                        status = '审核失败';
                        break;
                    case 5:
                        status = '审核成功';
                        break;
                    case 6:
                        status = '发布成功';
                        break;
                    case 7:
                        status = '发布失败';
                        break;
                    default:
                        status = '状态异常';
                        break;
                }
                $('.status').text(status);
                if (status === '审核中') {
                    $('.withdraw').show();
                } else {
                    $('.withdraw').hide();
                }
            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);
            }
        })

        /*
            miniStatus 小程序可执行操作
            -1,2,6，只能上传 6需要判断
            1,4，只能提交审核
            3，不允许任何操作
            5，只能发布
            6，判断是否为最新版本，是，不允许任何操作，不是，只能上传
         */

        var newestMsg = '已经是最新版本啦';
        /*
         * 上传、审核、发布以及状态显示，需要通过获取小程序的状态来判断
         * 去除当前版本等于最新版本的判断 2019/11/7
         */
        //点击上传执行事件
        $(document).off('click', '.upload').on('click', '.upload', function () {
            // if (adminVersionNumber == merchantVersionNumber && merchantVersionStatus == '6') {
            //     layer.msg(newestMsg);
            //     return;
            // }
            if (merchantVersionStatus == '-1' || merchantVersionStatus == '1' || merchantVersionStatus == '2' || merchantVersionStatus == '4' || merchantVersionStatus == '5' || merchantVersionStatus == '6' || merchantVersionStatus == '7') {
                // var url = baseUrl + '/miniProgramCommit';
                // miniOperation(url, 'post');

                //该处需要弹窗，填写描述 describe ，并有提交按钮
                open_index = layer.open({
                    type: 1,
                    title: '新增',
                    content: add_edit_form,
                    shade: 0.1,
                    offset: '100px',
                    area: ['600px', 'auto'],
                    cancel: function () {
                        add_edit_form.hide();
                    }
                })
            } else {
                layer.msg('当前状态不能上传');
            }
        });


        //执行添加或编辑
        form.on('submit(sub)', function () {
            // arr = {
            //     method: 'miniProgramCommit',
            //     type: 'post',
            //     data: {
            //         describe: $('textarea[name=describe]').val(),
            //     }
            // };
            // var res = getAjaxReturnKey(arr);
            // if (res) {
            //     layer.msg('上传成功', {icon: 1, time: 2000});
            //     layer.close(open_index);
            //     add_edit_form.hide();
            // }

            $.ajax({
                url: baseUrl + '/miniProgramCommit' + key,
                type: 'post',
                data: {
                    describe: $('textarea[name=describe]').val()
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
                    if (res.status != 200) {
                        layer.close(open_index);
                        if (res.message && res.message.errcode == '61007') {
                            layer.msg('您已绑定其他平台，请解绑后重新授权', {icon: 1, time: 2000});
                        } else {
                            layer.msg(res.message, {icon: 1, time: 2000});
                        }
                        return false;
                    }
                    layer.msg('上传成功', {icon: 1, time: 1000}, function () {
                        location.reload();
                    });
                    layer.close(open_index);
                    add_edit_form.hide();
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

        //点击提交审核执行事件
        $(document).off('click', '.examine').on('click', '.examine', function () {
            // if (adminVersionNumber == merchantVersionNumber && merchantVersionStatus == '6') {
            //     layer.msg(newestMsg);
            //     return;
            // }
            if (merchantVersionStatus == '1' || merchantVersionStatus == '4') {
                var url = baseUrl + '/miniProgramAudit';
                miniOperation(url, 'post');
            } else {
                layer.msg('当前状态不能提交审核');
            }
        });

        //点击提交审核撤回执行事件
        $(document).off('click', '.withdraw').on('click', '.withdraw', function () {
            $.ajax({
                url: baseUrl + '/miniProgramUndocodeaudit' + key,
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
                        layer.close(open_index);
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg('撤回成功', {icon: 1, time: 2000}, function () {
                        location.reload();
                    });
                    layer.close(open_index);
                    add_edit_form.hide();
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

        //点击发布执行事件
        $(document).off('click', '.release').on('click', '.release', function () {
            // if (adminVersionNumber == merchantVersionNumber && merchantVersionStatus == '6') {
            //     layer.msg(newestMsg);
            //     return;
            // }
            if (merchantVersionStatus == '5') {
                var url = baseUrl + '/miniProgramrelease';
                miniOperation(url, 'post');
            } else {
                layer.msg('当前状态不能发布');
            }
        })

        //获取体验二维码事件
        $(document).off('click', '.qrCode').on('click', '.qrCode', function () {
            $.ajax({
                url: baseUrl + '/miniProgramQrcode' + key,
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
                    if (!res.data) {
                        return false;
                    }
                    $('.qrCodeImg').attr('src', res.data);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        })

        //小程序执行操作，通过不同的 url 区分上传、提交审核、发布
        function miniOperation(url, type) {
            $.ajax({
                url: url + key,
                type: type,
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
                    $('.status').text(res.message);
                    if (!res.data) {
                        return false;
                    }
                    var msg = '';
                    if (url === baseUrl + '/miniProgramCommit') {
                        msg = '上传成功';
                        add_edit_form[0].reset();//表单重置
                        add_edit_form.hide();
                    } else if (url === baseUrl + '/miniProgramAudit') {
                        msg = '提交审核成功，请等待审核结果';
                    } else if (url === baseUrl + '/miniProgramrelease') {
                        msg = '发布成功';
                    }
                    layer.msg(msg, {
                        icon: 1,
                        time: 1000 //1秒关闭（如果不配置，默认是3秒）
                    }, function () {
                        location.reload();
                    });
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }


    });

    exports('miniProgram/formal', {})
});