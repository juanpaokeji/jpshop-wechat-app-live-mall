/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2018/5/17
 * Update DateTime: 2019/3/9  一直在更新，时间随时修改
 * js 提现列表
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
        var operation_id;//数据表格操作需要用到单条 id
        var arr, res;//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单

        var group_data = 0;//是否已加载分组 是 1 否 0
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'partnerWithdraws';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'apply_money', title: '提现金额'},
            {field: 'real_money', title: '实际到账金额'},
            {field: 'creat_time', title: '提现时间'},
            {field: 'status', title: '状态', templet: '#statusTpl'}
        ];
        /*diy设置结束*/

        //price ids 提交申请的时候用到
        var price = '';
        var ids = '';

        //获取提现金额
        arr = {
            method: 'partnerBalance',
            type: 'get'
        };
        res = getAjaxReturn(arr);
        if (res && res.data) {
            $('.price').html(res.data.price);
            price = res.data.price;
            ids = res.data.ids;
        }

        //执行添加或编辑
        form.on('submit(sub)', function () {
            arr = {
                method: ajax_method,
                type: 'post',
                data: {
                    apply_money: price,
                    ids: ids
                }
            };
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg('申请成功', {icon: 1, time: 2000});
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
                    type: 'get'
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

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method,//请求的 api 接口方法
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

    });
    exports('withdrawal/list', {})
});
