/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/12/5
 * 商城后台修改创建应用时的信息
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter', 'laydate'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        var openIndex;//定义弹出层，方便关闭
        var layDate = layui.laydate;
        var saa_id = sessionStorage.getItem('saa_id');
        var saa_key = sessionStorage.getItem('saa_key');
        var saa_category_id = sessionStorage.getItem('saa_category_id');//该应用的类型 圈子、商城
        var groupData = 0;//是否已加载分组 是 1 否 0
        var estimated_num = 0;
        var estimated_type = 1;
        var arr, res;

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }
        var set_image_width = '120px';//设置添加的图片宽度
        var set_image_height = '120px';//设置添加的图片高度

        if (saa_category_id == '1') {
            $('.shop_category_id').hide();
        } else {
            getGroups();
            $('.shop_category_id').show();
        }
        form.render();

        //获取应用信息
        arr = {
            method: 'merchantAppInfo/' + saa_id,
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res) {
            $("select[name=shop_category_id]").val(res.data.shop_category_id);
            $("input[name=name]").val(res.data.name);
            //3个天数 团长确认收货天数 团长确认发货天数 用户确认收货天数
            $("input[name=leader_confirm]").val(res.data.leader_confirm);
            $("input[name=leader_send]").val(res.data.leader_send);
            $("input[name=user_confirm]").val(res.data.user_confirm);
            $("input[name=coordinate]").val(res.data.coordinate);
            $("input[name=starting_price]").val(parseFloat(res.data.starting_price));
            $("#image").empty().append('<img src="' + res.data.pic_url + '" width="' + set_image_width + '" height="' + set_image_height + '">');
            $("input[name=phone]").val(res.data.phone);
            $("input[name=supplier_phone]").val(res.data.supplier_phone);
            $("input[name=leader_phone]").val(res.data.leader_phone);
            $("#imageLogin").empty().append('<img src="' + res.data.pic_url_login + '" width="' + set_image_width + '" height="' + set_image_height + '">');
            var default_pic_url = res.data.default_pic_url;
            if (Trim(default_pic_url) === '') {
                $("#imageDefault").empty().append('<img src="https://api2.juanpao.com/uploads/default_pic_url.png" width="' + set_image_width + '" height="' + set_image_height + '">');
            } else {
                $("#imageDefault").empty().append('<img src="' + default_pic_url + '" width="' + set_image_width + '" height="' + set_image_height + '">');
            }
            $("textarea[name=detail_info]").val(res.data.detail_info);
            $("input[name='user_vip'][value='" + res.data.user_vip + "']").prop("checked", true);
            if (res.data.pay_info == 1) {
                $("input[name=pay_info]").prop('checked', true);
            } else {
                $("input[name=pay_info]").removeAttr('checked');
            }
            if (res.data.score_shop == 1) {
                $("input[name=score_shop]").prop('checked', true);
            } else {
                $("input[name=score_shop]").removeAttr('checked');
            }
            if (res.data.leader_level == 1) {
                $("input[name=leader_level]").prop('checked', true);
            } else {
                $("input[name=leader_level]").removeAttr('checked');
            }
            if (res.data.my_mini_info == 1) {
                $("input[name=my_mini_info]").prop('checked', true);
            } else {
                $("input[name=my_mini_info]").removeAttr('checked');
            }
            if (res.data.good_phenosphere == 1) {
                $("input[name=good_phenosphere]").prop('checked', true);
            } else {
                $("input[name=good_phenosphere]").removeAttr('checked');
            }
            if (res.data.balance_pay == 1) {
                $("input[name=balance_pay]").prop('checked', true);
            } else {
                $("input[name=balance_pay]").removeAttr('checked');
            }
            if (res.data.shansong == 1) {
                $("input[name=shansong]").prop('checked', true);
            } else {
                $("input[name=shansong]").removeAttr('checked');
            }
            if (res.data.uu_is_open == 1) {
                $("input[name=uu_is_open]").prop('checked', true);
            } else {
                $("input[name=uu_is_open]").removeAttr('checked');
            }
            if (res.data.store_is_open == 1) {
                $("input[name=store_is_open]").prop('checked', true);
            } else {
                $("input[name=store_is_open]").removeAttr('checked');
            }
            if (res.data.yly_print == 1) {
                $("input[name=yly_print]").prop('checked', true);
            } else {
                $("input[name=yly_print]").removeAttr('checked');
            }
            if (res.data.is_stock == 1) {
                $("input[name=is_stock]").prop('checked', true);
            } else {
                $("input[name=is_stock]").removeAttr('checked');
            }
            if (res.data.is_merchant_info == 1) {
                $("input[name=is_merchant_info]").prop('checked', true);
            } else {
                $("input[name=is_merchant_info]").removeAttr('checked');
            }
            if (res.data.is_info_header == 1) {
                $("input[name=is_info_header]").prop('checked', true);
            } else {
                $("input[name=is_info_header]").removeAttr('checked');
            }
            if (res.data.is_info_bottom == 1) {
                $("input[name=is_info_bottom]").prop('checked', true);
            } else {
                $("input[name=is_info_bottom]").removeAttr('checked');
            }
            if (res.data.estimated_service_time_info && res.data.estimated_service_time_info.is_estimated == 1) {
                $("input[name=is_estimated_service_time]").prop('checked', true);
                estimated_type = res.data.estimated_service_time_info.estimated_type
                var selected1 = '';
                var selected2 = '';
                var selected3 = '';
                if (estimated_type == 1){
                    selected1 = 'selected';
                } else if (estimated_type == 2){
                    selected2 = 'selected';
                } else if (estimated_type == 3){
                    selected3 = 'selected';
                }
                $('.estimated_service_time_info').append(
                    '<div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">模式</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                    <select name="estimated_type" lay-verify="" lay-filter="estimated_type">\n' +
                    '                       <option value="1" ' + selected1 + '>每日</option>\n' +
                    '                       <option value="2" ' + selected2 + '>每周</option>\n' +
                    '                       <option value="3" ' + selected3 + '>按固定时间段</option>\n' +
                    '                    </select> ' +
                    '                    </div>\n' +
                    '</div>\n'
                );
                var estimated_str = '';
                var estimated_data_len = 0;
                var estimated_time_len = 0;
                var estimated_time_info = [];
                var estimated_data_info = [];
                estimated_time_len = res.data.estimated_service_time_info.estimated_time.length
                estimated_time_info = res.data.estimated_service_time_info.estimated_time
                estimated_num = estimated_time_len - 1
                if (estimated_type == 1){
                    estimated_str += '<div class="time_list">\n';
                    estimated_str += '    <div class="layui-form-item">\n';
                    estimated_str += '                    <label class="layui-form-label">活动时间</label>\n';
                    estimated_str += '                    <div class="layui-input-inline">\n';
                    estimated_str += '                        <input type="text" value="' + estimated_time_info[0] + '" class="layui-input" lay-verify="required" name="estimated_time" id="estimated_time_0">\n';
                    estimated_str += '                    </div>\n';
                    estimated_str += '                    <a href="javascript: void(0)" class="estimated_add" style="color: limegreen;">添加</a>\n';
                    estimated_str += '    </div>\n';
                    if (estimated_time_len > 1){
                        for (var i = 1; i < estimated_time_len; i++){
                            estimated_str += '    <div class="layui-form-item">\n';
                            estimated_str += '                    <label class="layui-form-label">活动时间</label>\n';
                            estimated_str += '                    <div class="layui-input-inline">\n';
                            estimated_str += '                        <input type="text" value="' + estimated_time_info[i] + '" class="layui-input" lay-verify="required" name="estimated_time" id="estimated_time_' + i +'">\n';
                            estimated_str += '                    </div>\n';
                            estimated_str += '                    <a href="javascript: void(0)" class="estimated_del" style="color: red;">删除</a>\n';
                            estimated_str += '    </div>\n';
                        }
                    }
                    estimated_str += '    <div class="estimated_service_time_list"></div>';
                    estimated_str += '</div>\n';
                    $('.estimated_service_time_info').append(estimated_str);
                    layDate.render({
                        elem: '#estimated_time_0',
                        type: 'time',
                        trigger: 'click',
                        range: true
                    });
                    if (estimated_time_len > 1) {
                        for (var i = 1; i < estimated_time_len; i++) {
                            layDate.render({
                                elem: '#estimated_time_' + i,
                                type: 'time',
                                trigger: 'click',
                                range: true
                            });
                        }
                    }
                } else if (estimated_type == 2){
                    estimated_data_len = res.data.estimated_service_time_info.estimated_data.length
                    estimated_data_info = res.data.estimated_service_time_info.estimated_data
                    var checked1 = '';
                    var checked2 = '';
                    var checked3 = '';
                    var checked4 = '';
                    var checked5 = '';
                    var checked6 = '';
                    var checked7 = '';
                    for (var i = 0; i < estimated_data_len; i++){
                        switch(estimated_data_info[i])
                        {
                            case "周一":
                                checked1 = 'checked';
                                break;
                            case "周二":
                                checked2 = 'checked';
                                break;
                            case "周三":
                                checked3 = 'checked';
                                break;
                            case "周四":
                                checked4 = 'checked';
                                break;
                            case "周五":
                                checked5 = 'checked';
                                break;
                            case "周六":
                                checked6 = 'checked';
                                break;
                            case "周日":
                                checked7 = 'checked';
                                break;
                            default:
                                break;
                        }
                    }

                    estimated_str += '<div class="time_list">\n';
                    estimated_str += '    <div class="layui-form-item">\n'
                    estimated_str += '                    <label class="layui-form-label">活动时间</label>\n'
                    estimated_str += '                    <div class="layui-input-inline">\n'
                    estimated_str += '                       <input type="checkbox" ' + checked1 + ' name="week" title="周一" value="周一" lay-skin="primary">\n'
                    estimated_str += '                       <input type="checkbox" ' + checked2 + ' name="week" title="周二" value="周二" lay-skin="primary">\n'
                    estimated_str += '                       <input type="checkbox" ' + checked3 + ' name="week" title="周三" value="周三" lay-skin="primary">\n'
                    estimated_str += '                       <input type="checkbox" ' + checked4 + ' name="week" title="周四" value="周四" lay-skin="primary">\n'
                    estimated_str += '                       <input type="checkbox" ' + checked5 + ' name="week" title="周五" value="周五" lay-skin="primary">\n'
                    estimated_str += '                       <input type="checkbox" ' + checked6 + ' name="week" title="周六" value="周六" lay-skin="primary">\n'
                    estimated_str += '                       <input type="checkbox" ' + checked7 + ' name="week" title="周日" value="周日" lay-skin="primary">\n'
                    estimated_str += '                    </div>\n'
                    estimated_str += '    </div>\n'
                    estimated_str += '    <div class="layui-form-item">\n';
                    estimated_str += '                    <label class="layui-form-label"></label>\n';
                    estimated_str += '                    <div class="layui-input-inline">\n';
                    estimated_str += '                        <input type="text" value="' + estimated_time_info[0] + '" class="layui-input" lay-verify="required" name="estimated_time" id="estimated_time_0">\n';
                    estimated_str += '                    </div>\n';
                    estimated_str += '                    <a href="javascript: void(0)" class="estimated_add" style="color: limegreen;">添加</a>\n';
                    estimated_str += '    </div>\n';
                    if (estimated_time_len > 1){
                        for (var i = 1; i < estimated_time_len; i++){
                            estimated_str += '    <div class="layui-form-item">\n';
                            estimated_str += '                    <label class="layui-form-label"></label>\n';
                            estimated_str += '                    <div class="layui-input-inline">\n';
                            estimated_str += '                        <input type="text" value="' + estimated_time_info[i] + '" class="layui-input" lay-verify="required" name="estimated_time" id="estimated_time_' + i +'">\n';
                            estimated_str += '                    </div>\n';
                            estimated_str += '                    <a href="javascript: void(0)" class="estimated_del" style="color: red;">删除</a>\n';
                            estimated_str += '    </div>\n';
                        }
                    }
                    estimated_str += '    <div class="estimated_service_time_list"></div>';
                    estimated_str += '</div>\n';
                    $('.estimated_service_time_info').append(estimated_str);
                    layDate.render({
                        elem: '#estimated_time_0',
                        type: 'time',
                        trigger: 'click',
                        range: true
                    });
                    if (estimated_time_len > 1) {
                        for (var i = 1; i < estimated_time_len; i++) {
                            layDate.render({
                                elem: '#estimated_time_' + i,
                                type: 'time',
                                trigger: 'click',
                                range: true
                            });
                        }
                    }
                } else if (estimated_type == 3){
                    estimated_data_info = res.data.estimated_service_time_info.estimated_data

                    estimated_str += '<div class="time_list">\n';
                    estimated_str += '    <div class="layui-form-item">\n';
                    estimated_str += '                    <label class="layui-form-label">活动时间</label>\n';
                    estimated_str += '                    <div class="layui-input-inline">\n';
                    estimated_str += '                        <input type="text" value="' + estimated_data_info[0] + '" class="layui-input" lay-verify="required" name="estimated_data" id="estimated_data_0">\n';
                    estimated_str += '                    </div>\n';
                    estimated_str += '                    <div class="layui-input-inline">\n';
                    estimated_str += '                        <input type="text" value="' + estimated_time_info[0] + '" class="layui-input" lay-verify="required" name="estimated_time" id="estimated_time_0">\n';
                    estimated_str += '                    </div>\n';
                    estimated_str += '                    <a href="javascript: void(0)" class="estimated_add" style="color: limegreen;">添加</a>\n';
                    estimated_str += '    </div>\n';
                    if (estimated_time_len > 1){
                        for (var i = 1; i < estimated_time_len; i++){
                            estimated_str += '    <div class="layui-form-item">\n';
                            estimated_str += '                    <label class="layui-form-label">活动时间</label>\n';
                            estimated_str += '                    <div class="layui-input-inline">\n';
                            estimated_str += '                        <input type="text" value="' + estimated_data_info[i] + '" class="layui-input" lay-verify="required" name="estimated_data" id="estimated_data_' + i +'">\n';
                            estimated_str += '                    </div>\n';
                            estimated_str += '                    <div class="layui-input-inline">\n';
                            estimated_str += '                        <input type="text" value="' + estimated_time_info[i] + '" class="layui-input" lay-verify="required" name="estimated_time" id="estimated_time_' + i +'">\n';
                            estimated_str += '                    </div>\n';
                            estimated_str += '                    <a href="javascript: void(0)" class="estimated_del" style="color: red;">删除</a>\n';
                            estimated_str += '    </div>\n';
                        }
                    }
                    estimated_str += '    <div class="estimated_service_time_list"></div>';
                    estimated_str += '</div>\n';
                    $('.estimated_service_time_info').append(estimated_str);
                    if (estimated_time_len > 1) {
                        for (var i = 1; i < estimated_time_len; i++) {
                            layDate.render({
                                elem: '#estimated_data_' + i,
                                type: 'date',
                                trigger: 'click'
                            });
                            layDate.render({
                                elem: '#estimated_time_' + i,
                                type: 'time',
                                trigger: 'click',
                                range: true
                            });
                        }
                    }
                    layDate.render({
                        elem: '#estimated_data_0',
                        type: 'date',
                        trigger: 'click'
                    });
                    layDate.render({
                        elem: '#estimated_time_0',
                        type: 'time',
                        trigger: 'click',
                        range: true
                    });

                }
            } else {
                $("input[name=is_estimated_service_time]").removeAttr('checked');
            }
            // if (res.data.is_info_header_bottom_goods == 1) {
            //     $("input[name=is_info_header_bottom_goods]").prop('checked', true);
            // } else {
            //     $("input[name=is_info_header_bottom_goods]").removeAttr('checked');
            // }
            form.render();
        }

        //设置资料
        form.on('submit(setInfo)', function () {

            var subData = {
                name: $("input[name=name]").val(),
                pic_url: $('#image img').attr('src'),
                phone: $("input[name=phone]").val(),
                supplier_phone: $("input[name=supplier_phone]").val(),
                leader_phone: $("input[name=leader_phone]").val(),
                pic_url_login: $('#imageLogin img').attr('src'),
                default_pic_url: $('#imageDefault img').attr('src'),
                detail_info: $("textarea[name=detail_info]").val(),
                user_vip: $('input[name=user_vip]:checked').val(),
                pay_info: $('input[name=pay_info]:checked').val() ? 1 : 0,
                score_shop: $('input[name=score_shop]:checked').val() ? 1 : 0,
                leader_level: $('input[name=leader_level]:checked').val() ? 1 : 0,
                my_mini_info: $('input[name=my_mini_info]:checked').val() ? 1 : 0,
                good_phenosphere: $('input[name=good_phenosphere]:checked').val() ? 1 : 0,
                balance_pay: $('input[name=balance_pay]:checked').val() ? 1 : 0,
                shansong: $('input[name=shansong]:checked').val() ? 1 : 0,
                uu_is_open: $('input[name=uu_is_open]:checked').val() ? 1 : 0,
                store_is_open: $('input[name=store_is_open]:checked').val() ? 1 : 0,
                yly_print: $('input[name=yly_print]:checked').val() ? 1 : 0,
                is_stock: $('input[name=is_stock]:checked').val() ? 1 : 0,
                is_merchant_info: $('input[name=is_merchant_info]:checked').val() ? 1 : 0,
                is_info_header: $('input[name=is_info_header]:checked').val() ? 1 : 0,
                is_info_bottom: $('input[name=is_info_bottom]:checked').val() ? 1 : 0,
                // is_info_header_bottom_goods: $('input[name=is_info_header_bottom_goods]:checked').val() ? 1 : 0,
                leader_confirm: $("input[name=leader_confirm]").val(),//三个天数
                leader_send: $("input[name=leader_send]").val(),
                user_confirm: $("input[name=user_confirm]").val(),
                coordinate: $("input[name=coordinate]").val(),
                starting_price: $("input[name=starting_price]").val(),
                key: saa_key
            }
            if (saa_category_id == '2') {
                subData.shop_category_id = $("select[name=shop_category_id]").val();
            }
            //拼装预计送达时间数据
            var estimated_data = []
            if (estimated_type == 2){
                $('input[name=week]').each(function (i, j) {
                    if (j.checked) {
                        estimated_data.push(j.value);
                    }
                });
            } else if (estimated_type == 3){
                $('input[name=estimated_data]').each(function (i, j) {
                    estimated_data.push(j.value);
                });
            }
            var estimated_time = []
            $('input[name=estimated_time]').each(function (i, j) {
                estimated_time.push(j.value);
            });
            subData.estimated_service_time_info = {
                is_estimated: $('input[name=is_estimated_service_time]:checked').val() ? 1 : 0,
                estimated_type:estimated_type,
                estimated_data:estimated_data,
                estimated_time:estimated_time
            }

            //提交修改
            arr = {
                method: 'merchantAppInfo/' + saa_id,
                type: 'put',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                sessionStorage.setItem('logoImg', JSON.stringify({key: saa_key, src: res.data.pic_url}));
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
                layer.close(openIndex);
            }
        });

        /*动态添加单选框 应用分组*/
        function getGroups() {
            arr = {
                method: 'merchantShopCategory',
                type: 'get'
            };
            res = getAjaxReturn(arr);
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

        //上传图片现方法
        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.addImgPut').on('click', '.addImgPut', function () {
            sessionStorage.setItem('images_common_div', '#image');
            sessionStorage.setItem('images_common_div_info', '<img width="' + set_image_width + '" height="' + set_image_height + '">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.addImgPutLogin').on('click', '.addImgPutLogin', function () {
            sessionStorage.setItem('images_common_div', '#imageLogin');
            sessionStorage.setItem('images_common_div_info', '<img width="'+set_image_width+'" height="' + set_image_height + '">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.addImgPutDefault').on('click', '.addImgPutDefault', function () {
            sessionStorage.setItem('images_common_div', '#imageDefault');
            sessionStorage.setItem('images_common_div_info', '<img width="'+set_image_width+'" height="' + set_image_height + '">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

        //开启预计送达按钮点击事件
        form.on('switch(is_estimated_service_time)', function (data) {
            if (data.elem.checked) {
                $('.estimated_service_time_info').append(
                    '<div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">模式</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                    <select name="estimated_type" lay-verify="" lay-filter="estimated_type">\n' +
                    '                       <option value="1">每日</option>\n' +
                    '                       <option value="2">每周</option>\n' +
                    '                       <option value="3">按固定时间段</option>\n' +
                    '                    </select> ' +
                    '                    </div>\n' +
                    '</div>\n' +
                    '<div class="time_list">\n' +
                    '    <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">活动时间</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_time"\n' +
                    '                               id="estimated_time_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <a href="javascript: void(0)" class="estimated_add" style="color: limegreen;">添加</a>\n' +
                    '    </div>\n' +
                    '    <div class="estimated_service_time_list"></div>' +
                    '</div>\n'
                );
                layDate.render({
                    elem: '#estimated_time_' + estimated_num,
                    type: 'time',
                    trigger: 'click',
                    range: true
                });
                form.render();
            } else {
                estimated_type = 1;
                estimated_num = 0;
                $('.estimated_service_time_info').empty();
            }
        });

        //添加预计送达时间
        $(document).off('click', '.estimated_add').on('click', '.estimated_add', function () {
            estimated_num++;
            if (estimated_type == 3){
                $('.estimated_service_time_list').append(
                    '<div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">活动时间</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_data"\n' +
                    '                               id="estimated_data_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_time"\n' +
                    '                               id="estimated_time_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <a href="javascript: void(0)" class="estimated_del" style="color: red;">删除</a>\n' +
                    '</div>\n'
                );
                layDate.render({
                    elem: '#estimated_data_' + estimated_num,
                    type: 'date',
                    trigger: 'click'
                });
                layDate.render({
                    elem: '#estimated_time_' + estimated_num,
                    type: 'time',
                    trigger: 'click',
                    range: true
                });
            } else if (estimated_type == 1){
                $('.estimated_service_time_list').append(
                    '<div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">活动时间</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_time"\n' +
                    '                               id="estimated_time_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <a href="javascript: void(0)" class="estimated_del" style="color: red;">删除</a>\n' +
                    '</div>\n'
                );
                layDate.render({
                    elem: '#estimated_time_' + estimated_num,
                    type: 'time',
                    trigger: 'click',
                    range: true
                });
            }  else if (estimated_type == 2){
                $('.estimated_service_time_list').append(
                    '<div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"></label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_time"\n' +
                    '                               id="estimated_time_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <a href="javascript: void(0)" class="estimated_del" style="color: red;">删除</a>\n' +
                    '</div>\n'
                );
                layDate.render({
                    elem: '#estimated_time_' + estimated_num,
                    type: 'time',
                    trigger: 'click',
                    range: true
                });
            }

        });

        //删除预计送达时间
        $(document).off('click', '.estimated_del').on('click', '.estimated_del', function () {
            estimated_num--;
            $(this).parent().remove();
        });

        //预计送达时间下拉列表
        form.on('select(estimated_type)', function(data){
            estimated_type = data.value
            if (data.value == 3){
                $('.time_list').empty();
                estimated_num = 0;
                $('.estimated_service_time_info').append(
                    '<div class="time_list">\n' +
                    '    <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">活动时间</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_data"\n' +
                    '                               id="estimated_data_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_time"\n' +
                    '                               id="estimated_time_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <a href="javascript: void(0)" class="estimated_add" style="color: limegreen;">添加</a>\n' +
                    '    </div>\n' +
                    '    <div class="estimated_service_time_list"></div>' +
                    '</div>\n'
                );
                layDate.render({
                    elem: '#estimated_data_' + estimated_num,
                    type: 'date',
                    trigger: 'click'
                });
                layDate.render({
                    elem: '#estimated_time_' + estimated_num,
                    type: 'time',
                    trigger: 'click',
                    range: true
                });
            } else if (data.value == 1){
                $('.time_list').empty();
                estimated_num = 0;
                $('.estimated_service_time_info').append(
                    '<div class="time_list">\n' +
                    '    <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">活动时间</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_time"\n' +
                    '                               id="estimated_time_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <a href="javascript: void(0)" class="estimated_add" style="color: limegreen;">添加</a>\n' +
                    '    </div>\n' +
                    '    <div class="estimated_service_time_list"></div>' +
                    '</div>\n'
                );
                layDate.render({
                    elem: '#estimated_time_' + estimated_num,
                    type: 'time',
                    trigger: 'click',
                    range: true
                });
            } else if (data.value == 2){
                $('.time_list').empty();
                estimated_num = 0;
                $('.estimated_service_time_info').append(
                    '<div class="time_list">\n' +
                    '    <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">活动时间</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                       <input type="checkbox" name="week" title="周一" value="周一" lay-skin="primary">\n' +
                    '                       <input type="checkbox" name="week" title="周二" value="周二" lay-skin="primary">\n' +
                    '                       <input type="checkbox" name="week" title="周三" value="周三" lay-skin="primary">\n' +
                    '                       <input type="checkbox" name="week" title="周四" value="周四" lay-skin="primary">\n' +
                    '                       <input type="checkbox" name="week" title="周五" value="周五" lay-skin="primary">\n' +
                    '                       <input type="checkbox" name="week" title="周六" value="周六" lay-skin="primary">\n' +
                    '                       <input type="checkbox" name="week" title="周日" value="周日" lay-skin="primary">\n' +
                    '                    </div>\n' +
                    '    </div>\n' +
                    '    <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"></label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="estimated_time"\n' +
                    '                               id="estimated_time_' + estimated_num +'">\n' +
                    '                    </div>\n' +
                    '                    <a href="javascript: void(0)" class="estimated_add" style="color: limegreen;">添加</a>\n' +
                    '    </div>\n' +
                    '    <div class="estimated_service_time_list"></div>' +
                    '</div>\n'
                );
                form.render();
                layDate.render({
                    elem: '#estimated_time_' + estimated_num,
                    type: 'time',
                    trigger: 'click',
                    range: true
                });

            }

        });

    });

    exports('appSet/info', {})
});