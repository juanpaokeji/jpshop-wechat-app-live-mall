/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/9 10:10  一直在更新，时间随时修改
 * js model
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;

        form.render();

        /*diy设置开始*/
        //页面不同属性
        var filePut = '';//base64图片
        var groupData = 0;//是否已加载分组 是 1 否 0
        //判断是否为商城类应用，如果是，则需要显示商城分类
        var category_id = sessionStorage.getItem('category_id');
        if (category_id && category_id == '2') {
            //下拉请求接口必须，未请求过，则请求接口并保存，已请求过，获取保存的信息，减少加载时间
            if (!groupData) {
                getGroups(0);
            }
            $('.category').show();
        }

        $('.appName').text(sessionStorage.getItem('appName'));
        $('.comboName').text(sessionStorage.getItem('comboName'));

        $("#addImgPut").change(function () {//加载图片至img
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    filePut = e.target.result;
                    $("#image").attr("src", e.target.result);
                };
            }
            file = null;
        });
        /*diy设置结束*/

        //执行添加或编辑
        form.on('submit(sub)', function () {
            /*diy设置开始*/
            var subData = {
                name: $('input[name=name]').val(),
                pic_url: filePut,
                app_id: sessionStorage.getItem('appId'),
                combo_id: sessionStorage.getItem('comboId'),
                category_id: category_id
            };
            /*diy设置结束*/
            if (category_id && category_id == '2') {
                subData.shop_category_id = $('select[name=shop_category_id]').val();
            }

            arr = {
                method: 'merchantPay',
                type: 'post',
                data: subData
            };
            var res = getAjaxReturn(arr);
            if (res) {
                //跳转商户应用列表页面
                layer.msg(res.message, {icon: 1, time: 1000},
                    function () {
                        location.hash = '/app/list'
                    }
                );
            }
        });

        /*动态添加单选框 应用分组*/
        function getGroups() {
            arr = {
                method: 'merchantShopCategory',
                type: 'get'
            };
            var res = getAjaxReturn(arr);
            if (res && res.data) {
                for (var a = 0; a < res.data.length; a++) {
                    var name = res.data[a].name;
                    var id = res.data[a].id;
                    $('select[name=shop_category_id]').append("<option value=" + id + ">" + name + "</option>");
                }
                form.render();
                groupData = 1;
            }
        }
    });
    exports('app/add/info', {})
});
