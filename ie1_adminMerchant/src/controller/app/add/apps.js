/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/7/3 9:50
 * 商户后台 应用管理
 */

layui.define(function (exports) {
    layui.use(['jquery', 'admin', 'form', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;

        //首次进入或刷新该页面时，删除以保存的值
        var del_values = ['appId', 'appName', 'category_id', 'comboId', 'comboMoney', 'comboName'];
        for (var dv = 0; dv < del_values.length; dv++) {
            sessionStorage.removeItem(del_values[dv]);
        }

        //获取应用列表
        arr = {
            method: 'merchantApps',
            type: 'get'
        };
        var res = getAjaxReturn(arr);
        if (res && res.data) {
            var data = res.data;
            var content = '';
            for (var i = 0; i < data.length; i++) {
                content = '<div style="cursor: pointer" class="layui-col-md2 appList" id="' + data[i].id + '">\n' +
                    '<span class="category_id" style="display: none;">' + data[i].category_id + '</span>\n' +
                    '<div class="layui-row detail">\n' +
                    '<p class="name">' + data[i].name + '</p>\n' +
                    '<img class="app_pic_url" src="' + data[i].pic_url + '"/>\n' +
                    '<p class="detail_info">' + data[i].detail_info + '</p>\n' +
                    '</div>\n' +
                    '</input>';
                //循环添加数据
                $('.apps').append(content);
                $('#btn').show();
                if (i === 0) {
                    //默认选中第一条
                    var that = $('.appList')[0];
                    that.style.border = 'none';
                    that.style.color = "#fff";
                    that.style.background = "-webkit-gradient(linear, 0 0, 0 100%, from(#1ba2e8), to(#36eae8))"
                    sessionStorage.setItem("appId", data[i].id);
                    sessionStorage.setItem("appName", data[i].name);
                    sessionStorage.setItem("category_id", data[i].category_id);
                }
            }
        }

        //点击事件
        $(document).off('click', '.appList').on('click', '.appList', function () {
            //点击后清除所有class为list的样式，将该点击加上边框样式
            var list = document.getElementsByClassName("appList");
            $(".detail").css("border", "none");
            for (var y = 0, j = list.length; y < j; y++) {
                list[y].style.border = "none";
                list[y].style.color = "#66667a";
                list[y].style.background = "#fff";
            }
            // this.style.border = "2px solid dodgerblue";
            this.style.border = 'none';
            this.style.color = "#fff";
            this.style.background = "-webkit-gradient(linear, 0 0, 0 100%, from(#1ba2e8), to(#36eae8))"
            sessionStorage.setItem("appId", this.id);
            sessionStorage.setItem("appName", $('#' + this.id).find('.name')[0].innerHTML);
            sessionStorage.setItem("category_id", $($(this).find('.category_id')).text());
        });

        //执行下一步
        form.on('submit(sub)', function () {
            var appId = sessionStorage.getItem('appId');
            if (!appId) {
                layer.msg('请选择需要创建的应用');
                return;
            }
            $('.apps').empty();
            window.location.href = '#/app/add/combos';
        })

    })
    exports('app/add/apps', {})
});
