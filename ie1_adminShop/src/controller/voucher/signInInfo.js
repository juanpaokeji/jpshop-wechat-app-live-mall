/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/2/13
 * 插件-签到
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var sign_in_id = sessionStorage.getItem('sign_in_id');//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var add_edit_form = $('#add_edit_form');//常用的表单
        var render;
        form.render();

        var ajax_method = 'merchantSign';//新ajax需要的参数 method
        var cols = [//加载的表格
            //头像 昵称 连续签到次数 累计次数 操作查看详情
            {field: 'avatar', title: '头像', templet: '#imgTpl'},
            {field: 'nickname', title: '昵称'},
            {field: 'series', title: '连续签到次数'},
            {field: 'total', title: '累计次数'},
            {field: 'create_time', title: '签到时间'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];

        //获取该活动的日期范围，如果未结束则显示今天日期，如果已结束显示最后一天的日期，并获取当天的签到人员记录
        var start_time = sessionStorage.getItem('sign_in_start_time');//获取之前页面传过来的开始日期
        var end_time = sessionStorage.getItem('sign_in_end_time');//获取之前页面传过来的结束日期
        var today = new Date().format('yyyy-MM-dd');//格式化的今天日期
        if (start_time > end_time) {//如果开始时间大于结束时间，则肯定是违规操作，不予理会
            return;
        }
        var diff_days = Math.abs((new Date(start_time) - new Date(end_time))) / (1000 * 60 * 60 * 24);//获取间隔天数
        var this_date = end_time;//结束日期
        var date_arr = [this_date];
        var dates_div = $('#dates');
        dates_div.empty().append("<option value=" + this_date + ">" + this_date + "</option>");
        for (var i = 0; i < diff_days; i++) {
            this_date = new Date(new Date(this_date).setDate(new Date(this_date).getDate() - 1)).format('yyyy-MM-dd');
            date_arr.push(this_date);
            dates_div.append("<option value=" + this_date + ">" + this_date + "</option>");
        }

        //获取默认加载列表
        function getRenderData() {
            if (today < start_time) {
                //活动尚未开始
                layer.msg('活动尚未开始', {icon: 1, time: 2000});
                return false;
            } else if (today > end_time) {
                //活动已结束，查询活动最后一天记录
                getRender(end_time);
                $('select[name=dates]').val(end_time);
            } else {
                //活动中，查询当天记录
                getRender(today);
                $("select[name=dates]").val(today);
            }
        }

        getRenderData();
        form.render();

        //切换日期查询签到人员记录
        form.on('select(dates)', function (data) {
            if (render) {
                render.reload({
                    where: {time: data.value},
                    page: {curr: 1}
                });
            } else {
                getRenderData();
            }
            form.render();
        })

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            if (layEvent === 'show') {//查看签到记录
                arr = {
                    method: 'merchantSignUser/' + sign_in_id,
                    type: 'get',
                    params: 'user_id=' + data.user_id,
                };
                var res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    //循环活动天数，判断该用户每一天是否签到，已签到和未签到分开显示
                    var res_data = res.data;//用户签到数据
                    //将data数据转化为可判断的数组
                    var res_data_arr = [];
                    for (var j = 0; j < res_data.length; j++) {
                        res_data_arr.push(res_data[j].create_time.substr(0, 10));
                    }
                    var len = date_arr.length;//活动的天数
                    add_edit_form.empty();
                    for (var i = 0; i < len; i++) {
                        var date_s = date_arr[i];
                        if (date_s <= new Date().format('yyyy-MM-dd')) {//只显示今天之前的，后面的不需要显示
                            if (res_data_arr.indexOf(date_s) > -1) {
                                add_edit_form.append('<div class="layui-col-md12 check_in">\n' +
                                    '            <span>' + date_s + '</span>--<span>已签到</span>\n' +
                                    '        </div>');
                            } else {
                                add_edit_form.append('<div class="layui-col-md12 no_sign_in">\n' +
                                    '            <span>' + date_s + '</span>--<span>未签到</span>\n' +
                                    '        </div>');
                            }
                        }

                    }
                    form.render();
                    open_index = layer.open({
                        type: 1,
                        title: '签到记录',
                        content: add_edit_form,
                        shade: 0,
                        offset: '100px',
                        area: ['400px', 'auto'],
                        cancel: function () {
                            add_edit_form.hide();
                        }
                    })
                }
            } else {
                layer.msg(setter.errorMsg);
            }
        });

        //显示所有签到人员
        form.on('submit(show_users)', function () {
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTable',//需要加载的 table 表格对应的 id
                method: 'merchantSignUserAll/' + sign_in_id + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
                cols: [cols],//加载的表格字段
            };
            getTableRender(arr);//变量名对应 arr 中的 name
        });

        //用户签到的详情点击事件，可查看当前用户该活动中的签到日期
        $(document).off('click', '.user_sign_info').on('click', '.user_sign_info', function () {
            arr = {
                method: 'merchantSignUser/' + sign_in_id,
                type: 'get',
                params: 'user_id=' + $(this).attr('data'),
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                //循环活动天数，判断该用户每一天是否签到，已签到和未签到分开显示
                var data = res.data;//用户签到数据
                //将data数据转化为可判断的数组
                var res_data_arr = [];
                for (var j = 0; j < data.length; j++) {
                    res_data_arr.push(data[j].create_time.substr(0, 10));
                }
                var len = date_arr.length;//活动的天数
                add_edit_form.empty();
                for (var i = 0; i < len; i++) {
                    var date_s = date_arr[i];
                    if (res_data_arr.indexOf(date_s) > -1) {
                        add_edit_form.append('<div class="layui-col-md12 check_in">\n' +
                            '            <span>' + date_s + '</span>--<span>已签到</span>\n' +
                            '        </div>');
                    } else {
                        add_edit_form.append('<div class="layui-col-md12 no_sign_in">\n' +
                            '            <span>' + date_s + '</span>--<span>未签到</span>\n' +
                            '        </div>');
                    }
                }
                form.render();
                open_index = layer.open({
                    type: 1,
                    title: '签到记录',
                    content: add_edit_form,
                    shade: 0,
                    offset: '100px',
                    area: ['400px', 'auto'],
                    cancel: function () {
                        add_edit_form.hide();
                    }
                })
            }
        });

        //render简单封装，适用于当前页面
        function getRender(time) {
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTable',//需要加载的 table 表格对应的 id
                method: ajax_method + '/' + sign_in_id + '?key=' + saa_key + '&time=' + time,//请求的 api 接口方法和可能携带的参数 key
                cols: [cols],//加载的表格字段
            };
            render = getTableRender(arr);//变量名对应 arr 中的 name
        }

    });
    exports('voucher/signInInfo', {})
});
