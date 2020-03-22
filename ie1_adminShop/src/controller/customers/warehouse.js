/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/6/25
 * js 团购路线
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id = '0';//数据表格操作需要用到单条 id
        var arr, res;//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'merchantWarehouse';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'name', title: '路线名称'},
            {field: 'realname', title: '司机姓名'},
            {field: 'phone', title: '联系电话'},
            {field: 'addr', title: '地址'},
            {
                field: 'coordinate', title: '经纬度', templet: function (d) {
                    return d.latitude + ',' + d.longitude;
                }
            },
            {field: 'leader_num', title: '团长人数'},
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            /*diy设置开始*/
            operation_id = '0';
            form.render();//还原后需要重置表单
            ajax_type = 'post';//设置类型为新增
            /*diy设置结束*/
            getGroups(0);

            open_index = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0,
                offset: '100px',
                area: ['600px', 'auto'],
                cancel: function () {
                    add_edit_form.hide();
                }
            })
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
                method += '/' + operation_id;
                success_msg = sucMsg.put;
            }
            arr = {
                method: method,
                type: ajax_type,
                data: {
                    name: $('input[name=name]').val(),
                    realname: $('input[name=realname]').val(),
                    phone: $('input[name=phone]').val(),
                    addr: $('input[name=addr]').val(),
                    coordinate: $('input[name=coordinate]').val(),
                    leader_uid: $('select[name=leader_uid]').val(),
                    status: status,
                }
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(success_msg, {icon: 1, time: 2000});
                layer.close(open_index);
                add_edit_form[0].reset();//表单重置
                add_edit_form.hide();
                render.reload();//表格局部刷新
            }
        });

        //表格操作点击事件
        var map;
        var circuit_map_div = $('.circuit_map');
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'circuit_map') {//查看自提点线路图
                //初始化地图插件
                var longitude = data.longitude;
                var latitude = data.latitude;
                map = new AMap.Map('circuit_map', {
                    center: [longitude, latitude],
                    zoom: 18
                });
                //在地图上创建标注点
                // 创建一个 Marker 实例：
                var marker;
                marker = new AMap.Marker({
                    position: new AMap.LngLat(longitude, latitude),   // 经纬度对象，也可以是经纬度构成的一维数组[116.39, 39.9]
                    title: '仓库'
                });
                map.add(marker);
                //获取团长地址列表
                arr = {
                    method: 'merchantWarehouseleader/' + data.id,
                    type: 'get'
                };
                res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    for (var i = 0; i < res.data.length; i++) {
                        // 创建一个 Marker 实例：
                        marker = new AMap.Marker({
                            position: new AMap.LngLat(res.data[i].longitude, res.data[i].latitude),   // 经纬度对象，也可以是经纬度构成的一维数组[116.39, 39.9]
                            title: res.data[i].realname
                        });
                        map.add(marker);
                    }
                    /*diy设置结束*/
                    form.render();//设置完值需要刷新表单
                }
                open_index = layer.open({
                    type: 1,
                    title: '标注图',
                    content: circuit_map_div,
                    shade: 0,
                    offset: '100px',
                    area: ['800px', '600px'],
                    cancel: function () {
                        circuit_map_div.hide();
                    }
                })
            } else if (layEvent === 'edit') {//修改
                ajax_type = 'put';
                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get'
                };
                res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    $("input[name=name]").val(res.data.name);
                    $("input[name=realname]").val(res.data.realname);
                    $("input[name=phone]").val(res.data.phone);
                    $("input[name=addr]").val(res.data.addr);
                    $("input[name=coordinate]").val(res.data.latitude + ',' + res.data.longitude);
                    getGroups(res.data.leader_uid);
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
                        offset: '100px',
                        area: ['600px', 'auto'],
                        cancel: function () {
                            add_edit_form.hide();
                        }
                    })
                }
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    arr = {
                        method: ajax_method + '/' + data.id,
                        type: 'delete'
                    };
                    if (getAjaxReturnKey(arr)) {
                        layer.msg(sucMsg.delete, {icon: 1, time: 2000});
                        obj.del();
                    }
                })
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        /*动态添加单选框 应用分组*/
        function getGroups(group_id) {
            arr = {
                method: 'merchantTuanUser',
                type: 'get',
                data: {warehouse_id: 0,
                    warehouse_id1: operation_id,
                    limit: 999
                }// warehouse_id 筛选条件，团长没有绑定仓库， warehouse_id1 判断条件，编辑时当前编辑的仓库 id
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                $('select[name=leader_uid]').empty();
                var name;
                var id;
                for (var a = 0; a < res.data.length; a++) {
                    name = res.data[a].realname;
                    id = res.data[a].uid;
                    if (group_id) {
                        var selected = '';
                        if (group_id === id) {
                            selected = ' selected ';
                        }
                        $('select[name=leader_uid]').append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        $('select[name=leader_uid]').append("<option value=" + id + ">" + name + "</option>");
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
            cols: [cols]//加载的表格字段
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
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
                layer.close(open_index);
            }
        });

    });
    exports('customers/warehouse', {})
});
