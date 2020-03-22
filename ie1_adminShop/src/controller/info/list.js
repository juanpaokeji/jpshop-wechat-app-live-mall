/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/8/10  一直在更新，时间随时修改
 * js model
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var successMsg;//成功提示，仅用于判断新增编辑
        // var limit = 10;//列表中每页显示数量
        // var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');
        var operationId;
        var ajaxType = 'post';
        form.render();

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantAfterInfo";//当前页面主要使用 url
        var key = '?key=' + saa_key;

        //获取收货信息
        $.ajax({
            url: url + key,
            type: 'get',
            async: false,
            headers: headers,
            beforeSend: function () {
                loading = layer.load(loadType, loadShade);//显示加载图标
            },
            success: function (res) {
                if (res.status == timeOutCode) {
                    layer.msg(timeOutMsg);
                    admin.exit();
                    return false;
                }
                layer.close(loading);//关闭加载图标
                if (res.status != 200) {
                    if (res.status != 204) {
                        layer.msg(res.message);
                    }
                    // layer.msg('未设置收货信息', {icon: 1});
                    return false;
                }
                ajaxType = 'put';//有数据设置
                var data = res.data[0];
                operationId = data.id;//设置该收货地址在数据库中的 id
                $('input[name=after_phone]').val(data.after_phone);
                if (data.status == 1) {
                    $("input[name=add_edit_status]").prop('checked', true);
                } else {
                    $("input[name=add_edit_status]").removeAttr('checked');
                }
                //设置省市区级联 获取省级，开始做级联
                getGroups(1, 0, data.province);
                getGroups(2, data.province, data.city);
                getGroups(3, data.city, data.area);
                $('textarea[name=address]').val(data.address);
                $('textarea[name=store_address]').val(data.store_address);
                form.render();//设置完值需要刷新表单
            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);
            }
        });

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var status = 0;
            var subData;
            var ajaxUrl = url;
            if ($('input[name=add_edit_status]:checked').val()) {
                status = 1;
            }
            if (ajaxType == 'post') {
                successMsg = sucMsg.post;
            } else if (ajaxType == 'put') {
                ajaxUrl = url + '/' + operationId;
                successMsg = sucMsg.put;
            }

            /*diy设置开始*/
            subData = {
                after_phone: $('input[name=after_phone]').val(),
                province: $('select[name=province]').val(),
                city: $('select[name=city]').val(),
                area: $('select[name=area]').val(),
                address: $('textarea[name=address]').val(),
                store_address: $('textarea[name=store_address]').val(),
                status: status,
                key: saa_key
            };
            /*diy设置结束*/

            $.ajax({
                url: ajaxUrl,
                data: subData,
                type: ajaxType,
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);//关闭加载图标
                    if (res.status != 200) {
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg('保存成功', {icon: 1, time: 1000});
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        })


        form.on('select(region)', function(d){
            var select_code = d.value;//当前选中的下拉值，用来请求获取低一层级
            var type = d.elem.id;
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

        var is_area = 1;//是否有区的判断依据，点击市后如果没有区，则该值为 0，保存时候用来判断
        var first_city = 0;//获取的第一个市，用于获取区
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
                        if (a === 0) {
                            first_city = name;
                        }
                        if (group_id) {
                            var selected = '';
                            if (group_id === name) {
                                selected = ' selected ';
                            }
                            $('select[name=' + class_name + ']').append("<option value=" + name + selected + ">" + name + "</option>");
                        } else {
                            $('select[name=' + class_name + ']').append("<option value=" + name + ">" + name + "</option>");
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

    });
    exports('info/list', {})
});
