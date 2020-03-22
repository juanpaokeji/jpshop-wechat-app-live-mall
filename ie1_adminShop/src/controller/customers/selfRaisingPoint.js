/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/3/2
 * 团购
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'element', 'laydate', 'laypage'], function () {
        var table = layui.table;
        var element = layui.element;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;
        var layDate = layui.laydate;
        var laypage = layui.laypage;
        var arr = {};//全局ajax请求参数
        var open_index;//定义弹出层，方便关闭
        var d_longitude = '119.216774';//默认经度
        var d_latitude = '34.615586';//默认纬度
        var d_tude_name = '德惠商务大厦';//默认经纬度标题，鼠标移入显示
        var operation_id;//数据表格操作需要用到单条 id
        var total_count = 0;//订单总数，分页用
        var pageLimit = 10;//查询使用到的每页显示数量，只需要初始化与 limit 相同即可
        var limit = 10;//列表中每页显示数量
        var tabPage = 1;//获取当前分页的页数
        var tab_id = 0;
        form.render();

        //搜索时间清空
        $(document).off('click', '.empty_time').on('click', '.empty_time', function () {
            $('#date-range0').val('');
        });


        var placeSearch;  //构造地点查询类
        var infoWindow;//信息窗口
        var markers = [];//定义标注数组

        //加载团长列表页面，并获取团长列表
        getGroupList('');
        getPage();

        //默认列表分页
        function getPage() {
            laypage.render({
                elem: 'page' //注意，这里的 page 是 ID，不用加 # 号
                , count: total_count //数据总数，从服务端得到
                , prev: '<'
                , next: '>'
                , limit: pageLimit
                , limits: [limit, limit * 2, limit * 3]
                , layout: ['prev', 'page', 'next', 'refresh', 'skip', 'limit']
                , jump: function (obj, first) {
                    pageLimit = obj.limit;
                    tabPage = obj.curr;
                    //首次不执行
                    if (!first) {
                        getGroupList('');
                    }
                }
            });
        }

        //团长列表开始
        //页面不同属性
        var map;
        var is_area = 1;//是否有区的判断依据，点击市后如果没有区，则该值为 0，保存时候用来判断
        var first_city = 0;//获取的第一个市，用于获取区
        function getGroupList(s_info) {
            $('.groupList').empty().append('<tr>\n' +
                '                        <th>头像</th>\n' +
                '                        <th style="width: 138px">昵称</th>\n' +
                '                        <th>团长信息</th>\n' +
                '                        <th>所属城市</th>\n' +
                '                        <th>自提点信息</th>\n' +
                '                        <th>团长配送费</th>\n' +
                '                        <th>总订单数</th>\n' +
                '                        <th>旗下团员</th>\n' +
                '                        <th>佣金</th>\n' +
                '                        <th>状态</th>\n' +
                '                        <th>操作</th>\n' +
                '                    </tr>');
            arr = {
                method: 'merchantTuanUser',
                type: 'get',
                params: 'type=1&is_self=1' + s_info,
                data: {limit: pageLimit, page: tabPage}
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var data = res.data;
                var len = data.length;
                total_count = res.count;
                for (var i = 0; i < len; i++) {
                    $('.groupList').append(groupListDiv(data[i]));
                }
            }
        }

        //搜索团长列表
        $(document).off('click', '.list_search').on('click', '.list_search', function () {
            var time = $('#date-range0').val();
            var listSearchName = $('input[name=listSearchName]').val();
            var search_city = $('input[name=search_city]').val();
            var s_info = '';
            if (time !== '') {
                //判断时间范围的格式
                var time_arr = time.split(' to ');
                if (time_arr.length !== 2) {
                    layer.msg('时间范围格式错误', {icon: 1, time: 2000});
                    return;
                }
                s_info += '&audit_time=' + time;
            }
            if (listSearchName !== '') {
                s_info += '&searchName=' + listSearchName;
            }
            if (search_city !== '') {
                s_info += '&city=' + search_city;
            }
            getGroupList(s_info);
        });

        //点击绑定商品事件
        var goods_open_index;
        var goods_form = $('#goods_form');
        var save_goods_user_id;
        var goods_member_arr = [];
        $(document).off('click', '.bind').on('click', '.bind', function () {
            save_goods_user_id = $(this).attr('data');
            var cols = [//加载的表格
                {type: 'checkbox'},
                {
                    field: 'pic_urls', title: '图片', templet: function (d) {
                        var pic_url_one = d.pic_urls.split(',')[0];
                        return '<img src="' + pic_url_one + '">';
                    }
                },
                {field: 'name', title: '商品名称'},
                {field: 'price', title: '价格'}
            ];

            table.render({
                elem: '#pageTableGoods',
                url: baseUrl + '/merchantGoods?key=' + saa_key,
                page: true, //开启分页
                limit: 10,
                limits: [10, 20, 30],
                loading: true,
                cols: [cols],
                response: {
                    statusName: 'status', //数据状态的字段名称，默认：code
                    statusCode: "200", //成功的状态码，默认：0
                    dataName: 'data' //数据列表的字段名称，默认：data
                },
                headers: headers,
                done: function (res) {
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
                    goods_member_arr = res.data;
                    //加载完商品列表后，需要获取已选中的商品 ids
                    arr = {
                        method: 'merchantLeaderGoods/' + save_goods_user_id,
                        type: 'get'
                    };
                    var ajax_res = getAjaxReturnKey(arr);
                    save_goods_list_ids = ajax_res.data.goods_ids;

                    //获取商品列表对应的 tr
                    var tr = document.getElementsByTagName('tbody')[1].getElementsByTagName('tr');
                    //设置该 checkbox 选中
                    for (var i = 0; i < tr.length; i++) {
                        var id = res.data[i].id;//当前标签对应的 id
                        if (save_goods_list_ids.indexOf(id) > -1) {
                            //表示在数组内，设置该样式
                            tr[i].getElementsByTagName('td')[0].getElementsByTagName('div')[1].className = 'layui-unselect layui-form-checkbox layui-form-checked';
                            tr[i].getElementsByTagName('td')[0].getElementsByTagName('input')[0].checked = true;
                        }
                    }

                    goods_open_index = layer.open({
                        type: 1,
                        title: '添加商品',
                        content: goods_form,
                        shade: 0,
                        offset: '100px',
                        area: ['600px', '600px'],
                        cancel: function () {
                            goods_form.hide();
                        }
                    });
                }
            });
        });

        var save_goods_list_ids = [];
        //商品列表点击复选框事件
        table.on('checkbox(pageTableGoods)', function (obj) {
            if (obj.type == 'all') {
                //点击全选执行
                if (obj.checked == true) {
                    for (var i = 0; i < goods_member_arr.length; i++) {
                        save_goods_list_ids.push(goods_member_arr[i]['id']);
                    }
                }
            } else {
                //选择单条执行
                if (obj.checked == true) {
                    //将该选择数据存入数组
                    save_goods_list_ids.push(obj.data.id);
                } else {
                    //删除该选择元素
                    var arrIndex = save_goods_list_ids.indexOf(obj.data.id);
                    if (arrIndex > -1) {
                        save_goods_list_ids.splice(arrIndex, 1);
                    }
                }
            }
        });

        //商品列表保存执行事件
        form.on('submit(goods_save)', function () {
            if (save_goods_list_ids.length <= 0) {
                layer.msg('未选择商品', {icon: 1, time: 2000});
                return;
            }
            duplicateRemoval(save_goods_list_ids);
            arr = {
                method: 'merchantLeaderGoods/' + save_goods_user_id,
                type: 'put',
                data: {
                    goods_ids: save_goods_list_ids
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                save_goods_list_ids = [];
                layer.msg('保存成功', {icon: 1, time: 2000}, function () {
                    location.reload();
                });
            }
        });

        //点击解绑会员事件
        $(document).off('click', '.untying').on('click', '.untying', function () {
            var id = $(this).attr('data');
            arr = {
                method: 'merchantTuanUserUntying/' + id,
                type: 'delete',
            };
            var res = getAjaxReturnKey(arr);
            if (!res) {
                return false;
            }
            layer.msg('解绑成功', {icon: 1, time: 2000});
            location.reload();
        });

        //点击编辑事件
        $(document).off('click', '.edit').on('click', '.edit', function () {
            var id = $(this).attr('data');
            operation_id = id;
            $('.groupInfo').load('./src/views/voucher/groupInfo.html', function () {
                arr = {
                    method: 'merchantTuanUser/' + id,
                    type: 'get',
                };
                var res = getAjaxReturnKey(arr);
                if (!res || !res.data) {
                    // layer.msg('查询失败', {icon: 1, time: 2000});
                    return false;
                }
                //设置页面值
                var data = res.data;
                $('#avatar').attr('src', data.avatar);
                $('.nickname').html(data.nickname);
                $('.sex').html(data.sex);
                $('input[name=phone]').val(data.phone);
                $('input[name=realname]').val(data.realname);
                //设置省市区级联 获取省级，开始做级联
                getGroups(1, 0, data.province_code);
                getGroups(2, data.province_code, data.city_code);
                getGroups(3, data.city_code, data.area_code);
                //获取仓库列表
                getWarehouse(data.warehouse_id);
                $('input[name=area_name]').val(data.area_name);
                $('input[name=addr]').val(data.addr);
                $('input[name=longitude]').val(data.longitude);
                $('input[name=latitude]').val(data.latitude);
                if (data.is_self == 1) {
                    $("#is_self").addClass('on');
                } else {
                    $("#is_self").removeClass('on');
                }
                if (data.is_tuan_express == 1) {
                    $("#is_tuan_express_info").addClass('on');
                } else {
                    $("#is_tuan_express_info").removeClass('on');
                }
                $('#create_time').html(data.format_create_time);
                $('input[name=tuan_express_fee]').val(parseInt(data.tuan_express_fee));
                var longitude = d_longitude;
                var latitude = d_latitude;
                var area_name = d_tude_name;
                if (data.longitude && data.latitude) {
                    longitude = data.longitude;
                    latitude = data.latitude;
                    area_name = data.area_name;
                }
                open_index = layer.open({
                    type: 1,
                    title: '编辑',
                    content: $('.groupInfo'),
                    shade: 0,
                    offset: '100px',
                    area: ['45vw', '35vw'],
                    cancel: function () {
                        $('.groupInfo').hide();
                    },
                    success: function () {
                        markers = [];//定义标注数组
                        //初始化地图插件
                        map = new AMap.Map('map', {
                            center: [longitude, latitude],
                            zoom: 18
                        });

                        //在地图上创建标注点
                        marker = new AMap.Marker({
                            icon: "http://webapi.amap.com/theme/v1.3/markers/n/mark_b.png"
                        });
                        marker.setPosition(new AMap.LngLat(longitude, latitude));
                        marker.setMap(map);
                        marker.setLabel({//label默认蓝框白底左上角显示，样式className为：amap-marker-label
                            offset: new AMap.Pixel(3, 0),//修改label相对于maker的位置
                        });
                        marker.name = area_name;
                        marker.lng = longitude;
                        marker.lat = latitude;
                        marker.on('click', markerClick);
                        markers.push(marker);


                        //地图点击事件
                        map.on('click', function (e) {
                            //设置页面经纬度坐标
                            $('input[name=longitude]').val(e.lnglat.getLng());//经度
                            $('input[name=latitude]').val(e.lnglat.getLat());//纬度
                            var geocoder = new AMap.Geocoder({
                                // city 指定进行编码查询的城市，支持传入城市名、adcode 和 citycode
                                // city: '010'
                            })

                            //通过当前点击坐标查询地址名称并显示到页面
                            var lnglat = [e.lnglat.getLng(), e.lnglat.getLat()];
                            geocoder.getAddress(lnglat, function (status, result) {
                                if (status === 'complete' && result.info === 'OK') {
                                    // result为对应的地理位置详细信息  设置页面值
                                    $('input[name=addr]').val(result.regeocode.formattedAddress);
                                }
                            })
                        })

                        placeSearch = new AMap.PlaceSearch();  //构造地点查询类
                        infoWindow = new AMap.InfoWindow({offset: new AMap.Pixel(0, -30)});//信息窗口
                    }
                });
                $('input[name=pay_info_ali]').val(data.ali);
                $('input[name=pay_info_pay_name]').val(data.pay_name);
                $('input[name=pay_info_pay_realname]').val(data.pay_realname);
                $('input[name=pay_info_pay_number]').val(data.pay_number);
                form.render();
            });
        });

        $(document).off('change', '.region').on('change', '.region', function () {
            var select_code = this.value;//当前选中的下拉值，用来请求获取低一层级
            var type = $(this).attr('id');
            if (type === 'province') {
                //选择省事件，清空市区，循环获取市
                $('#city').empty();
                $('#area').empty();
                getGroups(2, select_code);
                getGroups(3, first_city);
            } else if (type === 'city') {
                $('#area').empty();
                //选择市事件，清空区，循环获取区
                getGroups(3, select_code);
            }
        });

        //地图搜索按钮执行事件
        $(document).off('click', '.search_map').on('click', '.search_map', function () {
            doSearch();
        });

        var a_w_o_open_index;
        var s_w_o_open_index;
        var add_write_off_form = $('#add_write_off_form');
        var show_write_off_form = $('#show_write_off_form');
        //添加核销员按钮执行事件
        $(document).off('click', '.add_write_off').on('click', '.add_write_off', function () {
            var id = $(this).attr('data');
            var cols = [//加载的表格
                {type: 'checkbox'},
                {field: 'avatar', title: '图片', templet: '#imgTpl'},
                {field: 'nickname', title: '团员姓名'},
                {field: 'phone', title: '团员手机号'},
            ];
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTable',//需要加载的 table 表格对应的 id
                method: 'merchantLeagueMember/' + id + '?key=' + saa_key + '&type=0',//请求的 api 接口方法和可能携带的参数 key
                cols: [cols],//加载的表格字段
            };
            getTableRender(arr);//变量名对应 arr 中的 name
            a_w_o_open_index = layer.open({
                type: 1,
                title: '添加核销员',
                content: add_write_off_form,
                shade: 0,
                offset: '100px',
                area: ['600px', '600px'],
                cancel: function () {
                    add_write_off_form.hide();
                }
            });
        });

        var save_goods_ids = [];
        //团员列表点击复选框事件
        table.on('checkbox(pageTable)', function (obj) {
            var member_arr = result.data;
            if (obj.type == 'all') {
                //点击全选执行
                save_goods_ids = [];
                if (obj.checked == true) {
                    for (var i = 0; i < member_arr.length; i++) {
                        save_goods_ids.push(member_arr[i]['id']);
                    }
                }
            } else {
                //选择单条执行
                if (obj.checked == true) {
                    //将该选择数据存入数组
                    save_goods_ids.push(obj.data.id);
                } else {
                    //删除该选择元素
                    var arrIndex = save_goods_ids.indexOf(obj.data.id);
                    if (arrIndex > -1) {
                        save_goods_ids.splice(arrIndex, 1);
                    }
                }
            }
        });

        //团员列表保存执行事件
        form.on('submit(add_write_off_save)', function () {
            if (save_goods_ids.length <= 0) {
                layer.msg('未选择团员', {icon: 1, time: 2000});
                return;
            }
            arr = {
                method: 'merchantLeagueMember',
                type: 'put',
                data: {
                    id: save_goods_ids,
                    is_verify: 1
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.close(a_w_o_open_index);
                add_write_off_form.hide();
            }
        });

        var pageTableShow;
        //查看核销员按钮执行事件
        $(document).off('click', '.show_write_off').on('click', '.show_write_off', function () {
            var id = $(this).attr('data');
            var cols = [//加载的表格
                {field: 'avatar', title: '图片', templet: '#imgTpl'},
                {field: 'nickname', title: '团员姓名'},
                {field: 'phone', title: '团员手机号'},
                {field: 'operations', title: '操作', toolbar: '#operations'}
            ];
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTableShow',//需要加载的 table 表格对应的 id
                method: 'merchantLeagueMember/' + id + '?key=' + saa_key + '&type=1',//请求的 api 接口方法和可能携带的参数 key
                cols: [cols],//加载的表格字段
            };
            pageTableShow = getTableRender(arr);//变量名对应 arr 中的 name
            s_w_o_open_index = layer.open({
                type: 1,
                title: '查看核销员',
                content: show_write_off_form,
                shade: 0,
                offset: '100px',
                area: ['600px', '600px'],
                cancel: function () {
                    show_write_off_form.hide();
                }
            });
        });


        //表格操作点击事件
        table.on('tool(pageTableShow)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            if (layEvent === 'cancel') {
                layer.confirm('确定撤销该团员核销员身份吗?', function (index) {
                    layer.close(index);
                    arr = {
                        method: 'merchantLeagueMember',
                        type: 'put',
                        data: {
                            id: [data.id],
                            is_verify: 0
                        }
                    };
                    var res = getAjaxReturnKey(arr);
                    if (res) {
                        pageTableShow.reload();
                    }
                })
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        //旗下团员数量按钮执行事件
        var count_form = $('#count_form');
        $(document).off('click', '.count').on('click', '.count', function () {
            var id = $(this).attr('data');
            var cols = [//加载的表格
                {field: 'avatar', title: '图片', templet: '#imgTpl'},
                {field: 'nickname', title: '团员姓名'},
                {field: 'phone', title: '团员手机号'},
            ];
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTableCount',//需要加载的 table 表格对应的 id
                method: 'merchantLeagueMember/' + id + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
                cols: [cols],//加载的表格字段
            };
            getTableRender(arr);//变量名对应 arr 中的 name
            layer.open({
                type: 1,
                title: '旗下团员',
                content: count_form,
                shade: 0,
                offset: '100px',
                area: ['600px', '600px'],
                cancel: function () {
                    count_form.hide();
                }
            });
        });
        //团长列表结束

        //团长信息开始
        //获取省市区级联 type 1 省 2 市 3 区，name option需要添加的class，group_id 需要默认选中的值
        function getGroups(type, parent_id, group_id) {
            var this_method = 'address';
            if (type !== 1) {
                this_method += '?keywords=' + parent_id;
            }
            var class_name = '';
            if (type === 1) {
                class_name = 'province';
            } else if (type === 2) {
                class_name = 'city';
            } else if (type === 3) {
                class_name = 'area';
            }
            arr = {
                method: this_method,
                type: 'get',
            };
            var res = getAjaxReturn(arr);
            is_area = 1;
            if (res && res.data && res.data.districts && res.data.districts[0].districts) {
                var districts = res.data.districts[0].districts;
                var len = districts.length;
                var name;
                var code;
                for (var a = 0; a < len; a++) {
                    if (districts[a].level !== 'street') {
                        name = districts[a].name;
                        code = districts[a].adcode;
                        if (a === 0) {
                            first_city = code;
                        }
                        if (group_id) {
                            var selected = '';
                            if (group_id === code) {
                                selected = ' selected ';
                            }
                            $('select[name=' + class_name + ']').append("<option value=" + code + selected + ">" + name + "</option>");
                        } else {
                            $('select[name=' + class_name + ']').append("<option value=" + code + ">" + name + "</option>");
                        }
                    } else {
                        is_area = 0;
                        break;
                    }
                }
                form.render();
            } else {
                if (type === 3) {
                    is_area = 0;
                }
            }
        }

        //获取仓库列表
        function getWarehouse(group_id) {
            arr = {
                method: 'merchantWarehouse',
                type: 'get',
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var name;
                var id;
                $('select[name=warehouse_id]').append("<option value='0'>未选择</option>");
                for (var a = 0; a < res.data.length; a++) {
                    name = res.data[a].name;
                    id = res.data[a].id;
                    if (group_id) {
                        var selected = '';
                        if (group_id === id) {
                            selected = ' selected ';
                        }
                        $('select[name=warehouse_id]').append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        $('select[name=warehouse_id]').append("<option value=" + id + ">" + name + "</option>");
                    }
                    form.render();
                }
            }
        }

        //地址查询
        function doSearch() {
            map.remove(markers);//查询前先移除所有标注
            var address = document.getElementsByName("addr")[0].value;
            placeSearch.search(address, function (status, result) {
                if (status === 'complete' && result.info === 'OK') {
                    //               alert(JSON.stringify(result));
                    var poiArr = result.poiList.pois;
                    var str = "<ul>";
                    for (var i = 0; i < poiArr.length; i++) {
                        //在地图上创建标注点
                        marker = new AMap.Marker({
                            icon: "http://webapi.amap.com/theme/v1.3/markers/n/mark_b.png"
                        });
                        marker.setPosition(new AMap.LngLat(poiArr[i].location.lng, poiArr[i].location.lat));
                        marker.setMap(map);
                        marker.setLabel({//label默认蓝框白底左上角显示，样式className为：amap-marker-label
                            offset: new AMap.Pixel(3, 0),//修改label相对于maker的位置
                            content: String.fromCharCode(65 + i)
                        });
                        marker.name = poiArr[i].name;
                        marker.content = poiArr[i].name + "<br/>" + poiArr[i].address;
                        marker.lng = poiArr[i].location.lng;
                        marker.lat = poiArr[i].location.lat;
                        marker.on('click', markerClick);
                        // marker.emit('click', {target:marker});
                        markers.push(marker);

                        str += '<li>';
                        str += '<div class="res-data">';
                        str += '<div class="left res-marker">';
                        str += '<span>' + String.fromCharCode(65 + i) + '</span>';
                        str += '</div>';
                        str += '<div class="left res-address">';
                        str += '<div class="title">' + poiArr[i].name + '</div>';
                        str += '<div>地址：<span class="rout">' + poiArr[i].address + '</span></div>';
                        str += '<div>坐标：<span class="point">' + poiArr[i].location.lng + "," + poiArr[i].location.lat + '</span></div>';
                        str += '</div>';
                        str += '<div class="clearfix"></div>';
                        str += '</div>';
                        str += '</li>';
                    }
                    str += '</ul>';
                    $("#result").html(str);
                    $("#s-point").text(poiArr[0].location.lng + "," + poiArr[0].location.lat);
                    //设置地图显示级别及中心点
                    map.setZoomAndCenter(18, new AMap.LngLat(poiArr[0].location.lng, poiArr[0].location.lat));
                    //获取查询城市信息
                    map.getCity(function (res) {
                        $("#s-city").text(res.province + res.city);
                    });

                }
            });
        }

        //点击标注  显示信息窗口及内容
        function markerClick(e) {
            infoWindow.setContent(e.target.content);
            infoWindow.open(map, e.target.getPosition());
            $('input[name=addr]').val(e.target.name);
            $('input[name=longitude]').val(e.target.lng);
            $('input[name=latitude]').val(e.target.lat);
        }

        //保存团长资料信息
        $(document).off('click', '.save').on('click', '.save', function () {
            arr = {
                method: 'merchantTuanUsers/' + operation_id,
                type: 'put',
                data: {
                    phone: $('input[name=phone]').val(),
                    realname: $('input[name=realname]').val(),
                    province_code: $('#province').val(),
                    city_code: $('#city').val(),
                    area_code: $('#area').val(),
                    area_name: $('input[name=area_name]').val(),
                    addr: $('input[name=addr]').val(),
                    longitude: $('input[name=longitude]').val(),
                    latitude: $('input[name=latitude]').val(),
                    warehouse_id: $('select[name=warehouse_id]').val(),
                    tuan_express_fee: $('input[name=tuan_express_fee]').val(),
                    is_self: $('#is_self').attr('class') === 'btn-switch on' ? 1 : 0,
                    is_tuan_express: $('#is_tuan_express_info').attr('class') === 'btn-switch on' ? 1 : 0,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('保存成功', {icon: 1, time: 2000});
                layer.close(open_index);
                $('.groupInfo').hide();
                getGroupList('');
                getPage();
            }
        })

        //点击状态事件
        var state_open_index;
        var state_form = $('#state_form');
        $(document).off('click', '.now_state').on('click', '.now_state', function () {
            var id = $(this).attr('data');
            var state = $(this).attr('state');
            operation_id = id;
            $("input[name='state'][value='" + state + "']").prop("checked", true);
            form.render();//设置完值需要刷新表单

            state_open_index = layer.open({
                type: 1,
                title: '修改团长状态',
                content: state_form,
                shade: 0.1,
                offset: '100px',
                area: ['500px', '150px'],
                cancel: function () {
                    state_form.hide();
                }
            });
        })

        //状态修改
        form.on('submit(state_sub)', function (data) {//查询
            arr = {
                method: 'merchantTuanUsers/' + operation_id,
                type: 'put',
                data: {
                    state: data.field.state,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('修改成功', {icon: 1, time: 2000}, function () {
                    location.reload();
                });
                // $('.now_state').each(function () {
                //     if (operation_id === $(this).attr('data')) {
                //         var now_state_str = '正常';
                //         if (data.field.state === '1') {
                //             now_state_str = '冻结';
                //         } else if (data.field.state === '2') {
                //             now_state_str = '关闭';
                //         } else {
                //             now_state_str = '数据错误';
                //         }
                //         $(this).html(now_state_str);
                //     }
                // });
                // layer.close(state_open_index);
                // state_form.hide();
            }
        });

    });
    exports('customers/selfRaisingPoint', {})
});

//团长列表div
function groupListDiv(info) {
    var state = info.state;
    var state_a = '';
    //0 正常 1 冻结 2 关闭
    if (state === '0') {
        state_a = '正常';
    } else if (state === '1') {
        state_a = '冻结';
    } else if (state === '2') {
        state_a = '关闭';
    } else {
        state_a = '数据错误'
    }
    var sum_money = info.sum_money ? info.sum_money : 0;//总消费佣金
    var on_money = info.on_money ? info.on_money : 0;//待结算佣金
    var user_balance = info.user_balance ? info.user_balance : 0;//待提现金额
    return '               <tr>\n' +
        '                        <td><img src="' + (info.avatar ? info.avatar : '') + '" alt="" class="face"></td>\n' +
        '                        <td>' + (info.nickname ? info.nickname : '无') + '</td>\n' +
        '                        <td class="tLeft">' + info.realname + '<br>' + (info.phone ? info.phone : '无') + '</td>\n' +
        '                        <td>' + info.province + '<br/>' + info.city + '<br/>' + info.area + '</td>\n' +
        '                        <td class="tLeft">小区：' + info.area_name + '<br>自提点：' + info.addr + '</td>\n' +
        '                        <td>' + parseInt(info.tuan_express_fee) + '</td>\n' +
        '                        <td><i class="blue">' + info.self_number + '</i></td>\n' +
        '                        <td><i class="blue"><a data="' + info.uid + '" href="javascript:void(0)" class="green count">' + (info.count ? info.count : 0) + '</a></i></td>\n' +
        '                        <td class="tLeft">\n' +
        '                            <p>总消费佣金：' + sum_money + '</p>\n' +
        '                            <p>待结算佣金：' + on_money + '</p>\n' +
        '                            <p>待提现金额：' + user_balance + '</p>\n' +
        '                        </td>\n' +
        '                        <td><a data="' + info.id + '" state="' + info.state + '" href="javascript:void(0)" class="btn btn-green now_state">' + state_a + '</a></td>\n' +
        '                        <td>\n' +
        '                            <a data="' + info.id + '" href="javascript:void(0)" class="green bind">绑定商品</a>|\n' +
        '                            <a data="' + info.uid + '" href="javascript:void(0)" class="green untying">解绑会员</a>|\n' +
        '                            <a data="' + info.id + '" href="javascript:void(0)" class="green edit">编辑</a>|\n' +
        '                            <a data="' + info.uid + '" href="javascript:void(0)" class="green add_write_off">添加核销员</a>|\n' +
        '                            <a data="' + info.uid + '" href="javascript:void(0)" class="green show_write_off">查看核销员</a>\n' +
        '                        </td>\n' +
        '                    </tr>';
}
