/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/3/2
 * 团购
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laydate'], function () {
        var $ = layui.$;
        var form = layui.form;
        var layDate = layui.laydate;
        var arr = {};//全局ajax请求参数

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }
        var set_image_width = '140px';//设置添加的图片宽度
        var set_image_height = '140px';//设置添加的图片高度

        form.render();
        getGroupConfig();
        //选择开市时间 必须放这里，不然页面未加载完就执行是没有效果的
        layDate.render({
            elem: '#open_time',
            type: 'time'
        });
        //选择休市时间
        layDate.render({
            elem: '#close_time',
            type: 'time'
        });

        //获取团购基本配置
        function getGroupConfig() {
            $("#imageBan").empty().append('<img src="https://api2.juanpao.com/uploads/banner_pic_url.png" width="' + set_image_width + '" height="' + set_image_height + '">');
            $("#image").empty().append('<img src="https://api2.juanpao.com/uploads/close_pic_url.png" width="' + set_image_width + '" height="' + set_image_height + '">');
            $("#imageSupplier").empty().append('<img src="https://api2.juanpao.com/uploads/pic_url.png" width="' + set_image_width + '" height="' + set_image_height + '">');
            arr = {
                method: 'merchantTuanConfig',
                type: 'get',
            };
            var res = getAjaxReturnKey(arr);
            if (!res || !res.data) {
                return false;
            }
            if (res.data.is_open == 1) {
                $('#is_open').attr('class', 'checkbox on');
            } else {
                $('#is_open').attr('class', 'checkbox');
            }
            $('input[name=content]').val(res.data.content);
            $('input[name=open_time]').val(res.data.open_time);
            $('input[name=close_time]').val(res.data.close_time);
            var banner_pic_url = res.data.banner_pic_url;
            var close_pic_url = res.data.close_pic_url;
            var pic_url = res.data.pic_url;
            if (Trim(banner_pic_url) === '') {
                banner_pic_url = 'https://api2.juanpao.com/uploads/banner_pic_url.png';
            }
            if (Trim(close_pic_url) === '') {
                close_pic_url = 'https://api2.juanpao.com/uploads/close_pic_url.png';
            }
            if (Trim(pic_url) === '') {
                pic_url = 'https://api2.juanpao.com/uploads/pic_url.png';
            }
            $("#imageBan").empty().append('<img src="' + banner_pic_url + '" width="' + set_image_width + '" height="' + set_image_height + '">');
            $("#image").empty().append('<img src="' + close_pic_url + '" width="' + set_image_width + '" height="' + set_image_height + '">');
            $("#imageSupplier").empty().append('<img src="' +pic_url + '" width="' + set_image_width + '" height="' + set_image_height + '">');
            if (res.data.is_express == 1) {
                $('#is_express').attr('class', 'checkbox on');
            } else {
                $('#is_express').attr('class', 'checkbox');
            }
            if (res.data.is_site == 1) {
                $('#is_site').attr('class', 'checkbox on');
            } else {
                $('#is_site').attr('class', 'checkbox');
            }
            if (res.data.is_tuan_express == 1) {
                $('#is_tuan_express').attr('class', 'checkbox on');
            } else {
                $('#is_tuan_express').attr('class', 'checkbox');
            }
            $('input[name=min_withdraw_money]').val(parseFloat(res.data.min_withdraw_money));
            $('input[name=withdraw_fee_ratio]').val(parseFloat(res.data.withdraw_fee_ratio));
            $('input[name=commission_leader_ratio]').val(parseFloat(res.data.commission_leader_ratio));
            // $('input[name=commission_user_ratio]').val(parseInt(res.data.commission_user_ratio));
            $('input[name=commission_selfleader_ratio]').val(parseFloat(res.data.commission_selfleader_ratio));
            $('input[name=leader_name]').val(res.data.leader_name);
            $('input[name=leader_range]').val(res.data.leader_range);
        }

        //执行基本配置编辑
        form.on('submit(config_sub)', function () {
            //判断 是否数字
            var leader_range = $('input[name=leader_range]').val();
            if (leader_range === '' || isNaN(leader_range)) {
                layer.msg('团长覆盖范围请填写数字', {icon: 1, time: 2000});
                return;
            }
            var withdraw_fee_ratio = $('input[name=withdraw_fee_ratio]').val();
            if (withdraw_fee_ratio === '' || isNaN(withdraw_fee_ratio)) {
                layer.msg('提现手续费请填写数字', {icon: 1, time: 2000});
                return;
            }
            var min_withdraw_money = $('input[name=min_withdraw_money]').val();
            if (min_withdraw_money === '' || isNaN(min_withdraw_money)) {
                layer.msg('最低提现金额请填写数字', {icon: 1, time: 2000});
                return;
            }
            var commission_leader_ratio = $('input[name=commission_leader_ratio]').val();
            if (commission_leader_ratio === '' || isNaN(commission_leader_ratio)) {
                layer.msg('团长佣金请填写数字', {icon: 1, time: 2000});
                return;
            }
            // var commission_user_ratio = $('input[name=commission_user_ratio]').val();
            // if (commission_user_ratio === '' || isNaN(commission_user_ratio)) {
            //     layer.msg('推荐佣金请填写数字', {icon: 1, time: 2000});
            //     return;
            // }
            var commission_selfleader_ratio = $('input[name=commission_selfleader_ratio]').val();
            if (commission_selfleader_ratio === '' || isNaN(commission_selfleader_ratio)) {
                layer.msg('自提点佣金请填写数字', {icon: 1, time: 2000});
                return;
            }
            arr = {
                method: 'merchantTuanConfig',
                type: 'post',
                data: {
                    is_open: $('#is_open').attr('class') === 'checkbox on' ? 1 : 0,
                    content: $('input[name=content]').val(),
                    open_time: $('input[name=open_time]').val(),
                    close_time: $('input[name=close_time]').val(),
                    banner_pic_url: $('#imageBan img').attr('src'),
                    close_pic_url: $('#image img').attr('src'),
                    pic_url: $('#imageSupplier img').attr('src'),
                    is_express: $('#is_express').attr('class') === 'checkbox on' ? 1 : 0,
                    is_site: $('#is_site').attr('class') === 'checkbox on' ? 1 : 0,
                    is_tuan_express: $('#is_tuan_express').attr('class') === 'checkbox on' ? 1 : 0,
                    leader_name: $('input[name=leader_name]').val(),
                    leader_range: leader_range,
                    min_withdraw_money: min_withdraw_money,
                    withdraw_fee_ratio: withdraw_fee_ratio,
                    commission_leader_ratio: commission_leader_ratio,
                    // commission_user_ratio: commission_user_ratio,
                    commission_selfleader_ratio: commission_selfleader_ratio,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('保存成功', {icon: 1, time: 2000});
            }
        });

        //上传图片现方法
        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.addImgPutBan').on('click', '.addImgPutBan', function () {
            sessionStorage.setItem('images_common_div', '#imageBan');
            sessionStorage.setItem('images_common_div_info', '<img width="' + set_image_width + '" height="' + set_image_height + '">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.addImgPut').on('click', '.addImgPut', function () {
            sessionStorage.setItem('images_common_div', '#image');
            sessionStorage.setItem('images_common_div_info', '<img width="' + set_image_width + '" height="' + set_image_height + '">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.addImgPutSupplier').on('click', '.addImgPutSupplier', function () {
            sessionStorage.setItem('images_common_div', '#imageSupplier');
            sessionStorage.setItem('images_common_div_info', '<img width="' + set_image_width + '" height="' + set_image_height + '">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

    });
    exports('customers/groupConfig', {})
});
