/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2018/5/17
 * Update DateTime: 2019/3/13
 * js 营销 签到
 */

layui.define(function (exports) {
    /**
     * use 首参简单解释
     *
     * jquery 必须 很多地方那个用到，必须定义
     * setter 必须 获取config 配置，但不必定义
     * admin 必须 若未用到则不必定义
     * table 不必须 若表格渲染，若无表格操作点击事件，可不必定义
     * form 不必须 表单操作，一般用于页面有新增和编辑
     * laydate 不必须 日期选择器
     */
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr, res;//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        form.render();

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }

        //进入营销菜单必须执行方法，获取该应用的自定义版权状态，如果为1则显示自定义版权，为0则需要隐藏
        //之前写在layout里，太消耗性能，所以写在营销菜单下的所有页面里
        arr = {
            method: 'merchantCopyright',
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data && res.data.copyright && res.data.copyright === '1') {
            if ($('.copyright_li').length <= 0) {
                $('.voucher_ul').append('<li class="copyright_li"><a lay-href="voucher/copyright">自定义版权</a></li>');
            }
        } else {
            $('.copyright_li').remove();
        }

        /*diy设置开始*/

        var start_time, end_time;//定义开始和结束时间

        //页面不同属性
        var ajax_method = 'merchantSignIn';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'name', title: '活动名称', width: '20%'},
            {field: 'start_time', title: '活动日期', width: '10%'},
            {field: 'end_time', title: '活动日期', width: '10%'},
            {field: 'pic_url_activity', title: '活动背景', templet: '#activityTpl', width: '10%'},
            {field: 'pic_url_sign', title: '签到默认背景', templet: '#signTpl', width: '10%'},
            {field: 'remark', title: '签到说明', width: '10%'},
            {field: 'status', title: '状态', templet: '#statusTpl', width: '10%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '20%'}
        ];

        //先获取已设置的最大结束日期作为日期控件的最小日期
        var today = new Date().format("yyyy-MM-dd");
        arr = {
            method: 'merchantSignInTime',
            type: 'get',
        };
        res = getAjaxReturnKey(arr);
        var min_date = today;
        if (res && res.data) {
            min_date = res.data > min_date ? res.data : min_date;
        }
        /*diy设置结束*/

        //获取签到总开关设置
        arr = {
            method: 'merchantUnits',
            type: 'get',
            params: 'route=signIn'
        };
        res = getAjaxReturnKey(arr);
        var sign_in_id = 0;
        if (res && res.data) {
            sign_in_id = res.data.id;
            if (res.data.is_open === '1') {
                $("input[name=sign_in_status]").prop('checked', true);
            } else {
                $("input[name=sign_in_status]").removeAttr('checked');
            }
            form.render();
        }
        //签到总开关
        form.on('switch(sign_in_status)', function (obj) {
            arr = {
                method: 'merchantUnits/' + sign_in_id,
                type: 'put',
                data: {
                    status: obj.elem.checked ? 1 : 0,
                    route: 'signIn'
                }
            };
            if (getAjaxReturnKey(arr)) {
                layer.msg(sucMsg.put);
                layer.close(open_index);
            }
        });

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            $('.supplementary').hide();//补签隐藏
            $('.continuous_item').empty();//连续签到天数清空
            $('.continuous').hide();//连续签到天数隐藏
            $('.quotations_item').empty();//打卡语录清空
            $("#image_activity").empty();
            $("#image_sign").empty();

            /*diy设置开始*/
            form.render();//还原后需要重置表单
            ajax_type = 'post';//设置类型为新增
            getLayDateRender(min_date);//设置layDate
            open_index = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0,
                offset: '2vw',
                area: ['50vw', '40vw'],
                cancel: function () {
                    add_edit_form.hide();
                }
            })
        });

        //补签是否开启事件
        form.on('switch(supplementary)', function (e) {
            //判断连续签到开关是否开启，如果开启则显示连续签到设置，否则隐藏
            if (e.elem.checked) {
                $('.supplementary').show();
            } else {
                $('.supplementary').hide();
            }
        });

        //连续签到是否开启事件
        form.on('switch(continuous)', function (e) {
            //判断连续签到开关是否开启，如果开启则显示连续签到设置，否则隐藏
            $('.continuous_item').empty();
            if (e.elem.checked) {
                $('.continuous').show();
            } else {
                $('.continuous').hide();
            }
        });

        //添加连续签到天数事件，最多三条
        $(document).off('click', '.add').on('click', '.add', function () {
            //循环查找 continuous_item 下有几个子元素，超过两个个给予提示
            var c_len = $('.continuous_item').children().length;
            if (c_len >= 2) {
                layer.msg('连续签到天数最多可设置三条', {icon: 1, time: 2000});
                return;
            }
            $('.continuous_item').append(getContinuous('1', 0));
            form.render();
        });

        //删除连续签到天数事件
        $(document).off('click', '.delete').on('click', '.delete', function () {
            $(this).parent().parent().remove();
        });

        //连续签到天数获取类型改变事件，如果是1积分，则为文本框，否则下拉框
        form.on('select(give_type)', function (data) {
            var give_type = data.value;
            var item_div = $(this).parent().parent().parent().parent();
            if (give_type === '1') {
                //如果类型为积分，则添加积分文本框
                item_div.find('.give_values').empty().append('<input name="give_value" lay-verify="number" placeholder="积分" class="layui-input">');
            } else {
                //如果类型不是积分，则添加下拉框
                item_div.find('.give_values').empty().append('<select name="give_value" lay-filter="give_value"></select><input name="give_value" style="display: none;">');
            }
            form.render();
            //获取需要操作的 select
            var select_div = item_div.find('select[name=give_value]');//当前选择对应的 select 下拉框
            if (give_type === '2') {
                getGroupVoucher(0, select_div);
            } else if (give_type === '3') {
                getGroupGoods(0, select_div);
            }
        });

        //优惠券或商品下拉框改变事件 需要将 id和name存入隐藏框
        form.on('select(give_value)', function (data) {
            $($(this).parent().parent().parent().find('input[name=give_value]')).val(data.value + '_' + this.innerHTML);
        });

        //添加打卡语录事件，最多十条
        $(document).off('click', '.add_quotations').on('click', '.add_quotations', function () {
            //循环查找 quotations_item 下有几个子元素，超过九个给予提示
            var q_len = $('.quotations_item').children().length;
            if (q_len >= 9) {
                layer.msg('打卡语录最多可设置十条', {icon: 1, time: 2000});
                return;
            }
            $('.quotations_item').append(getQuotations(''));
        });

        //删除打卡语录事件
        $(document).off('click', '.delete_quotations').on('click', '.delete_quotations', function () {
            $(this).parent().parent().remove();
        });

        //执行添加或编辑
        form.on('submit(sub)', function () {
            if ($('input[name=start_time]').val() === '') {
                layer.msg('请选择活动开始日期', {icon: 1, time: 2000});
                return;
            }
            if ($('input[name=end_time]').val() === '') {
                layer.msg('请选择活动结束日期', {icon: 1, time: 2000});
                return;
            }
            if ($('#image_activity img').attr('src') === '') {
                layer.msg('请选择活动背景图片', {icon: 1, time: 2000});
                return;
            }
            if ($('#image_sign img').attr('src') === '') {
                layer.msg('请选择签到默认背景图片', {icon: 1, time: 2000});
                return;
            }
            var success_msg;
            var method;
            if (ajax_type === 'post') {
                method = ajax_method;
                success_msg = sucMsg.post;
            } else if (ajax_type === 'put') {
                method = ajax_method + '/' + operation_id;
                success_msg = sucMsg.put;
            }
            var continuous = $('input[name=continuous]:checked').val() ? 1 : 0;
            var continuous_arr = {};
            //判断连续签到是否开启，如果开启获取连续签到数组
            if (continuous) {
                //获取连续签到天数
                var days = '';
                var days_arr = [];
                $('input[name=days]').each(function () {
                    if (Trim(this.value) != '') {
                        days += this.value + ',';
                        days_arr.push(this.value);
                    }
                });
                //获取设置的签到天数，加入数组，并与去重对比，相同则无重复，有重复需要提示
                if (days_arr.length !== duplicateRemoval(days_arr).length) {
                    layer.msg('连续打卡天数不能有相同的设置', {icon: 1, time: 2000});
                    return;
                }
                //获取获取获取类型
                var give_type = '';
                $('select[name=give_type]').each(function () {
                    if (Trim(this.value) != '') {
                        give_type += this.value + ',';
                    }
                });
                //获取获取获取类型
                var give_value = '';
                $('input[name=give_value]').each(function () {
                    if (Trim(this.value) != '') {
                        give_value += this.value + ',';
                    }
                });
                continuous_arr = {
                    days: days,
                    give_type: give_type,
                    give_value: give_value,
                };
            }
            //获取打卡语录
            var quotations = '';
            $('input[name=quotations]').each(function () {
                if (Trim(this.value) != '') {
                    quotations += this.value + ',';
                }
            });
            arr = {
                method: method,
                type: ajax_type,
                data: {
                    name: $('input[name=name]').val(),//活动名称
                    start_time: $('input[name=start_time]').val(),//开始日期
                    end_time: $('input[name=end_time]').val(),//结束日期
                    integral: $('input[name=integral]').val(),//每日签到积分
                    pic_url_activity: $('#image_activity img').attr('src'),//活动背景
                    pic_url_sign: $('#image_sign img').attr('src'),//签到默认背景
                    supplementary: $('input[name=supplementary]:checked').val() ? 1 : 0,//补签是否开启
                    supplementary_price: $('input[name=supplementary_price]').val(),//单次补签费用
                    supplementary_number: $('input[name=supplementary_number]').val(),//最多补签次数
                    continuous: continuous,//连续签到是否开启
                    continuous_arr: continuous_arr,//连续签到未开始是空obj，连续签到开启包含内容 连续签到天数、获取类型、类型对应的值
                    remark: $('textarea[name=remark]').val(),//签到说明
                    quotations: quotations,//打卡语录 字符串
                    status: $('input[name=status]:checked').val() ? 1 : 0,//状态
                }
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(success_msg);
                layer.close(open_index);
                add_edit_form[0].reset();//表单重置
                add_edit_form.hide();
                render.reload();//表格局部刷新
            }
        });

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'edit') {//修改
                $("#add_edit_form")[0].reset();//表单重置  必须
                $("input[name='status']").prop('checked', true);//还原状态设置为 true
                $('.supplementary').hide();//补签隐藏
                $('.continuous_item').empty();//连续签到天数清空
                $('.continuous').hide();//连续签到天数隐藏
                $('.quotations_item').empty();//打卡语录清空
                $("#image_activity").empty();
                $("#image_sign").empty();
                ajax_type = 'put';
                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get',
                };
                res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    getLayDateRender(today);//重新渲染laydate
                    /*diy设置开始*/
                    $("input[name=name]").val(res.data.name);
                    $("input[name=start_time]").val(res.data.start_time);
                    $("input[name=end_time]").val(res.data.end_time);
                    $("input[name=integral]").val(res.data.integral);
                    $("#image_activity").append('<img src="' + res.data.pic_url_activity + '" width="100px" height="100px">');
                    $("#image_sign").append('<img src="' + res.data.pic_url_sign + '" width="100px" height="100px">');
                    if (res.data.supplementary == 1) {//补签开启
                        $("input[name=supplementary]").prop('checked', true);
                        $("input[name=supplementary_price]").val(parseInt(res.data.supplementary_price));
                        $("input[name=supplementary_number]").val(res.data.supplementary_number);
                        $('.supplementary').show();
                    } else {
                        $("input[name=supplementary]").removeAttr('checked');
                        $('.supplementary').hide();
                    }
                    if (res.data.continuous == 1) {//连续签到开启
                        $("input[name=continuous]").prop('checked', true);
                        //循环添加签到信息
                        var continuous_arr = res.data.continuous_arr;
                        var days = continuous_arr.days.substr(0, continuous_arr.days.length - 1).split(',');
                        var d_len = days.length;
                        var give_type = continuous_arr.give_type.substr(0, continuous_arr.give_type.length - 1).split(',');
                        var give_value = continuous_arr.give_value.substr(0, continuous_arr.give_value.length - 1).split(',');
                        for (var i = 0; i < d_len; i++) {
                            var id = give_value[i].split('_')[0];
                            if (i === 0) {//第一条数据直接插入现有文本框
                                $("input[name=days]").val(days[i]);
                                $("select[name=give_type]").val(give_type[i]);
                                if (give_type[i] !== '1') {
                                    $('.give_values').empty().append('<select name="give_value" lay-filter="give_value"></select><input name="give_value" style="display: none;">');
                                }
                                $("input[name=give_value]").val(give_value[i]);
                                if (give_type[i] === '2') {
                                    //需要获取优惠券列表，并选中对应的值
                                    getGroupVoucher(id, $('.give_values').find('select[name=give_value]'));
                                } else if (give_type[i] === '3') {
                                    getGroupGoods(id, $('.give_values').find('select[name=give_value]'));
                                }
                            } else {//多出来的需要新增整条数据
                                var continuous = getContinuous(give_type[i], days[i]);
                                $('.continuous_item').append(continuous);
                                form.render();
                                if (give_type[i] === '1') {
                                    $('input[name=give_value]').last().val(give_value[i]);
                                } else if (give_type[i] === '2') {
                                    getGroupVoucher(id, $('select[name=give_value]').last());
                                } else if (give_type[i] === '3') {
                                    getGroupGoods(id, $('select[name=give_value]').last());
                                }
                            }
                        }

                        $('.continuous').show();
                    } else {
                        $("input[name=continuous]").removeAttr('checked');
                        $('.continuous').hide();
                    }
                    $("textarea[name=remark]").val(res.data.remark);
                    //循环添加语录
                    var quotations = res.data.quotations.substr(0, res.data.quotations.length - 1).split(',');
                    var q_len = quotations.length;
                    if (q_len > 0) {
                        for (var i = 0; i < q_len; i++) {
                            if (i === 0) {//第一条数据直接插入现有文本框
                                $("input[name=quotations]").val(quotations[i]);
                            } else {//多出来的需要新增文本框，参照打卡语录新增事件
                                $('.quotations_item').append(getQuotations(quotations[i]));
                            }
                        }
                    }
                    if (res.data.status == 1) {
                        $("input[name=status]").prop('checked', true);
                    } else {
                        $("input[name=status]").removeAttr('checked');
                    }
                    /*diy设置结束*/
                    form.render();//设置完值需要刷新表单
                    open_index = layer.open({
                        type: 1,
                        title: '编辑',
                        content: add_edit_form,
                        shade: 0,
                        offset: '2vw',
                        area: ['50vw', '40vw'],
                        cancel: function () {
                            add_edit_form.hide();
                        }
                    })
                }
            } else if (layEvent === 'show') {//查看签到记录
                sessionStorage.setItem('sign_in_id', data.id);
                sessionStorage.setItem('sign_in_start_time', data.start_time);
                sessionStorage.setItem('sign_in_end_time', data.end_time);
                location.hash = '/voucher/signInInfo';
            } else if (layEvent === 'get') {//查看签到记录
                sessionStorage.setItem('sign_in_id', data.id);
                location.hash = '/voucher/signInPrize';
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    arr = {
                        method: ajax_method + '/' + data.id,
                        type: 'delete',
                    };
                    if (getAjaxReturnKey(arr)) {
                        layer.msg(sucMsg.delete);
                        obj.del();
                    }
                })
            } else {
                layer.msg(setter.errorMsg);
            }
        });

        /*动态添加单选框 优惠券分组*/
        function getGroupVoucher(group_id, select_div) {
            arr = {
                method: 'shopVouTypes',
                type: 'get',
                params: 'type=9'//9为签到专用
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var name;
                var id;
                for (var a = 0; a < res.data.length; a++) {
                    name = res.data[a].name;
                    id = res.data[a].id;
                    if (group_id) {
                        var selected = '';
                        if (group_id === id) {
                            selected = ' selected ';
                        }
                        select_div.append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        select_div.append("<option value=" + id + ">" + name + "</option>");
                    }
                    //将获取到的第一个值存入隐藏框，方便最后取值
                    if (a === 0) {
                        select_div.parent().find('input[name=give_value]').val(id + '_' + name);
                    }
                    form.render();
                }
            }
        }

        /*动态添加单选框 商品分组*/
        function getGroupGoods(group_id, select_div) {
            arr = {
                method: 'merchantGoods',
                type: 'get',
            };
            res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var name;
                var id;
                for (var a = 0; a < res.data.length; a++) {
                    name = res.data[a].name;
                    id = res.data[a].id;
                    if (group_id) {
                        var selected = '';
                        if (group_id === id) {
                            selected = ' selected ';
                        }
                        select_div.append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        select_div.append("<option value=" + id + ">" + name + "</option>");
                    }
                    //将获取到的第一个值存入隐藏框，方便最后取值
                    if (a === 0) {
                        select_div.parent().find('input[name=give_value]').val(id + '_' + name);
                    }
                    form.render();
                }
            }
        }

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols],//加载的表格字段
        };
        var render = getTableRender(arr);//变量名对应 arr 中的 name

        //搜索
        form.on('submit(find)', function (data) {//查询
            render.reload({
                where: {searchName: data.field.searchName},
                page: {curr: 1}
            });
        });

        //修改状态
        form.on('switch(status)', function (obj) {
            arr = {
                method: ajax_method + '/' + this.value,
                type: 'put',
                data: {status: obj.elem.checked ? 1 : 0},
            };
            if (getAjaxReturnKey(arr)) {
                layer.msg(sucMsg.put);
                layer.close(open_index);
            }
        });

        //对选择日期重新渲染
        function getLayDateRender(date) {
            $('.times').empty().append('<div class="layui-input-inline">\n' +
                '                    <input name="start_time" id="start_time" placeholder="开始日期" readonly class="layui-input"/>\n' +
                '                </div>\n' +
                '                <div class="layui-input-inline">\n' +
                '                    <input name="end_time" id="end_time" placeholder="结束日期" readonly class="layui-input"/>\n' +
                '                </div>');//重新追加选择日期的div
            //日期
            layDate.render({
                elem: '#start_time',
                type: 'date',
                min: date,
                value: date,
                done: function (value) {
                    end_time = $('#end_time').val();
                    if (end_time !== '' && value > end_time) {
                        layer.msg('活动未开始就结束了，请重新选择时间！', {icon: 1, time: 1000}, function () {
                            $('#start_time').focus();
                        });
                    }
                }
            });
            layDate.render({
                elem: '#end_time',
                type: 'date',
                min: date,
                done: function (value) {
                    start_time = $('#start_time').val();
                    if (start_time !== '' && value < start_time) {
                        layer.msg('活动未开始就结束了，请重新选择时间！', {icon: 1, time: 1000}, function () {
                            $('#end_time').focus();
                        });
                    }
                }
            });
        }

        //上传图片现方法
        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.add_img_put_activity').on('click', '.add_img_put_activity', function () {
            sessionStorage.setItem('images_common_div', '#image_activity');
            sessionStorage.setItem('images_common_div_info', '<img width="100px" height="100px">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.add_img_put_sign').on('click', '.add_img_put_sign', function () {
            sessionStorage.setItem('images_common_div', '#image_sign');
            sessionStorage.setItem('images_common_div_info', '<img width="100px" height="100px">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

    });
    exports('voucher/signIn', {})
});

//添加打卡语录，新增的时候参数是空，编辑的时候，参数需要循环获取传过来
function getQuotations(quotation) {
    return '        <div class="layui-form-item">\n' +
        '                <label class="layui-form-label"></label>\n' +
        '                <div class="layui-input-inline">\n' +
        '                    <input name="quotations" maxlength="15" value="' + quotation + '" required lay-verify="required" class="layui-input">\n' +
        '                </div>\n' +
        '                <div class="layui-input-inline">\n' +
        '                    <span class="layui-btn layui-btn-danger delete_quotations" style="width: 4vw;">删除</span>\n' +
        '                </div>\n' +
        '            </div>';
}

//添加连续签到天数，新增默认类型为积分，后面跟着文本框，give_type=1，是文本框，2和3是下拉框
function getContinuous(give_type, days) {
    var input = '<input name="give_value" value="0" lay-verify="number" placeholder="积分" class="layui-input">';
    if (give_type !== '1') {
        input = '<select name="give_value" lay-filter="give_value"></select><input name="give_value" style="display: none;">';
    }
    var value1 = '', value2 = '', value3 = '';
    if (give_type === '1') {
        value1 = ' selected';
    } else if (give_type === '2') {
        value2 = ' selected';
    } else if (give_type === '3') {
        value3 = ' selected';
    }
    return '            <div class="layui-form-item">\n' +
        '                    <label class="layui-form-label"></label>\n' +
        '                    <div class="layui-input-inline" style="width: 5vw;">\n' +
        '                        <input name="days" value="' + days + '" lay-verify="number" placeholder="天数" class="layui-input">\n' +
        '                    </div>\n' +
        '                    <label class="layui-form-label" style="width: 2vw;">获取</label>\n' +
        '                    <div class="layui-input-inline" style="width: 6vw;">\n' +
        '                        <select name="give_type" lay-filter="give_type">\n' +
        '                            <option value="1" ' + value1 + '>积分</option>\n' +
        '                            <option value="2" ' + value2 + '>优惠券</option>\n' +
        '                            <option value="3" ' + value3 + '>实物商品</option>\n' +
        '                        </select>\n' +
        '                    </div>\n' +
        '                    <div class="layui-input-inline" style="width: 10vw;">\n' +
        '                        <div class="give_values">\n' +
        input +
        '                        </div>\n' +
        '                    </div>\n' +
        '                    <div class="layui-input-inline">\n' +
        '                        <span class="layui-btn layui-btn-danger delete" style="width: 4vw;">删除</span>\n' +
        '                    </div>\n' +
        '                </div>';
}
