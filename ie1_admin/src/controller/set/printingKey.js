/**
 * Created by 卷泡
 * author: wangjianren
 * Created DateTime: 2019/6/11
 * js 打印分组字段管理
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
        var baseUrl = setter.baseUrl;//访问地址
        var token = localStorage.getItem('juanpao');
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        var filePut = '';//base64图片
        var selval = '';//下拉列表 分组英文名标识

        /*diy设置开始*/
        form.render();

        //页面不同属性
        var ajax_method = 'adminPrintingkey';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'id', title: 'ID'},
            {field: 'name', title: '字段名称'},
            {field: 'english_name', title: '英文名称'},
            {field: 'category_name', title: '所属分组'},
            {field: 'format_create_time', title: '创建时间'},
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        var selectOptons = []; //接收appid
        var groupData = 0;//是否已加载分组 是 1 否 0

        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            //$(".margin-left layui-unselect").remove()
            /*diy设置开始*/
            form.render();//还原后需要重置表单
            ajax_type = 'post';//设置类型为新增
            if (!groupData) {
                getGroups(0);
            } else {
                var category = document.getElementById('category_id');
                category.options[0].selected = true;
            }
            /*diy设置结束*/

            open_index = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0,
                offset: '100px',
                area: ['400px', '600px'],
                cancel: function () {
                    add_edit_form.hide();
                    $('#pic').hide();
                    $("#image").removeAttr("src");
                    $("#background-color").empty();
                }
            })
        })

        /*动态添加单选框 应用分组*/
        function getGroups(group_id) {
            arr = {
                type:'get',
                method:'adminPrinting'
            }
            selectOptons = getAjaxReturn(arr).data
            if (selectOptons) {
                var name;
                var id;
                var type;
                for (var a = 0; a < selectOptons.length; a++) {
                    name = selectOptons[a].name;
                    id = selectOptons[a].id;
                    type= selectOptons[a].type;
                    if (group_id) {
                        var selected = '';
                        if (group_id === id) {
                            selected = ' selected ';
                        }
                        $('select[name=category_id]').append("<option value=" + id + selected + " data-type=" + type + ">" + name + "</option>");
                    } else {
                        $('select[name=category_id]').append("<option value=" + id + " data-type=" + type + ">" + name + "</option>");
                    }
                    form.render();
                }
                groupData = 1;
            }
        }

        //获取模板
        function getModel(){
            var dataList = [];
            $.ajax({
                url:baseUrl + "/adminPrintingkey",
                headers:{'Access-Token':JSON.parse(token).access_token,'Content-Type':'application/x-www-form-urlencoded'},
                type:'post',
                data:{keyword_list_id:$('input[name=keyword_list_id]').val()},
                async:false,
                success:function(res){
                    dataList = res.data.keyword_list
                }
            })
            return dataList
        }

        //遍历字符串
        function forEachString(data){
            var dataStr = '';
            data && data.forEach(function(e){
                dataStr += '<li class="margin-left"><input type="checkbox" name="'+e.keyword_id+'" value="'+e.name+'" style="vertical-align:middle;"><label style="vertical-align:middle;">'+e.name+'</label></li>'
            })
            $(".model").removeClass("is-display");
            $("#background-color li").remove();
            $("#background-color").empty().append(dataStr);
        }

        //模板库Id输入框失去焦点事件
        $('input[name=keyword_list_id]').blur(function(){
            //判断 当模板id存在同时保证当前值和之前的值不相等，目的是当编辑进来时不改变值，而又使其失去焦点触发事件时，不更新下方的checkbox
            if($('input[name=keyword_list_id]').val() && keyword_list_idStr != $('input[name=keyword_list_id]').val()){
                keyword_list_idStr = $('input[name=keyword_list_id]').val()
                forEachString(getModel());
            }
        })

        //checkbox选择和临时存储关键词库Id串checkboxStr
        var checkboxStr = '';
        $(document).off('click', '.margin-left input').on('click', '.margin-left input', function () {
            var a = [],b = [],str = '';
            $(".margin-left input:checked").each(function(e){ //遍历checkbox
                a.push($(this).val());
                b.push($(this).attr('name'))
            })
            //清空关键词库的值和要保存的id串，目的是为了防止重复
            $('input[name=keyword_list]').val('')
            checkboxStr = '';
            str = '';
            a.forEach(function(e){
                str += e + ','
            })
            $('input[name=keyword_list]').val(str)
            b.forEach(function(e){
                checkboxStr += e + ','
            })
        })

        //点击右上角x按钮关闭model移除checkbox
        $(document).off('click','.layui-layer-setwin').on('click','.layui-layer-setwin',function(){
            $("#background-color li").remove();
            $(".model").addClass("is-display");
            keyword_list_idStr = '';
        })

        //执行添加或编辑
        form.on('submit(sub)', function () {
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
                    english_name: $('input[name=english_name]').val(),
                    category_id: $('select[name=category_id]').val(),
                    pic_url: filePut,
                    sort: $('input[name=sort]').val(),
                    status: $('input[name=status]:checked').val() ? 1 : 0,
                }
            };
            var res = getAjaxReturn(arr);
            if (res) {
                layer.msg(success_msg);
                $("#background-color li").remove();
                layer.close(open_index);
                $(".model").addClass("is-display")
                add_edit_form[0].reset();//表单重置
                add_edit_form.hide();
                render.reload();//表格局部刷新
            }
        })

        var keyword_list_idStr = '';
        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            console.log(obj.data)
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'edit') {
                //修改
                ajax_type = 'put';
                arr = {
                    method: ajax_method + '/' + data.id,
                    type: 'get',
                };
                var res = getAjaxReturn(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    $("input[name=name]").val(res.data.name);
                    $("input[name=english_name]").val(res.data.english_name);
                    $("input[name=sort]").val(res.data.sort);
                    if (res.data.status == 1) {
                        $("input[name=status]").prop('checked', true);
                    } else {
                        $("input[name=status]").removeAttr('checked');
                    }
                    if (!groupData) {
                        getGroups(res.data.category_id);
                    } else {
                        $("#category_id").val(res.data.category_id);
                    }
                    selval = obj.data.type
                    if (selval == 2) {
                        $('#pic').show();
                    } else {
                        $('#pic').hide();
                    }

                    $("#image").attr("src",res.data.pic_url);

                    form.render();//设置完值需要刷新表单
                    /*diy设置结束*/
                    open_index = layer.open({
                        type: 1,
                        title: '编辑',
                        content: add_edit_form,
                        shade: 0,
                        offset: '100px',
                        area: ['400px', '600px'],
                        cancel: function () {
                            $("#image").removeAttr("src");
                            $('#pic').hide();
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
                layer.msg(setter.errorMsg);
            }
        });

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method,//请求的 api 接口方法
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

        $("#addImgPut").change(function () {//加载图片至img
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    filePut = e.target.result;
                    $("#image").attr("src", e.target.result);
                };
            }
            file = null;
        });

        //修改状态
        form.on('select(category_id)', function(data){
            selval = $(data.elem).find("option:selected").attr("data-type")
            if (selval == 2) {
                $('#pic').show();
            } else {
                $('#pic').hide();
            }
            $("#image").removeAttr("src");
        });

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
    exports('set/printingKey', {})
});
