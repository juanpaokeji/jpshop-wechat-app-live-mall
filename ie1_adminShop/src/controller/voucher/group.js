/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/3/2
 * 团购
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'element', 'laydate', 'laypage'], function () {
        var element = layui.element;
        var $ = layui.$;
        var form = layui.form;
        var layDate = layui.laydate;
        var laypage = layui.laypage;
        var arr = {};//全局ajax请求参数
        var open_index;//定义弹出层，方便关闭
        var d_longitude = '119.216774';//默认经度
        var d_latitude = '34.615586';//默认纬度
        var d_tude_name = '德惠商务大厦';//默认经纬度标题，鼠标移入显示
        var operation_id;//数据表格操作需要用到单条 id
        var total_count = 0;//订单总数，分页用
        var pageLimit = 1;//查询使用到的每页显示数量，只需要初始化与 limit 相同即可
        var limit = 1;//列表中每页显示数量
        var tabPage = 1;//获取当前分页的页数
        var tab_id = 0;
        form.render();

        var placeSearch;  //构造地点查询类
        var infoWindow;//信息窗口
        var markers = [];//定义标注数组
        element.on('tab(tab)', function (e) {
            var index = tab_id = e.index;
            if (index === 0) {
                loadGroupConfig();
            } else if (index === 1) {
                loadGroupExamineList();
            } else if (index === 2) {
                loadGroupList();
            }
        });

        //加载基本配置页面，并获取配置
        function loadGroupConfig() {
            $('#groupConfig').load('./src/views/voucher/groupConfig.html', function () {
                getGroupConfig();

                //选择开市时间 必须放这里，不然页面未加载完就执行是没有效果的
                layDate.render({
                    elem: '#open_time',
                    type: 'time',
                });
                //选择休市时间
                layDate.render({
                    elem: '#close_time',
                    type: 'time',
                });
                form.render();
            });
        }

        loadGroupConfig();//默认加载基本配置页面

        //加载团长审核页面，并获取团长列表
        function loadGroupExamineList() {
            $('#groupExamineList').load('./src/views/voucher/groupExamineList.html', function () {
                getGroupExamineList('');
                if (total_count > pageLimit) {
                    getPage();
                }
            });
        }

        //加载团长列表页面，并获取团长列表
        function loadGroupList() {
            $('#groupList').load('./src/views/voucher/groupList.html', function () {
                getGroupList('');
                if (total_count > pageLimit) {
                    getPage();
                }
            });
        }

//基本配置开始
        var config_method = 'merchantTuanConfig';
        var close_pic_url = '';//base64图片

        //加载图片至img 因为是后加载页面，原方法点击无效
        $(document).off('change', '#addImgPut').on('change', '#addImgPut', function () {
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    close_pic_url = e.target.result;
                    $("#image").attr("src", e.target.result);
                };
            }
            file = null;
        })

        //获取团购基本配置
        function getGroupConfig() {
            arr = {
                method: config_method,
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
            $('input[name=open_time]').val(res.data.open_time);
            $('input[name=close_time]').val(res.data.close_time);
            $("#image").attr("src", res.data.close_pic_url);
            close_pic_url = res.data.close_pic_url;
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
            $('input[name=min_withdraw_money]').val(parseInt(res.data.min_withdraw_money));
            $('input[name=withdraw_fee_ratio]').val(parseInt(res.data.withdraw_fee_ratio));
            $('input[name=commission_leader_ratio]').val(parseInt(res.data.commission_leader_ratio));
            $('input[name=commission_user_ratio]').val(parseInt(res.data.commission_user_ratio));
            $('input[name=commission_selfleader_ratio]').val(parseInt(res.data.commission_selfleader_ratio));
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
            var commission_user_ratio = $('input[name=commission_user_ratio]').val();
            if (commission_user_ratio === '' || isNaN(commission_user_ratio)) {
                layer.msg('推荐佣金请填写数字', {icon: 1, time: 2000});
                return;
            }
            var commission_selfleader_ratio = $('input[name=commission_selfleader_ratio]').val();
            if (commission_selfleader_ratio === '' || isNaN(commission_selfleader_ratio)) {
                layer.msg('自提点佣金请填写数字', {icon: 1, time: 2000});
                return;
            }
            arr = {
                method: config_method,
                type: 'post',
                data: {
                    is_open: $('#is_open').attr('class') === 'checkbox on' ? 1 : 0,
                    open_time: $('input[name=open_time]').val(),
                    close_time: $('input[name=close_time]').val(),
                    close_pic_url: close_pic_url,
                    is_express: $('#is_express').attr('class') === 'checkbox on' ? 1 : 0,
                    is_site: $('#is_site').attr('class') === 'checkbox on' ? 1 : 0,
                    is_tuan_express: $('#is_tuan_express').attr('class') === 'checkbox on' ? 1 : 0,
                    leader_name: $('input[name=leader_name]').val(),
                    leader_range: leader_range,
                    min_withdraw_money: min_withdraw_money,
                    withdraw_fee_ratio: withdraw_fee_ratio,
                    commission_leader_ratio: commission_leader_ratio,
                    commission_user_ratio: commission_user_ratio,
                    commission_selfleader_ratio: commission_selfleader_ratio,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('保存成功', {icon: 1, time: 2000});
            }
        });

//基本配置结束

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
                        if (tab_id === 1) {
                            var status = $('#status').val();
                            var time = $('#date-range0').val();
                            var searchName = $('input[name=searchName]').val();
                            var s_info = '';
                            if (status === '') {
                                s_info += 'type=3';
                            } else {
                                s_info += 'type=' + status;
                            }
                            if (time !== '') {
                                //判断时间范围的格式
                                var time_arr = time.split(' to ');
                                if (time_arr.length !== 2) {
                                    layer.msg('时间范围格式错误', {icon: 1, time: 2000});
                                    return;
                                }
                                s_info += '&time=' + time;
                            }
                            if (searchName !== '') {
                                s_info += '&searchName=' + searchName;
                            }
                            getGroupExamineList(s_info)
                        } else if (tab_id === 2) {

                        }
                    }
                }
            });
        }

        //团长审核和团长列表共用请求
        var group_method = 'merchantTuanUser';//新ajax需要的参数 method
//团长审核开始
        //获取团长审核列表
        function getGroupExamineList(s_info) {
            total_count = 0;
            $('.groupExamineList').empty().append('<tr>\n' +
                '                        <th>会员ID</th>\n' +
                '                        <th>头像+昵称</th>\n' +
                '                        <th>姓名+手机号</th>\n' +
                '                        <th>团长配送费</th>\n' +
                '                        <th>消费金额</th>\n' +
                '                        <th>推荐人姓名</th>\n' +
                '                        <th>审核人姓名</th>\n' +
                '                        <th>城市</th>\n' +
                '                        <th>小区名称</th>\n' +
                '                        <th>申请时间</th>\n' +
                '                        <th>审核状态</th>\n' +
                '                    </tr>');
            if (s_info === '') {
                s_info = 'type=3';
            }
            s_info += '&limit=' + pageLimit;
            s_info += '&page=' + tabPage;
            arr = {
                method: group_method,
                type: 'get',
                params: s_info,//type 3是待审核和失败 0待审核 1审核通过 2审核失败 不传 type 是全部
            };
            var res = getAjaxReturnKey(arr);
            if (!res || !res.data) {
                return false;
            }
            total_count = res.count;
            var data = res.data;
            var len = data.length;
            for (var i = 0; i < len; i++) {
                $('.groupExamineList').append(groupExamineDiv(data[i]));
            }
        }

        //搜索团长审核列表  点击搜索的时候，设置的 pageLimit tabPage 需要处理
        $(document).off('click', '.examine_search').on('click', '.examine_search', function () {
            tabPage = 1;
            var status = $('#status').val();
            var time = $('#date-range0').val();
            var searchName = $('input[name=searchName]').val();
            var s_info = '';
            if (status === '') {
                s_info += 'type=3';
            } else {
                s_info += 'type=' + status;
            }
            if (time !== '') {
                //判断时间范围的格式
                var time_arr = time.split(' to ');
                if (time_arr.length !== 2) {
                    layer.msg('时间范围格式错误', {icon: 1, time: 2000});
                    return;
                }
                s_info += '&time=' + time;
            }
            if (searchName !== '') {
                s_info += '&searchName=' + searchName;
            }
            getGroupExamineList(s_info);
            getPage();
        });

        //搜索时间清空
        $(document).off('click', '.empty_time').on('click', '.empty_time', function () {
            $('#date-range0').val('');
            $('#date-range1').val('');
        });

        //点击待审核按钮执行操作
        $(document).off('click', '.need_audit').on('click', '.need_audit', function () {
            var that = this;
            var id = $(that).attr('id');
            //打开新窗口，显示通过和不通过按钮
            layer.confirm('该团长是否通过审核？', {
                btn: ['通过', '不通过'] //可以无限个按钮
                , btnAlign: 'c'
                , btn1: function (index) {
                    //按钮 通过 的回调
                    layer.close(index);
                    arr = {
                        method: group_method + '/' + id,
                        type: 'put',
                        data: {
                            status: 1,
                        },
                    };
                    var res = getAjaxReturnKey(arr);
                    if (!res) {
                        return false;
                    }
                    layer.msg(res.message, {icon: 1, time: 2000});
                    $(that).parent().parent().parent().remove();
                }
                , btn2: function () {
                    //按钮 不通过 的回调 需要修改审核状态
                    arr = {
                        method: group_method + '/' + id,
                        type: 'put',
                        data: {
                            status: 0,
                        },
                    };
                    var res = getAjaxReturnKey(arr);
                    if (!res) {
                        return false;
                    }
                    layer.msg(res.message, {icon: 1, time: 2000});
                    $(that).parent().removeClass('btn-green').addClass('btn-red').html('审核未通过');
                }
            });
        })

//团长审核结束

//团长列表开始
        //页面不同属性
        var map;
        var is_area = 1;//是否有区的判断依据，点击市后如果没有区，则该值为 0，保存时候用来判断
        var first_city = 0;//获取的第一个市，用于获取区
        function getGroupList(s_info) {
            $('.groupList').empty().append('<tr>\n' +
                '                        <th>头像</th>\n' +
                '                        <th>昵称</th>\n' +
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
                method: group_method,
                type: 'get',
                params: 'type=1' + s_info,
            };
            var res = getAjaxReturnKey(arr);
            if (!res) {
                return false;
            }

            var data = res.data;
            var len = data.length;
            for (var i = 0; i < len; i++) {
                $('.groupList').append(groupListDiv(data[i]));
            }
        }

        //搜索团长列表
        $(document).off('click', '.list_search').on('click', '.list_search', function () {
            var time = $('#date-range1').val();
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

        //点击编辑事件
        $(document).off('click', '.edit').on('click', '.edit', function () {
            var id = $(this).attr('id');
            operation_id = id;
            $('.groupInfo').load('./src/views/voucher/groupInfo.html', function () {
                arr = {
                    method: group_method + '/' + id,
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
                $('.phone').html(data.phone);
                $('input[name=realname]').val(data.realname);
                //设置省市区级联 获取省级，开始做级联
                getGroups(1, 0, data.province_code);
                getGroups(2, data.province_code, data.city_code);
                getGroups(3, data.city_code, data.area_code);
                $('input[name=area_name]').val(data.area_name);
                $('input[name=addr]').val(data.addr);
                $('input[name=longitude]').val(data.longitude);
                $('input[name=latitude]').val(data.latitude);
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
                    realname: $('input[name=realname]').val(),
                    province_code: $('#province').val(),
                    city_code: $('#city').val(),
                    area_code: $('#area').val(),
                    area_name: $('input[name=area_name]').val(),
                    addr: $('input[name=addr]').val(),
                    longitude: $('input[name=longitude]').val(),
                    latitude: $('input[name=latitude]').val(),
                    tuan_express_fee: $('input[name=tuan_express_fee]').val(),
                    is_tuan_express: $('#is_tuan_express_info').attr('class') === 'btn-switch on' ? 1 : 0,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('保存成功', {icon: 1, time: 2000});
                layer.close(open_index);
                $('.groupInfo').hide();
                loadGroupList();
            }
        })
//团长信息结束

    });
    exports('voucher/group', {})
});

//团长审核div
function groupExamineDiv(info) {
    var status = info.status;
    if (status === '0') {
        status = '<span class="btn btn-green"><a href="javascript:void(0)" class="need_audit" id="' + info.id + '">待审核</a></span>';
    } else if (status === '2') {
        status = '<span class="btn btn-red">审核未通过</span>';
    } else {
        status = '<span class="btn btn-red">类型错误</span>';
    }
    var area = info.province + info.city + info.area;
    return '                   <tr>\n' +
        '                            <td>' + info.id + '</td>\n' +
        '                            <td>\n' +
        '                                <img src="' + info.avatar + '" alt="" class="face">\n' +
        '                                <div class="name">' + info.nickname + '</div>\n' +
        '                            </td>\n' +
        '                            <td>' + info.realname + '<br>' + info.phone + '</td>\n' +
        '                            <td>' + parseInt(info.tuan_express_fee) + '</td>\n' +
        '                            <td>' + parseInt(info.money) + '</td>\n' +
        '                            <td>未设置</td>\n' +
        '                            <td>' + info.audit_name + '</td>\n' +
        '                            <td>' + area + '</td>\n' +
        '                            <td>' + info.area_name + '</td>\n' +
        '                            <td>' + info.format_create_time + '</td>\n' +
        '                            <td>' + status + '</td>\n' +
        // '                            <td><a href="" class="green">查看详情</a></td>\n' +
        '                        </tr>';
}

//团长列表div
function groupListDiv(info) {
    return '               <tr>\n' +
        '                        <td><img src="' + info.avatar + '" alt="" class="face"></td>\n' +
        '                        <td>' + info.nickname + '</td>\n' +
        '                        <td class="tLeft">' + info.realname + '<br>' + info.phone + '</td>\n' +
        '                        <td>' + info.province + '<br/>' + info.city + '<br/>' + info.area + '</td>\n' +
        '                        <td class="tLeft">小区：' + info.area_name + '<br>自提点：' + info.addr + '</td>\n' +
        '                        <td>' + parseInt(info.tuan_express_fee) + '</td>\n' +
        '                        <td><i class="blue">未设置</i></td>\n' +
        '                        <td><i class="blue">未设置</i></td>\n' +
        '                        <td class="tLeft">\n' +
        '                            <p>总消费佣金：未设置</p>\n' +
        '                            <p>待结算佣金：未设置</p>\n' +
        '                            <p>未提现佣金：未设置</p>\n' +
        '                        </td>\n' +
        '                        <td><a href="" class="btn btn-green">正常</a><a href="" class="btn btn-green">服务中</a></td>\n' +
        '                        <td>\n' +
        '                            <a id="' + info.id + '" href="javascript:void(0)" class="green edit">编辑</a>|\n' +
        '                            <a href="" class="green">添加核销员</a>|\n' +
        '                            <a href="" class="green">查看核销员</a>\n' +
        '                        </td>\n' +
        '                    </tr>';
}
