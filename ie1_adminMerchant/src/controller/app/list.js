/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/7/3 9:50
 * 商户后台 应用管理
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var setter = layui.setter;//配置
        //获取网站域名和协议
        var protocol = document.location.protocol;
        var host = window.location.host;
        var arr, res;
        var ajax_method = 'merchantApp';

        //获取该商户是否绑定手机，如果未绑定则提示绑定
        var is_bind_phone = '0';
        arr = {
            method: 'checkPhone',
            type: 'get',
            data: {phone: localStorage.getItem('name')}
        };
        res = getAjaxReturn(arr);
        if (res && res.status === 200) {
            is_bind_phone = '1';
            sessionStorage.setItem('is_bind_phone', '1');
        } else {
            sessionStorage.setItem('is_bind_phone', '0');
        }
        if (is_bind_phone === '0') {
            layer.confirm('检测到您没有绑定手机，现在是否绑定?', function (index) {
                layer.close(index);
                location.hash = '/user/phone';
            })
        }

        var app_number = 0;
        //获取应用列表并追加到页面
        arr = {
            method: ajax_method,
            type: 'get'
        };
        res = getAjaxReturn(arr);
        if (res && res.data) {
            var data = res.data;
            var data_len = data.length;
            app_number = data_len;
            for (var i = 0; i < data_len; i++) {
                var content = '';
                if (data[i].category_id == '1') {
                    content = '<a href="' + protocol + '//' + host + '/adminApp/#/basicFacts/info" target="_blank">';
                } else if (data[i].category_id == '2') {
                    content = '<a href="' + protocol + '//' + host + '/ie1_adminShop/#/overview/index" target="_blank">';
                } else {

                }
                content += '<div class="layui-col-md3 detail" id="' + data[i].saa_key + ',' + data[i].saa_id + ',' + data[i].category_id + '">\n' +
                    '                                   <div class="layui-row" style="display: flex;flex-direction: row;justify-content: space-between;">\n' +
                    '<div class="layui-col-md12">\n' +
                    '                                               <img img" style="width:3.5vw; height:3.5vw;border-radius:50%" src="' + data[i].saa_pic_url + '"/>\n' +
                    '                                           </div>\n' +
                    '                                   <div style="text-align: left">\n' +
                    '                                       <p class="combo_name">' + data[i].saa_name + '</p>\n' +
                    '                                       <p class="app_name">' + data[i].app_name + '</p>\n' +
                    '                                   </div>\n' +
                    '                                   </div>\n' +
                    '</div>';
                content += '</a>';
                //循环添加数据
                $('.combo').append(content);
            }
        }

        //点击事件
        $(document).on('click', '.detail', function () {
            //点击后清除所有class为list的样式，将该点击加上边框样式
            var list = document.getElementsByClassName("detail");
            for (var y = 0, j = list.length; y < j; y++) {
                list[y].style.border = '';
            }
            this.style.border = "1px solid #FB6638";
            var str = this.id.split(",");
            sessionStorage.setItem("saa_key", str[0]);
            sessionStorage.setItem("saa_id", str[1]);
            sessionStorage.setItem("saa_category_id", str[2]);
        });

        //退出事件
        $(document).on('click', '.logout', function () {
            //在这写退出判断，是商户跳转商户登录，是应用管理员跳转应用登录
            localStorage.removeItem(setter.tableName);
            localStorage.removeItem('name');
            localStorage.removeItem('is_admin_login');
            location.href = protocol + '//' + host + '/adminMerchant/#/user/login';
        })

        //点击创建判断应用数量
        $(document).on('click', '.check_number', function () {
            //获取该商户对应的应用数量
            arr = {
                method: 'merchantAppOne',
                type: 'get'
            };
            res = getAjaxReturn(arr);
            if (res && res.data) {
                if (res.data.number >= app_number) {
                    location.href = protocol + '//' + host + '/adminMerchant/#/app/add/apps';
                } else {
                    layer.msg('应用数量已达上限，不可继续创建', {icon: 1, time: 2000});
                }
            }
        })

    });
    exports('app/list', {})
});
