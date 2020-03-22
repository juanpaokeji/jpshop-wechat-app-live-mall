/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/5/17 9:50
 * 商户管理
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        var openIndex;//定义弹出层，方便关闭
        var operation_id;
        var arr = {};//全局ajax请求参数

        var is_area = 1;//是否有区的判断依据，点击市后如果没有区，则该值为 0，保存时候用来判断
        //获取省，并获取第一个省的市
        var first_province = 0;
        getGroups(1, 0, 0);//首先获取省列表

        //省切换事件
        form.on('select(province)', function (data) {
            //选择省事件，清空市区，循环获取市
            $('#city').empty().append('<option value="">选择市</option>');
            if (data.value !== '') {
                getGroups(2, data.value);
            } else {
                $('#city').empty().append('<option value="">选择市</option>');
            }
            form.render();
        });

        //页面不同属性
        var ajax_method = 'adminSystemVipUser';//当前页面主要使用 url 请求方法，加载列表和新的render在方法中直接填写，不需要定义

        //商户列表
        var cols = [ //表头
            {field: 'phone', title: '代理人手机号', width: '8%'},
            {
                field: 'province', title: '省市区', templet: function (d) {
                    return '<span>' + d.province + d.city + d.area + '</span>';
                }, width: '13%'
            },
            {field: 'addr', title: '详细地址', width: '10%'},
            {field: 'company_name', title: '公司名称', width: '10%'},
            {field: 'telephone', title: '联系电话', width: '8%'},
            {field: 'qq', title: 'qq', width: '8%'},
            {field: 'email', title: 'email', width: '15%'},
            {field: 'format_create_time', title: '申请时间', width: '11%'},
            {field: 'status', title: '状态', templet: '#statusTpl', width: '7%'},
            {field: 'operations', title: '操作', toolbar: '#operations', width: '10%'}
        ];

        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'edit') {//审核
                //打开新窗口，显示通过和不通过按钮
                layer.confirm('该代理是否通过审核？', {
                    btn: ['通过', '不通过'] //可以无限个按钮
                    , btnAlign: 'c'
                    , btn1: function (index) {
                        //按钮 通过 的回调
                        layer.close(index);
                        arr = {
                            method: ajax_method + '/' + data.id,
                            type: 'put',
                            data: {
                                status: 1,
                            },
                        };
                        var res = getAjaxReturn(arr);
                        if (!res) {
                            return false;
                        }
                        layer.msg(res.message, {icon: 1, time: 2000});
                        render.reload();//表格局部刷新
                    }
                    , btn2: function () {
                        //按钮 不通过 的回调 需要修改审核状态
                        arr = {
                            method: ajax_method + '/' + data.id,
                            type: 'put',
                            data: {
                                status: 2,
                            },
                        };
                        var res = getAjaxReturn(arr);
                        if (!res) {
                            return false;
                        }
                        layer.msg(res.message, {icon: 1, time: 2000});
                        render.reload();//表格局部刷新
                    }
                });
            } else if (layEvent === 'del') {//删除
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    arr = {
                        method: ajax_method + '/' + data.id,
                        type: 'delete',
                    };
                    if (getAjaxReturn(arr)) {
                        layer.msg(sucMsg.delete, {icon: 1, time: 2000});
                        obj.del();
                    }
                })
            }
        });

        //以下基本不动
        //默认加载列表
        arr = {
            'name': 'render',//必传参
            'elem': '#pageTable',//必传参
            'method': ajax_method,//必传参
            'type': 'get',//必传参
            'cols': [cols],//必传参
        };
        var render = getTableRender(arr);

        //搜索
        form.on('submit(find)', function (data) {//查询
            render.reload({
                where: {
                    searchName: data.field.searchName,
                    province_code: $('select[name=province]').val(),
                    city_code: $('select[name=city]').val(),
                },
                page: {
                    curr: 1
                }
            });
        });

        //修改状态
        form.on('switch(status)', function (obj) {
            arr = {
                'method': ajax_method + '/' + this.value,
                'type': 'put',
                'data': {status: obj.elem.checked ? 1 : 0},
            };
            var res = getAjaxReturn(arr);
            if (res) {
                layer.msg(sucMsg.put);
                layer.close(openIndex);
            }
        });

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

    });

    exports('users/merchant/proxy', {});
});