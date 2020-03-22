/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/5/17 9:50
 * 插件-优惠券
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
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
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');
        var arr, res;

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

        //页面不同属性
        var url = baseUrl + "/shopVouTypes";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var cols = [//加载的表格
            {field: 'name', title: '类型名称', width: '20%'},
            {field: 'price', title: '面值', templet: '<div>{{parseFloat(d.price)}}</div>', width: '10%'},
            {field: 'full_price', title: '满减金额', templet: '<div>{{parseFloat(d.full_price)}}</div>', width: '10%'},
            {field: 'count', title: '发放总量', width: '10%'},
            // {field: 'collection_type', title: '领取类型', templet: '#collectionTypeTpl', width: '10%'},
            {field: 'from_date', title: '开始时间', width: '10%'},
            {field: 'to_date', title: '结束时间', width: '10%'},
            {field: 'status', title: '状态', width: '10%', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '20%'}
        ];

        //有效期 时间插件 到年月日
        layDate.render({
            elem: '#from_to_date',
            type: 'date',
            range: true,
            done: function (value) {
                // console.log(value); //得到日期生成的值，如：2017-08-18
            }
        });
        //监听Tab切换
        var tabId = '1';
        layui.element.on('tab(assemble_list)', function () {
            tabId = this.getAttribute('lay-id');
            getTableRender();
        });

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置
            $("input[name='add_edit_status']").prop('checked', true);//还原状态设置为true
            $('.luck').hide();
            if (tabId === '3') {
                $('.luck').show();
            } else {
                $('.luck').hide();
            }
            if (tabId === '4') {
                getGroupsCategory(0);
                $('.category').show();
            } else {
                $('.category').hide();
            }
            if (tabId === '5') {
                getGroupsGoods(0);
                $('.goods').show();
            } else {
                $('.goods').hide();
            }
            form.render();
            $('input[name=add_edit_type]').val('add');//设置类型为新增
            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: $('#add_edit_form'),
                shade: 0,
                offset: '100px',
                area: ['400px', 'auto'],
                cancel: function () {
                    $('#add_edit_form').hide();
                }
            })
        });

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var type = $("input[name='add_edit_type']").val();
            var status;
            var subData;
            var ajaxType;
            var ajaxUrl = url;
            if ($('input[name=add_edit_status]:checked').val()) {
                status = 1;
            } else {
                status = 0;
            }
            if (type == 'add') {
                ajaxType = 'post';
                successMsg = sucMsg.post;
            } else if (type == 'edit') {
                ajaxUrl = url + '/' + $("input[name='operationId']").val();
                ajaxType = 'put';
                successMsg = sucMsg.put;
            }
            var form_to_date = $('input[name=from_to_date]').val();
            form_to_date = form_to_date.split(' - ');
            subData = {
                name: $('input[name=name]').val(),
                type: tabId,
                // collection_type: $('select[name=collection_type]').val(),
                category_id: $('select[name=category_id]').val(),
                goods_id: $('select[name=goods_id]').val(),
                price: $('input[name=price]').val(),
                min_price: $('input[name=min_price]').val(),
                lucky_price: $('input[name=lucky_price]').val(),
                lucky_min_price: $('input[name=lucky_min_price]').val(),
                full_price: $('input[name=full_price]').val(),
                receive_count: $('input[name=receive_count]').val(),
                count: $('input[name=count]').val(),
                act_id: $('#act_id').val(),
                from_date: form_to_date[0],
                to_date: form_to_date[1],
                status: status,
                key: saa_key
            };
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
                    layer.msg(successMsg);
                    layer.close(openIndex);
                    $("#add_edit_form")[0].reset();//表单重置
                    $('#add_edit_form').hide();
                    render.reload();//表格局部刷新
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        })

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            $("input[name='operationId']").val(data.id);
            if (layEvent === 'record') {//领券记录
                arr = {
                    name: 'render',//可操作的 render 对象名称
                    elem: '#pageTableRecord',//需要加载的 table 表格对应的 id
                    method: 'shopVouchers?key=' + saa_key + '&type_id=' + data.id,//请求的 api 接口方法和可能携带的参数 key
                    cols: [[//加载的表格
                        {field: 'user_id', title: '领取用户', width: '15%'},
                        {field: 'create_time', title: '领取时间', width: '20%'},
                        {
                            field: 'is_used', title: '是否已使用', templet: function (d) {
                                if (d.is_used === '1')
                                    return '已使用';
                                else
                                    return '未使用';
                            }, width: '13%'
                        }
                    ]]//加载的表格字段
                };
                getTableRender(arr);//变量名对应 arr 中的 name
                openIndex = layer.open({
                    type: 1,
                    title: '领券记录',
                    content: $('.record_div'),
                    shade: 0,
                    offset: '100px',
                    area: ['800px', '600px'],
                    cancel: function () {
                        $('.record_div').hide();
                    }
                })
            } else if (layEvent === 'edit') {//修改
                $("input[name='add_edit_type']").val('edit');
                $.ajax({
                    url: url + '/' + data.id + key,
                    type: 'get',
                    async: false,
                    headers: headers,
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

                        /*diy设置开始*/
                        $("input[name=name]").val(res.data.name);
                        $("select[name=type]").val(res.data.type);
                        if (res.data.type === '3') {
                            $("input[name=min_price]").val(res.data.min_price);
                            $("input[name=lucky_price]").val(res.data.lucky_price);
                            $("input[name=lucky_min_price]").val(res.data.lucky_min_price);
                            $('.luck').show();
                        } else {
                            $('.luck').hide();
                        }
                        if (res.data.type === '4') {
                            //需要获取商品id res.data.category_id
                            getGroupsCategory(res.data.category_id);
                            $('.category').show();
                        } else {
                            $('.category').hide();
                        }
                        if (res.data.type === '5') {
                            //需要获取商品id res.data.goods_id
                            getGroupsGoods(res.data.goods_id);
                            $('.goods').show();
                        } else {
                            $('.goods').hide();
                        }
                        // $("select[name=collection_type]").val(res.data.collection_type);
                        $("input[name=price]").val(parseFloat(res.data.price));
                        $("input[name=full_price]").val(parseFloat(res.data.full_price));
                        $("input[name=receive_count]").val(res.data.receive_count);
                        $("input[name=count]").val(res.data.count);
                        $("input[name=from_to_date]").val(res.data.from_date + " - " + res.data.to_date);
                        if (res.data.status == 1) {
                            $("input[name=add_edit_status]").prop('checked', true);
                        } else {
                            $("input[name=add_edit_status]").removeAttr('checked');
                        }
                        /*diy设置结束*/

                        form.render();//设置完值需要刷新表单
                        openIndex = layer.open({
                            type: 1,
                            title: '编辑',
                            content: $('#add_edit_form'),
                            shade: 0,
                            offset: '100px',
                            area: ['400px', 'auto'],
                            cancel: function () {
                                $('#add_edit_form').hide();
                            }
                        })
                    },
                    error: function () {
                        layer.msg(errorMsg);
                        layer.close(loading);//关闭加载图标
                    },
                    beforeSend: function () {
                        loading = layer.load(loadType, loadShade);//显示加载图标
                    }
                })
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    $.ajax({
                        url: url + '/' + data.id,
                        data: {key: saa_key},
                        type: 'delete',
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
                            layer.close(loading);
                            if (res.status != 200) {
                                if (res.status != 204) {
                                    layer.msg(res.message);
                                }
                                return false;
                            }
                            layer.msg(sucMsg.delete);
                            obj.del();
                        },
                        error: function () {
                            layer.msg(errorMsg);
                            layer.close(loading);
                        }
                    })
                })
            } else {
                layer.msg(errorMsg);
            }
        });

        //加载列表
        var render;

        function getTableRender() {
            render = table.render({
                elem: '#pageTable',
                url: url + key + '&type=' + tabId,
                page: true, //开启分页
                limit: limit,
                limits: limits,
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
                    if (res.status !== 200) {
                        if (res.status !== 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                }
            });
        }

        getTableRender();

        //搜索
        form.on('submit(find)', function (data) {
            var searchName = data.field.searchName;
            render.reload({
                where: {
                    searchName: searchName
                },
                page: {
                    curr: 1
                }
            })
        });

        //获取优惠券配置
        var config_div = $('.config_div');
        form.on('submit(config)', function () {
            arr = {
                method: 'merchantShopConfig',
                type: 'get'
            };
            res = getAjaxReturnKey(arr);
            if (res && res.data) {
                if (res.data.is_large_scale == 1) {
                    $("input[name=is_large_scale]").prop('checked', true);
                } else {
                    $("input[name=is_large_scale]").removeAttr('checked');
                }
                $('input[name=number]').val(res.data.number);
            }
            form.render();
            openIndex = layer.open({
                type: 1,
                title: '编辑',
                content: config_div,
                shade: 0,
                offset: '100px',
                area: ['400px', 'auto'],
                cancel: function () {
                    config_div.hide();
                }
            })
        });

        //保存优惠券设置
        form.on('submit(subConfig)', function () {
            var is_large_scale = 0;
            if ($('input[name=is_large_scale]:checked').val()) {
                is_large_scale = 1;
            }
            var subData = {
                is_large_scale: is_large_scale,
                number: $('input[name=number]').val(),
                key: saa_key
            };
            arr = {
                method: 'merchantShopConfig',
                type: 'put',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('配置保存成功', {icon: 1, time: 2000});
                layer.close(openIndex);
                config_div.hide();
            }
        });

        //修改状态
        form.on('switch(status)', function (obj) {
            var statusCode;
            if (obj.elem.checked) {/*将禁用改为启用*/
                statusCode = 1
            } else if (!obj.elem.checked) {//将启用改为禁用
                statusCode = 0
            }
            $.ajax({
                url: url + "/" + this.value,
                type: 'put',
                async: false,
                data: {
                    status: statusCode,
                    key: saa_key
                },
                headers: headers,
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
                    layer.msg(sucMsg.put);
                    layer.close(openIndex);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                },
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                }
            })
        });

        //红包类型切换事件，如果为拼手气红包，则需要显示 最小面值 运气红包面值 运气红包最小面值
        form.on('select(type)', function (data) {
            if (data.value === '3') {
                $('.luck').show();
            } else {
                $('.luck').hide();
            }
        });

        /*动态添加单选框 商品列表*/
        function getGroupsCategory(group_id) {
            arr = {
                method: 'merchantCategoryTypeSub',
                type: 'get'
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                $('select[name=category_id]').empty();
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
                        $('select[name=category_id]').append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        $('select[name=category_id]').append("<option value=" + id + ">" + name + "</option>");
                    }
                    form.render();
                }
            }
        }

        /*动态添加单选框 商品列表*/
        function getGroupsGoods(group_id) {
            arr = {
                method: 'merchantGoods',
                type: 'get'
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                $('select[name=goods_id]').empty();
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
                        $('select[name=goods_id]').append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        $('select[name=goods_id]').append("<option value=" + id + ">" + name + "</option>");
                    }
                    form.render();
                }
            }
        }


    });
    exports('voucher/type', {})
});
