/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 创建于 2019/4/16
 * js 电子面单
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
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        var is_area = 1;//是否有区的判断依据，点击市后如果没有区，则该值为 0，保存时候用来判断
        var first_province = 0;//获取的第一个省，用于获取市
        var first_city = 0;//获取的第一个市，用于获取区
        var group_data = 0;//是否已加载分组 是 1 否 0
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'merchantElectronics';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'express_name', title: '快递公司', width: '10%'},
            {field: 'dot_name', title: '网点名称', width: '10%'},
            {field: 'dot_code', title: '网点编码', width: '10%'},
            {field: 'customer_name', title: '客户号', width: '10%'},
            {field: 'name', title: '发件人名称', width: '10%'},
            {field: 'phone', title: '发件人联系方式', width: '10%'},
            {field: 'addr', title: '发件人地址', width: '20%'},
            {field: 'status', title: '状态', templet: '#statusTpl', width: '10%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '10%'}
        ];
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            /*diy设置开始*/
            form.render();//还原后需要重置表单
            ajax_type = 'post';//设置类型为新增
            //下拉请求接口必须，未请求过，则请求接口并保存，已请求过，获取保存的信息，减少加载时间
            if (!group_data) {
                getGroups(0);
            } else {
                var category = document.getElementById('express_id');
                category.options[0].selected = true;
            }
            getRegion(1, 0, 0);//首先获取省列表
            getRegion(2, first_province, 0);//通过获取到的第一个省获取市列表
            getRegion(3, first_city, 0);//通过获取到的第一个市获取区列表
            /*diy设置结束*/

            open_index = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0,
                offset: '100px',
                area: ['40vw', '35vw'],
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
                    express_id: $('select[name=express_id]').val(),
                    customer_name: $('input[name=customer_name]').val(),
                    customer_pwd: $('input[name=customer_pwd]').val(),
                    month_code: $('input[name=month_code]').val(),
                    dot_code: $('input[name=dot_code]').val(),
                    dot_name: $('input[name=dot_name]').val(),
                    company: $('input[name=company]').val(),
                    name: $('input[name=name]').val(),
                    tel: $('input[name=tel]').val(),
                    phone: $('input[name=phone]').val(),
                    post_code: $('input[name=post_code]').val(),
                    addr: $('input[name=addr]').val(),
                    province_code: $('select[name=province]').val(),
                    city_code: $('select[name=city]').val(),
                    area_code: $('select[name=area]').val(),
                    province_name: $('select[name=province]').find("option:selected").text(),
                    city_name: $('select[name=city]').find("option:selected").text(),
                    area_name: $('select[name=area]').find("option:selected").text(),
                    towing_goods: $('input[name=towing_goods]').val(),
                    status: status,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(success_msg, {icon: 1, time: 2000});
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
                ajax_type = 'put';
                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get',
                };
                var res = getAjaxReturnKey(arr);
                if (res && res.data) {
                    var res_data = res.data;
                    /*diy设置开始*/
                    $("input[name=customer_name]").val(res_data.customer_name);
                    $("input[name=customer_pwd]").val(res_data.customer_name);
                    $("input[name=month_code]").val(res_data.customer_name);
                    $("input[name=dot_code]").val(res_data.customer_name);
                    $("input[name=dot_name]").val(res_data.customer_name);
                    $("input[name=company]").val(res_data.customer_name);
                    $("input[name=name]").val(res_data.customer_name);
                    $("input[name=tel]").val(res_data.customer_name);
                    $("input[name=phone]").val(res_data.customer_name);
                    $("input[name=post_code]").val(res_data.customer_name);
                    $("input[name=addr]").val(res_data.customer_name);
                    $("input[name=towing_goods]").val(res_data.towing_goods);
                    if (!group_data) {
                        getGroups(res_data.express_id);
                    } else {
                        $("#express_id").val(res_data.express_id);
                    }
                    getRegion(1, 0, res_data.province_code);
                    getRegion(2, res_data.province_code, res_data.city_code);
                    getRegion(3, res_data.city_code, res_data.area_code);
                    if (res_data.status == 1) {
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
                        area: ['40vw', '35vw'],
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
                        type: 'delete',
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
                method: 'merchantShopExpressCompany',
                type: 'get',
                params: 'is_ok=1',
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
                        $('select[name=express_id]').append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        $('select[name=express_id]').append("<option value=" + id + ">" + name + "</option>");
                    }
                    form.render();
                }
                group_data = 1;
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
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
                layer.close(open_index);
            }
        });

        //获取省市区级联 type 1 省 2 市 3 区，name option需要添加的class，group_id 需要默认选中的值
        function getRegion(type, parent_id, group_id) {
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
                            if (type === 1) {
                                first_province = code;
                            } else if (type === 2) {
                                first_city = code;
                            }
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

        //省市切换事件
        form.on('select(region)', function (data) {
            var type = $(data.elem).attr('id');
            if (type === 'province') {
                //选择省事件，清空市区，循环获取市
                $('#city').empty();
                $('#area').empty();
                getRegion(2, data.value);
                getRegion(3, first_city);
            } else if (type === 'city') {
                $('#area').empty();
                //选择市事件，清空区，循环获取区
                getRegion(3, data.value);
            }
        });

    });
    exports('electronics', {})
});
