/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/10/21
 * js 新人专享
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
        var saa_id = sessionStorage.getItem('saa_id');
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var add_edit_form = $('#add_edit_form');//常用的表单
        form.render();
        /*diy设置开始*/

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
        var ajax_method = 'merchantRecruits';//新ajax需要的参数 method
        //已添加的商品表格
        var cols = [//加载的表格
            {
                field: 'pic_urls', title: '图片', templet: function (d) {
                    var pic_url_one = d.pic_urls.split(',')[0];
                    return '<img src="' + pic_url_one + '">';
                }
            },
            {field: 'name', title: '商品名称'},
            {field: 'price', title: '价格'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        //可选择商品表格
        var cols_choice = [//加载的表格
            {type: 'checkbox'},
            {
                field: 'pic_urls', title: '图片', templet: function (d) {
                    var pic_url_one = d.pic_urls.split(',')[0];
                    return '<img src="' + pic_url_one + '">';
                }
            },
            {field: 'name', title: '商品名称'},
            {field: 'price', title: '价格'}
        ];
        /*diy设置结束*/

        //获取开关状态
        arr = {
            method: 'merchantAppInfo/' + saa_id,
            type: 'get'
        };
        var res = getAjaxReturnKey(arr);
        if (res && res.data) {
            if (res.data.is_recruits === '1') {
                $("input[name=is_recruits]").prop('checked', true);
            } else {
                $("input[name=is_recruits]").removeAttr('checked');
            }
            if (res.data.is_recruits_show == '1') {
                $("input[name=is_recruits_show]").prop('checked', true);
            } else {
                $("input[name=is_recruits_show]").removeAttr('checked');
            }
            form.render();
        }

        //新人专享开关事件
        form.on('switch(is_recruits)', function(data){
            var is_checked = data.elem.checked ? 1 : 0;
            arr = {
                method: 'merchantAppInfos/' + saa_id,
                type: 'put',
                data: {is_recruits: is_checked}
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.status === 200) {
                if (is_checked) {
                    layer.msg('开启成功', {icon: 1, time: 2000});
                } else {
                    layer.msg('关闭成功', {icon: 1, time: 2000});
                }
            } else {
                if (is_checked) {
                    layer.msg('开启失败', {icon: 1, time: 2000});
                    $("input[name=is_recruits]").removeAttr('checked');
                } else {
                    layer.msg('关闭失败', {icon: 1, time: 2000});
                    $("input[name=is_recruits]").prop('checked', true);
                }
                form.render();
            }
        });

        //新用户展示开关事件
        form.on('switch(is_recruits_show)', function(data){
            var is_checked = data.elem.checked ? 1 : 0;
            arr = {
                method: 'merchantAppInfos/' + saa_id,
                type: 'put',
                data: {is_recruits_show: is_checked}
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.status === 200) {
                if (is_checked) {
                    layer.msg('开启成功', {icon: 1, time: 2000});
                } else {
                    layer.msg('关闭成功', {icon: 1, time: 2000});
                }
            } else {
                if (is_checked) {
                    layer.msg('开启失败', {icon: 1, time: 2000});
                    $("input[name=is_recruits_show]").removeAttr('checked');
                } else {
                    layer.msg('关闭失败', {icon: 1, time: 2000});
                    $("input[name=is_recruits_show]").prop('checked', true);
                }
                form.render();
            }
        });

        //已选择的商品表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'del') {
                layer.confirm('确定要删除这个商品么?', function (index) {
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

        //添加商品按钮点击事件
        var goods_open_index;
        var goods_form = $('#goods_form');
        var save_goods_user_id;
        var goods_member_arr = [];
        form.on('submit(add_goods)', function () {
            save_goods_user_id = $(this).attr('data');
            var cols = [//加载的表格
                {type: 'checkbox'},
                {
                    field: 'pic_urls', title: '图片', templet: function (d) {
                        var pic_url_one = d.pic_urls.split(',')[0];
                        return '<img src="' + pic_url_one + '">';
                    }
                },
                {field: 'name', title: '商品名称'},
                {field: 'price', title: '价格'}
            ];

            table.render({
                elem: '#pageTableGoods',
                url: baseUrl + '/merchantRecruitsgoods?key=' + saa_key,
                page: true, //开启分页
                limit: 10,
                limits: [10, 20, 30],
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
                    goods_member_arr = res.data;
                    goods_open_index = layer.open({
                        type: 1,
                        title: '添加商品',
                        content: goods_form,
                        shade: 0,
                        offset: '100px',
                        area: ['600px', '600px'],
                        cancel: function () {
                            goods_form.hide();
                        }
                    });
                }
            });
        });

        var save_goods_list_ids = [];
        //商品列表点击复选框事件
        table.on('checkbox(pageTableGoods)', function (obj) {
            if (obj.type == 'all') {
                //点击全选执行
                if (obj.checked == true) {
                    for (var i = 0; i < goods_member_arr.length; i++) {
                        save_goods_list_ids.push(goods_member_arr[i]['id']);
                    }
                }
            } else {
                //选择单条执行
                if (obj.checked == true) {
                    //将该选择数据存入数组
                    save_goods_list_ids.push(obj.data.id);
                } else {
                    //删除该选择元素
                    var arrIndex = save_goods_list_ids.indexOf(obj.data.id);
                    if (arrIndex > -1) {
                        save_goods_list_ids.splice(arrIndex, 1);
                    }
                }
            }
        });

        //商品列表保存执行事件
        form.on('submit(goods_save)', function () {
            if (save_goods_list_ids.length <= 0) {
                layer.msg('未选择商品', {icon: 1, time: 2000});
                return;
            }
            duplicateRemoval(save_goods_list_ids);
            arr = {
                method: 'merchantRecruits',
                type: 'post',
                data: {
                    goods_ids: save_goods_list_ids
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                save_goods_list_ids = [];
                layer.msg('保存成功', {icon: 1, time: 2000}, function () {
                    location.reload();
                });
            }
        });

    });
    exports('voucher/recruits', {})
});
