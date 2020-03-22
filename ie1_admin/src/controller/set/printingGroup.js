/**
 * Created by 卷泡
 * author: wangjianren
 * Created DateTime: 2019/6/11
 * js 打印分组管理
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

        /*diy设置开始*/
        form.render();

        //页面不同属性
        var ajax_method = 'adminPrinting';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'id', title: 'ID'},
            {field: 'name', title: '分组名称'},
            {field: 'english_name', title: '英文名称'},
            {field: 'format_create_time', title: '创建时间'},
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];

        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            $("#add_edit_form")[0].reset();//表单重置  必须
            $("input[name='status']").prop('checked', true);//还原状态设置为true
            //$(".margin-left layui-unselect").remove()
            /*diy设置开始*/
            form.render();//还原后需要重置表单
            ajax_type = 'post';//设置类型为新增
            /*diy设置结束*/

            open_index = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form,
                shade: 0,
                offset: '100px',
                area: ['400px', '350px'],
                cancel: function () {
                    add_edit_form.hide();
                    $("#background-color").empty();
                }
            })
        })

        //获取模板
        function getModel(){
            var dataList = [];
            $.ajax({
                url:baseUrl + "/adminPrinting",
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
                    sort: $('input[name=sort]').val(),
                    type: $('select[name=type]').val(),
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
                    $("select[name=type]").val(res.data.type);
                    if (res.data.status == 1) {
                        $("input[name=status]").prop('checked', true);
                    } else {
                        $("input[name=status]").removeAttr('checked');
                    }
                    form.render();//设置完值需要刷新表单
                    /*diy设置结束*/
                    open_index = layer.open({
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
                    params = {
                        method: 'adminPrintingkey',
                        type: 'get',
                        data:{
                            category_id:data.id
                        }
                    }
                    var result = getAjaxReturn(params)

                    if (result.status == 200) {
                        layer.confirm('此分组下有子类，需要一并删除么?', function (idx) {
                            layer.close(idx);
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
                        arr = {
                            method: ajax_method + '/' + data.id,
                            type: 'delete',
                        };
                        if (getAjaxReturn(arr)) {
                            layer.msg(sucMsg.delete);
                            obj.del();
                        }
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
            } else {
                swt.prop('checked',!obj.elem.checked);
                form.render();
            }
        });

    });
    exports('set/printingGroup', {})
});
