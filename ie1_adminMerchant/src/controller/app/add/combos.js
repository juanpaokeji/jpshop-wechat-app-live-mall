/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/7/3 9:50
 * 商户后台 应用管理
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;

        //首次进入或刷新该页面时，删除以保存的值
        var del_values = ['comboId', 'comboMoney', 'comboName'];
        for (var dv = 0; dv < del_values.length; dv++) {
            sessionStorage.removeItem(del_values[dv]);
        }

        //页面不同属性
        var appId = sessionStorage.getItem('appId');

        //获取套餐列表
        arr = {
            method: 'merchantCombos/' + appId,
            type: 'get'
        };
        var res = getAjaxReturn(arr);
        if (res && res.data) {
            var data = res.data;
            var content = '';
            for (var i = 0; i < data.length; i++) {
                content = '<div class="layui-col-md2 comboList" id="'+data[i].id+'">\n' +
                    '<div class="layui-row">\n' +
                    '<p class="name">'+data[i].name+'</p>\n' +
                    '<img class="app_pic_url" src="'+data[i].pic_url+'"/>\n' +
                    '<p class="detail_info">'+data[i].detail_info+'</p>\n' +
                    '<p class="price">\n' +
                    '</p>\n' +
                    '</div>\n' +
                    '</div>';
                //循环添加数据
                $('.combos').append(content);
                $('#btn').show();
                if (i === 0) {
                    //默认选中第一条
                    var that = $('.comboList')[0];
                    that.style.border = "1px solid #1E90FF";
                    sessionStorage.setItem("comboId", data[i].id);
                    sessionStorage.setItem("comboName", data[i].name);
                }
            }
        }

        //点击事件
        $(document).on('click', '.comboList', function () {
            //点击后清除所有class为list的样式，将该点击加上边框样式
            var list = document.getElementsByClassName("comboList");
            for (var y = 0, j = list.length; y < j; y++) {
                list[y].style.border = '';
            }
            this.style.border = "1px solid #1E90FF";

            sessionStorage.setItem("comboId", this.id);
            sessionStorage.setItem("comboName", $('#' + this.id).find('.name')[0].innerHTML);
        });

        //执行下一步
        form.on('submit(sub)', function () {
            var comboId = sessionStorage.getItem('comboId');
            if (!comboId) {
                layer.msg('请选择需要购买的套餐');
                return;
            }
            $('.combos').empty();
            window.location.href = '#/app/add/info';
        })

    })
    exports('app/add/combos', {})
});
