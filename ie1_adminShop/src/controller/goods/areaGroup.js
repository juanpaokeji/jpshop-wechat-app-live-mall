/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/8/10 18:00  一直在更新，时间随时修改
 * js model
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        var province = '';//当前点击的省
        var cities = {};//当前点击的省对应的市列表
        var city = '';//当前点击的市
        var areas = {};//当前点击的市对应的区列表
        var all_area = {};//新增窗口获取到的所有省市区数组
        var all_area_len = 0;
        var s_cities = [];//最终提交的市级数据
        var s_areas = [];//最终提交的区级数据
        var group_data = 0;//是否已初始化级联 是 1 否 0

        /*diy设置开始*/
        //页面不同属性
        var ajax_method = 'merchantGoodsCityGroup';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'name', title: '分组名称', width: '25%'},
            {field: 'format_create_time', title: '创建时间', width: '25%'},
            {field: 'status', title: '状态', width: '25%', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '25%'}
        ];
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $('.province_name').css('background-color', 'white');
            $('.province_name').css('color', 'black');
            $('.city').empty();
            $('.area').empty();
            add_edit_form[0].reset();//表单重置  必须
            $('#image').attr('src', '');
            $("input[name='status']").prop('checked', true);//还原状态设置为true

            /*diy设置开始*/
            ajax_type = 'post';//设置类型为新增
            /*diy设置结束*/

            if (!group_data) {
                initRegion();
            }

            open_index = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0,
                offset: '100px',
                area: ['40vw', '35vw'],
                cancel: function () {
                    s_cities = [];
                    s_areas = [];
                    province = '';//当前点击的省
                    city = '';//当前点击的市
                    add_edit_form.hide();
                }
            })
        });

        //省级全选复选框点击事件 先值后样式
        form.on('checkbox(province_all)', function (data) {
            var checked = false;
            if (data.elem.checked) {
                checked = true;
            }
            //如果全选未选中，则初始化 s_cities 和 s_areas
            if (!checked) {
                s_cities = [];
                s_areas = [];
            } else {
                //循环所有的市和区存入 s_cities 和 s_areas 中
                for (var i = 0; i < all_area_len; i++) {
                    var this_province = all_area[i];
                    if (this_province.city && this_province.city.length > 0) {
                        var this_cities = this_province.city;
                        var this_cities_len = this_cities.length;
                        //循环当前省的市，存入 s_cities 中
                        for (var j = 0; j < this_cities_len; j++) {
                            s_cities.push(this_cities[j].code);
                            if (this_cities[j].area && this_cities[j].area.length > 0) {
                                var this_areas = this_cities[j].area;
                                //判断当前市是否有区，如果有，循环存入 s_areas 中
                                var this_area_len = this_cities[j].area.length;
                                for (var k = 0; k < this_area_len; k++) {
                                    s_areas.push(this_areas[k].code);
                                }
                            }
                        }
                    }
                }
            }
            //如果全选选中，则将所有市和区选中，如果展开的话
            $('input[name=province]').each(function (index, item) {//所有省选中
                item.checked = checked;
            });
            //市全选框选中
            if ($('input[name=city_all]')[0]) {
                $('input[name=city_all]')[0].checked = checked;
            }
            $('input[name=city]').each(function (index, item) {//所有打开的市选中
                item.checked = checked;
            });
            $('input[name=area]').each(function (index, item) {//所有打开的区选中
                item.checked = checked;
            });
            form.render();
        });

        //省级名称复选框点击事件 先值后样式
        form.on('checkbox(province)', function (data) {
            //判断该次点击是选中还是取消，选中则循环将该省对应的市和区存入 s_cities 和 s_areas
            var this_province_code = $(this).attr('id');//当前点击的省 code
            for (var i = 0; i < all_area_len; i++) {
                //循环查找这个 code 对应的省
                if (all_area[i].code === this_province_code && all_area[i].city && all_area[i].city.length) {
                    var this_cities = all_area[i].city;
                    var this_cities_len = this_cities.length;
                    //循环当前省的市，如果选中则存入 s_cities 中，否则从 s_cities 中删除
                    for (var j = 0; j < this_cities_len; j++) {
                        if (data.elem.checked) {
                            s_cities.push(this_cities[j].code);
                        } else {
                            deleteSpecifiedElement(s_cities, this_cities[j].code);
                        }
                        if (this_cities[j].area && this_cities[j].area.length > 0) {
                            var this_areas = this_cities[j].area;
                            //判断当前市是否有区，如果有，如果选中则循环存入 s_areas 中，否则从 s_areas 中删除
                            var this_area_len = this_cities[j].area.length;
                            for (var k = 0; k < this_area_len; k++) {
                                if (data.elem.checked) {
                                    s_areas.push(this_areas[k].code);
                                } else {
                                    deleteSpecifiedElement(s_areas, this_areas[k].code);
                                }
                            }
                        }
                    }
                }
            }

            //判断市数量和选中的是数量，如果相同则市级全选复选框选中，否则取消选中
            var checked = false;
            if (data.elem.checked) {
                checked = true;
            }
            //判断当前点击省和当前选中省是否为同一个，如果是同一个，则市区同步选中或取消选中
            if (province === this_province_code) {
                //市全选框选中
                if ($('input[name=city_all]')[0]) {
                    $('input[name=city_all]')[0].checked = checked;
                }
                $('input[name=city]').each(function (index, item) {//所有打开的市选中
                    item.checked = checked;
                });
                $('input[name=area]').each(function (index, item) {//所有打开的区选中
                    item.checked = checked;
                });
            }
            //判断省数量是否和选中的省数量相同，相同则全选复选框选中
            var is_province_all_checked = false;
            if ($('input[name=province]').length === $('input[name=province]:checked').length) {
                is_province_all_checked = true;
            }
            $('input[name=province_all]')[0].checked = is_province_all_checked;
            form.render();
        });

        //省级标题点击事件 不需要存值，只写样式
        $(document).off('click', '.province_name').on('click', '.province_name', function () {
            //判断是否重复点击同一个省，如果是不做操作
            if ($(this).parent().find('input').attr('id') === province) {
                return;
            }
            //获取当前点击的省级值
            province = $(this).parent().find('input').attr('id');
            //将所有省样式还原，并为当前点击的省添加样式
            $('.province_name').css('background-color', 'white');
            $('.province_name').css('color', 'black');
            $(this).css('background-color', 'cornflowerblue');
            $(this).css('color', 'white');
            $('.city').empty();
            $('.area').empty();
            //获取级联数据，通过已经获取的数据，查询省对应的市列表
            for (var a = 0; a < all_area.length; a++) {
                var code = all_area[a].code;
                if (province === code) {
                    cities = all_area[a].city;
                }
            }
            var c_len = cities.length;
            $('.city').append('<div class="city_div">\n' +
                '                    <input type="checkbox" lay-skin="primary" lay-filter="city_all" name="city_all"/>\n' +
                '                    <span>全选</span>\n' +
                '                </div>');
            for (var c = 0; c < c_len; c++) {
                $('.city').append(getCityDiv(cities[c]));
            }
            //判断当前省是否选中，如果选中，则循环所有市选中，若未选中，循环所有市判断是否有单独市选中
            if ($(this).parent().find('input')[0].checked) {
                //市全选框选中
                if ($('input[name=city_all]')[0]) {
                    $('input[name=city_all]')[0].checked = true;
                }
                $('input[name=city]').each(function (index, item) {//所有打开的市选中
                    item.checked = true;
                });
            } else {
                //循环中判断用的市
                var for_cities = {};
                for (var i = 0; i < all_area_len; i++) {
                    var this_province = all_area[i];
                    //判断循环中的省值是否为当前点击的省值
                    if (all_area[i].code === province) {
                        if (this_province.city && this_province.city.length > 0) {
                            for_cities = this_province.city;
                        }
                    }
                }
                //所有之前选中的市选中(判断依据，该市对应的区全部选中) 后置 待测试 待测试 待测试 测试没问题
                $('input[name=city]').each(function (index, item) {
                    var this_div = $(this)[0];
                    //判断该市下的区是否都在 s_areas 中，如果都在，则该市选中，否则不选中（判断是否在 s_cities 中是无效的，因为只要有一个区选中，该市值就存在）
                    var this_div_code = $(this_div).attr('id');
                    //获取该市对应的区列表
                    var this_city_checked = true;//该市选中状态
                    if (for_cities[index].area) {
                        //如果存在区则需要循环区来判断市是否选中
                        var this_areas = for_cities[index].area;
                        //判断当前市是否有区，如果有，循环判断是否都在 s_areas 中
                        var this_area_len = this_areas.length;
                        for (var k = 0; k < this_area_len; k++) {
                            //判断该区 code 是否在 s_areas 中，如果不在 this_checked = false
                            if (s_areas.indexOf(this_areas[k].code) === -1) {
                                this_city_checked = false;
                                break;
                            }
                        }
                    } else {
                        //不存在区的话只需要判断 s_cities 中是否有这个市
                        if (s_cities.indexOf(this_div_code) === -1) {
                            this_city_checked = false;
                        }
                    }
                    //最终执行该市是否选中
                    this_div.checked = this_city_checked;
                    //由于肯定不是全部选中市，所以不需要判断是否选中全选复选框
                });
            }
            form.render();
        });

        //市级全选复选框点击事件 先值后样式
        form.on('checkbox(city_all)', function (data) {
            var checked = false;
            if (data.elem.checked) {
                checked = true;
            }
            //如果全选选中，则需要循环添加所有市和区
            for (var i = 0; i < all_area_len; i++) {
                //循环查找这个 code 对应的省
                if (all_area[i].code === province && all_area[i].city && all_area[i].city.length) {
                    var this_cities = all_area[i].city;
                    var this_cities_len = this_cities.length;
                    //循环当前省的市，如果选中则存入 s_cities 中，否则从 s_cities 中删除
                    for (var j = 0; j < this_cities_len; j++) {
                        if (checked) {
                            s_cities.push(this_cities[j].code);
                        } else {
                            deleteSpecifiedElement(s_cities, this_cities[j].code);
                        }
                        if (this_cities[j].area && this_cities[j].area.length > 0) {
                            var this_areas = this_cities[j].area;
                            //判断当前市是否有区，如果有，如果选中则循环存入 s_areas 中，否则从 s_areas 中删除
                            var this_area_len = this_cities[j].area.length;
                            for (var k = 0; k < this_area_len; k++) {
                                if (data.elem.checked) {
                                    s_areas.push(this_areas[k].code);
                                } else {
                                    deleteSpecifiedElement(s_areas, this_areas[k].code);
                                }
                            }
                        }
                    }
                }
            }
            //循环所有市实行选中或取消选中
            $('input[name=city]').each(function (index, item) {
                item.checked = checked;
            });
            $('input[name=area]').each(function (index, item) {//所有打开的区选中
                item.checked = checked;
            });
            $('#' + province)[0].checked = checked;
            //如果选中，则对应的省选中，并判断是否所以省都选中，如果选中则省全选复选框选中
            var is_province_all_checked = false;
            if (checked) {
                //判断省数量是否和选中的省数量相同，相同则全选复选框选中
                if ($('input[name=province]').length === $('input[name=province]:checked').length) {
                    is_province_all_checked = true;
                }
            }
            $('input[name=province_all]')[0].checked = is_province_all_checked;
            form.render('checkbox');
        });

        //市级名称复选框点击事件 先值后样式
        form.on('checkbox(city)', function (data) {
            var this_city_code = $(this).attr('id');//当前点击的省 code
            //选中状态决定是否存入该市
            if (data.elem.checked) {
                s_cities.push(this_city_code);
            } else {
                deleteSpecifiedElement(s_cities, this_city_code);
            }
            //如果选中，则需要添加该市和循环添加该市所有区
            for (var i = 0; i < all_area_len; i++) {
                //循环查找这个 code 对应的省
                if (all_area[i].code === province && all_area[i].city && all_area[i].city.length) {
                    var this_cities = all_area[i].city;
                    var this_cities_len = this_cities.length;
                    //循环当前省的市
                    for (var j = 0; j < this_cities_len; j++) {
                        if (this_cities[j].code === this_city_code && this_cities[j].area && this_cities[j].area.length > 0) {
                            var this_areas = this_cities[j].area;
                            //判断当前市是否有区，如果有，如果选中则循环存入 s_areas 中，否则从 s_areas 中删除
                            var this_area_len = this_cities[j].area.length;
                            for (var k = 0; k < this_area_len; k++) {
                                if (data.elem.checked) {
                                    s_areas.push(this_areas[k].code);
                                } else {
                                    deleteSpecifiedElement(s_areas, this_areas[k].code);
                                }
                            }
                        }
                    }
                }
            }

            //判断选中或取消选中的市是否当前点击的市，如果是则对应的区状态同时需要更改
            if (city === this_city_code) {
                $('input[name=area]').each(function (index, item) {//所有打开的区选中
                    item.checked = data.elem.checked;
                });
            }
            var city_len = $("input[name=city]").length;//市数量
            var checked_city_len = $("input[name=city]:checked").length;//选中的市数量
            //判断市数量和选中的市数量，如果相同则市级全选复选框选中，同时省级选中，否则取消选中，如果选中的市数量为0
            var checked = false;
            if (city_len === checked_city_len) {
                checked = true;
            }
            $('input[name=city_all]')[0].checked = checked;
            $('#' + province)[0].checked = checked;//省级选中状态
            //判断省级选中数量，如果与省数量相同则省级全选按钮选中
            var province_len = $("input[name=province]").length;//省数量
            var checked_province_len = $("input[name=province]:checked").length;//选中的省数量
            var province_all_checked = false;
            if (province_len === checked_province_len) {
                province_all_checked = true;
            }
            $('input[name=province_all]')[0].checked = province_all_checked;
            form.render();
        });

        //市级标题点击事件 不需要存值，只写样式
        $(document).off('click', '.city_name').on('click', '.city_name', function () {
            //判断是否重复点击同一个市，如果是不做操作
            if ($(this).parent().find('input').attr('id') === city) {
                return;
            }
            //获取当前点击的市级值
            city = $(this).parent().find('input').attr('id');
            //将所有市样式还原，并为当前点击的市添加样式
            $('.city_name').css('background-color', 'white');
            $('.city_name').css('color', 'black');
            $(this).css('background-color', 'cornflowerblue');
            $(this).css('color', 'white');
            $('.area').empty();
            //获取级联数据，通过已经获取的数据，查询省对应的市列表
            for (var c = 0; c < cities.length; c++) {
                var code = cities[c].code;
                if (city === code) {
                    areas = cities[c].area;
                }
            }
            if (!areas || areas.length <= 0) {
                layer.msg('该市没有区', {icon: 1, time: 1000});
                return;
            }
            var a_len = areas.length;
            for (var i = 0; i < a_len; i++) {
                $('.area').append(getAreaDiv(areas[i]));
            }
            //判断当前点击的市是否为选中状态，如果是，添加区后同时将区选中
            $('input[name=area]').each(function (index, item) {//所有打开的区选中
                item.checked = $('#' + city)[0].checked;
            });
            form.render();
        });

        //区级名称复选框点击事件 完成 倒序写，还需要写市复选框点击事件和省复选框点击事件 先值后样式
        form.on('checkbox(area)', function (data) {
            var this_area_code = $(this).attr('id');//当前点击的区 code
            //判断该次点击是选中还是取消
            if (data.elem.checked) {
                s_areas.push(this_area_code);
                //如果市已存在就不用加了，否则添加，省略去重操作
                if (s_cities.indexOf(city) === -1) {
                    s_cities.push(city);
                }
            } else {
                deleteSpecifiedElement(s_areas, this_area_code);
                //判断是否没有区选中，如果有则不删除市，没有删除市
                if ($('input[name=area]:checked').length === 0) {
                    deleteSpecifiedElement(s_cities, city);
                }
            }

            var area_len = $("input[name=area]").length;//区数量
            var checked_area_len = $("input[name=area]:checked").length;//选中的区数量
            //判断区数量和选中的区数量，如果相同则市级复选框选中，否则取消选中，如果选中的区数量为0，则 region 需要删除该市，同时判断选中的市数量是否为0
            var city_checked = false;
            if (area_len === checked_area_len) {
                city_checked = true;
            }
            $('#' + city)[0].checked = city_checked;

            var city_len = $("input[name=city]").length;//市数量
            var checked_city_len = $("input[name=city]:checked").length;//选中的市数量
            //判断市数量和选中的市数量，如果相同则市级全选复选框选中，同时省级选中，否则取消选中，如果选中的市数量为0
            var checked = false;
            if (city_len === checked_city_len) {
                checked = true;
            }
            $('input[name=city_all]')[0].checked = checked;
            $('#' + province)[0].checked = checked;//省级选中状态
            //判断省级选中数量，如果与省数量相同则省级全选按钮选中
            var province_len = $("input[name=province]").length;//省数量
            var checked_province_len = $("input[name=province]:checked").length;//选中的省数量
            var province_all_checked = false;
            if (province_len === checked_province_len) {
                province_all_checked = true;
            }
            $('input[name=province_all]')[0].checked = province_all_checked;
            form.render();
        });

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var status = 0;
            if ($('input[name=status]:checked').val()) {
                status = 1;
            }
            var success_msg;
            var method = ajax_method;
            if (ajax_type === 'post') {
                success_msg = sucMsg.post;
            } else if (ajax_type === 'put') {
                method = ajax_method + '/' + operation_id;
                success_msg = sucMsg.put;
            }
            arr = {
                method: method,
                type: ajax_type,
                data: {
                    city_codes: s_cities.join(','),
                    area_codes: s_areas.join(','),
                    name: $('input[name=name]').val(),
                    status: status,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(success_msg);
                layer.close(open_index);
                add_edit_form[0].reset();//表单重置
                s_cities = [];
                s_areas = [];
                province = '';
                city = '';
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
                ajax_type = 'put';

                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get',
                };
                var res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    if (!group_data) {
                        initRegion();
                    }
                    $('.province_name').css('background-color', 'white');
                    $('.province_name').css('color', 'black');
                    $('.city').empty();
                    $('.area').empty();
                    /*diy设置开始*/
                    $("input[name=name]").val(res.data.name);
                    if (res.data.status == 1) {
                        $("input[name=status]").prop('checked', true);
                    } else {
                        $("input[name=status]").removeAttr('checked');
                    }
                    s_cities = res.data.city_codes.split(',');
                    s_areas = res.data.area_codes.split(',');
                    //判断是否有选全省，如果有，该省选中 1 循环所有省，2 每个省循环所有市，3 如果该省的市都在 s_cities 中，则该省选中
                    for (var i = 0; i < all_area_len; i++) {
                        var this_province = all_area[i];
                        if (this_province.city && this_province.city.length > 0) {
                            var this_cities = this_province.city;
                            var this_cities_len = this_cities.length;
                            //循环当前省的市，判断是否都在 s_cities 中
                            var save_cities_len = 0;
                            for (var j = 0; j < this_cities_len; j++) {
                                if (s_cities.indexOf(this_cities[j].code) !== -1) {
                                    save_cities_len++;
                                }
                            }
                            if (this_cities_len === save_cities_len) {//相等，则该省选中
                                $('#' + this_province.code)[0].checked = true;
                            }
                        }
                    }
                    /*diy设置结束*/

                    form.render();//设置完值需要刷新表单
                    open_index = layer.open({
                        type: 1,
                        title: '编辑',
                        content: add_edit_form,
                        shade: 0,
                        offset: '100px',
                        area: ['40vw', '35vw'],
                        cancel: function () {
                            s_cities = [];
                            s_areas = [];
                            province = '';//当前点击的省
                            city = '';//当前点击的市
                            add_edit_form.hide();
                        }
                    })
                }
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

        //以下基本不动
        // 默认加载列表
        arr = {
            name: 'render',//必传参
            elem: '#pageTable',//必传参
            method: ajax_method + '?key=' + saa_key,//必传参
            cols: [cols],//必传参
        };
        var render = getTableRender(arr);

        //搜索
        form.on('submit(find)', function (data) {
            render.reload({
                where: {
                    searchName: data.field.searchName
                },
                page: {
                    curr: 1
                }
            })
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

        //初始化级联数据
        function initRegion() {
            //获取级联数据
            arr = {
                method: 'addr',//获取所有的省市区
                type: 'get',
            };
            $('.region').empty();
            var res = getAjaxReturn(arr);
            if (!res.data || res.data.length <= 0) {
                // layer.msg('查询失败', {icon: 1, time: 2000});
                return;//没有查到数据，不返回任何信息
            }
            all_area = res.data;
            all_area_len = all_area.length;
            var p_len = all_area.length;
            $('.province').append('<div class="province_div">\n' +
                '                    <input type="checkbox" lay-skin="primary" lay-filter="province_all" name="province_all"/>\n' +
                '                    <span>全选</span>\n' +
                '                </div>');
            for (var i = 0; i < p_len; i++) {
                $('.province').append(getProvinceDiv(all_area[i]));
            }
            group_data = 1;
        }

    });
    exports('goods/areaGroup', {})
});

//获取省div  background-color:cornflowerblue;
function getProvinceDiv(info) {
    return '             <div class="province_div">\n' +
        '                    <input type="checkbox" lay-skin="primary" lay-filter="province" name="province" id="' + info.code + '"/>\n' +
        '                    <a href="javascript:void(0)" class="province_name">' + info.name + '</a>\n' +
        '                </div>';
}

//获取市div
function getCityDiv(info) {
    return '             <div class="city_div">\n' +
        '                    <input type="checkbox" lay-skin="primary" lay-filter="city" name="city" id="' + info.code + '"/>\n' +
        '                    <a href="javascript:void(0)" class="city_name">' + info.name + '</a>\n' +
        '                </div>';
}

//获取区div
function getAreaDiv(info) {
    return '             <div class="area_div">\n' +
        '                    <input type="checkbox" lay-skin="primary" lay-filter="area" name="area" id="' + info.code + '"/>\n' +
        '                    <span class="area_name">' + info.name + '</span>\n' +
        '                </div>';
}
