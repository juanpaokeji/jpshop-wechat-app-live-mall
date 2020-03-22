/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/5/17 9:50
 * 员工管理
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
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量
        var arr = {};
        var ajax_type;

        var open_index;//定义弹出层，方便关闭
        var operation_id;
        var add_edit_form = $('#add_edit_form');//常用的表单

        //页面不同属性
        var ajax_method = 'users';
        var url = baseUrl + "/users";//当前页面主要使用 url
        var cols = [//加载的表格
            {field: 'username', title: '员工名称', width: "20%"},
            {field: 'title', title: '权限组', width: "20%"},
            {field: 'status', title: '状态', width: "20%", templet: '#statusTpl'},
            {field: 'phone', title: '手机号', width: "20%"},
            {field: 'operations', title: '操作', toolbar: '#operations', width: "20%"}
        ];
        var groupData = 0;//是否已加载 是 1 否 0

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            add_edit_form[0].reset();//表单重置
            $("input[name='add_edit_status']").prop('checked', true);
            //针对密码字段进行设置
            $("input[name=password]").attr('lay-verify', 'required');
            $("input[name=password]").attr('required', true);
            $("input[name=password]").attr('placeholder', '请输入密码');
            form.render();
            ajax_type = 'post';//设置类型为新增
            if (!groupData) {
                getGroups(0);
            } else {
                var selectionBox = document.getElementById('groups');
                selectionBox.options[0].selected = true;
            }
            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0,
                offset: '100px',
                area: ['400px', 'auto'],
                cancel: function () {
                    add_edit_form.hide();
                }
            })
        })

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var status = 0;
            arr = {};
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

            arr['method'] = method;
            arr['type'] = ajax_type;
            arr['data'] = {
                username: $('input[name=username]').val(),
                password: $('input[name=password]').val(),
                intro: $('input[name=intro]').val(),
                group_id: $('#groups').val(),
                status: status,
            };
            var res = getAjaxReturn(arr);
            if (res) {
                layer.msg(success_msg);
                layer.close(openIndex);
                add_edit_form[0].reset();//表单重置
                add_edit_form.hide();
                render.reload();//表格局部刷新
            }


            // var type = $("input[name='add_edit_type']").val();
            // var status;
            // var subData;
            // var ajax_type;
            // var ajaxUrl = url;
            // if ($('input[name=add_edit_status]:checked').val()) {
            //     status = 1;
            // } else {
            //     status = 0;
            // }
            // if (type == 'add') {
            //     ajax_type = 'post';
            //     successMsg = sucMsg.post;
            // } else if (type == 'edit') {
            //     ajaxUrl = url + '/' + $("input[name='operation_id']").val();
            //     ajax_type = 'put';
            //     successMsg = sucMsg.put;
            // }
            // subData = {
            //     username: $('input[name=username]').val(),
            //     password: $('input[name=password]').val(),
            //     intro: $('input[name=intro]').val(),
            //     group_id: $('#groups').val(),
            //     status: status,
            // }
            // $.ajax({
            //     url: ajaxUrl,
            //     data: subData,
            //     type: ajax_type,
            //     async: false,
            //     headers: headers,
            //     beforeSend: function () {
            //         loading = layer.load(loadType, loadShade);//显示加载图标
            //     },
            //     success: function (res) {
            //         if (res.status == timeOutCode) {
            //             layer.msg(timeOutMsg);
            //             admin.exit();
            //             return false;
            //         }
            //         layer.close(loading);//关闭加载图标
            //         if (res.status != 200) {
            //             layer.msg(res.message);
            //             return false;
            //         }
            //         layer.msg(successMsg);
            //         layer.close(openIndex);
            //         add_edit_form[0].reset();//表单重置
            //         add_edit_form.hide();
            //         render.reload();//表格局部刷新
            //     },
            //     error: function () {
            //         layer.msg(errorMsg);
            //         layer.close(loading);
            //     }
            // })
        })

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
                var res = getAjaxReturn(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    $("input[name=username]").val(res.data.username);
                    //针对密码字段进行设置
                    $("input[name=password]").val('');
                    $("input[name=password]").attr('lay-verify', '');
                    $("input[name=password]").attr('required', false);
                    $("input[name=password]").attr('placeholder', '不修改密码，请保持为空');
                    $("input[name=intro]").val(res.data.intro);
                    if (res.data.status == 1) {
                        $("input[name=add_edit_status]").prop('checked', true);
                    } else {
                        $("input[name=add_edit_status]").removeAttr('checked');
                    }
                    if (!groupData) {
                        getGroups(res.data.group_id);
                    } else {
                        $("#groups").val(res.data.group_id);
                    }
                    /*diy设置结束*/

                    form.render();//设置完值需要刷新表单
                    openIndex = layer.open({
                        type: 1,
                        title: '编辑',
                        content: add_edit_form,
                        shade: 0,
                        offset: '100px',
                        area: ['400px', 'auto'],
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
                    if (getAjaxReturn(arr)) {
                        layer.msg(sucMsg.delete);
                        obj.del();
                    }
                })
            } else {
                layer.msg(errorMsg);
            }
        })

        /*动态添加单选框*/
        function getGroups(group_id) {
            arr = {
                method: 'groups?type=1&status=1',
                type: 'get',
            };
            var res = getAjaxReturn(arr);
            if (res && res.data) {
                var txt;
                var id;
                for (var a = 0; a < res.data.length; a++) {
                    txt = res.data[a].title;
                    id = res.data[a].id;
                    if (group_id) {
                        var selected = '';
                        if (group_id === id) {
                            selected = ' selected ';
                        }
                        $("#groups").append("<option value=" + id + selected + ">" + txt + "</option>");
                    } else {
                        $("#groups").append("<option value=" + id + ">" + txt + "</option>");
                    }
                    form.render();
                }
                groupData = 1;
            }
        }

        //以下基本不动
        // 默认加载列表
        arr = {
            name: 'render',//必传参
            elem: '#pageTable',//必传参
            method: ajax_method,//必传参
            cols: [cols],//必传参
        };
        var render = getTableRender(arr);

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

        //修改状态
        form.on('switch(status)', function (obj) {
            arr = {
                method: ajax_method + '/' + this.value,
                type: 'put',
                data: {status: obj.elem.checked ? 1 : 0},
            };
            if (getAjaxReturn(arr)) {
                layer.msg(sucMsg.put);
                layer.close(open_index);
            }
        });

    });
    exports('users/staff/list', {})
});
