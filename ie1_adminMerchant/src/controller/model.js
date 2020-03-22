/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2018/5/17
 * Update DateTime: 2019/3/9  一直在更新，时间随时修改
 * js model
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单

        var group_data = 0;//是否已加载分组 是 1 否 0
        var file_put = '';//base64图片

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }
        var set_image_width = '140px';//设置添加的图片宽度
        var set_image_height = '140px';//设置添加的图片高度

        //实例化百度编辑器
        UE.delEditor('editor');//先删除之前实例的对象
        var ue = UE.getEditor('editor');//添加编辑器 //参数 id 可随意更改为当前期望的值
        ue.commands['uploadimage'] = {
            execCommand: function () {
                sessionStorage.setItem('images_common_type_uEditor', '1');//设置类型为百度编辑器
                sessionStorage.setItem('images_common_div_info', '<img width="100%">');
                images_open_index_fun();
            }
        };
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'model';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'name', title: '文本'},
            {field: 'unfixedSelects', title: '不固定下拉'},
            {field: 'fixedSelects', title: '固定下拉'},
            {field: 'pic_url', title: '图片', templet: '#imgTpl'},
            {field: 'text_area', title: '文本域'},
            {field: 'radio', title: '单选框', templet: '#typeTpl'},
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'sort', title: '排序'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];

        //选择日期
        layDate.render({
            elem: '#datetime',
            type: 'datetime'
        });

        $("#addImgPut").change(function () {//加载图片至img
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    file_put = e.target.result;
                    $("#image").attr("src", e.target.result);
                };
            }
            file = null;
        });
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            /*diy设置开始*/
            $("input[name='app_id']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            $("#image").attr('src', '');
            $("#image").empty();
            form.render();//还原后需要重置表单
            ajax_type = 'post';//设置类型为新增
            //下拉请求接口必须，未请求过，则请求接口并保存，已请求过，获取保存的信息，减少加载时间
            if (!group_data) {
                getGroups(0);
            } else {
                var category = document.getElementById('unfixedSelects');
                category.options[0].selected = true;
            }
            /*diy设置结束*/

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
            if (ue.getContent() === '') {
                layer.msg('百度富文本编辑器内容不能为空', {icon: 1, time: 2000});
                return;
            }
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
                    unfixedSelects: $('select[name=unfixedSelects]').val(),
                    pic_url: file_put,
                    pic_url1: $('#image img').attr('src'),
                    detail_info: ue.getContent(),
                    type: $('input[name=applicationType]:checked').val(),
                    status: status,
                }
            };
            var res = getAjaxReturn(arr);
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
                file_put = '';
                ajax_type = 'put';
                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get',
                };
                var res = getAjaxReturn(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    $("input[name=name]").val(res.data.name);
                    // setTimeout(function () {
                    //     ue.setContent(data.value);
                    // },600);
                    ue.ready(function() {
                        //设置编辑器的内容
                        setTimeout(function () {
                            ue.setContent(data.value, false);
                        }, 600);
                    });
                    if (!group_data) {
                        getGroups(res.data.category_id);
                    } else {
                        $("#category_name").val(res.data.category_id);
                    }
                    $("#image").attr("src", res.data.pic_url);
                    $("#image").empty().append('<img src="' + res.data.pic_url + '" width="' + set_image_width + '" height="' + set_image_height + '">');
                    $("textarea[name=text_area]").val(res.data.text_area);
                    $("select[name=rule_type]").val(res.data.rule_type);
                    $("input[name='radio'][value='" + res.data.type + "']").prop("checked", true);
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
                    if (getAjaxReturn(arr)) {
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
                method: 'merchantCategoryParent',
                type: 'get'
            };
            var res = getAjaxReturn(arr);
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
                        $('select[name=unfixedSelects]').append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        $('select[name=unfixedSelects]').append("<option value=" + id + ">" + name + "</option>");
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
            method: ajax_method,//请求的 api 接口方法和可能携带的参数 key
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
                data: {status: obj.elem.checked ? 1 : 0}
            };
            if (getAjaxReturn(arr)) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
                layer.close(open_index);
            }
        });

        //上传图片现方法
        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.addImgPut').on('click', '.addImgPut', function () {
            sessionStorage.setItem('images_common_div', '#image');
            sessionStorage.setItem('images_common_div_info', '<img width="' + set_image_width + '" height="' + set_image_height + '">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

    });
    exports('model', {})
});
