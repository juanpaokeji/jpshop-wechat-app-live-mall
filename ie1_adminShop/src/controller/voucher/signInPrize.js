/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/2/13
 * 插件-签到
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var table = layui.table;
        var form = layui.form;
        var setter = layui.setter;//配置
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var saa_key = sessionStorage.getItem('saa_key');
        var sign_in_id = sessionStorage.getItem('sign_in_id');//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var render;
        form.render();

        var ajax_method = 'merchantSignUserPrize';//新ajax需要的参数 method
        var cols = [//加载的表格
            //头像 昵称 连续签到次数 累计次数 操作查看详情
            {field: 'avatar', title: '头像', templet: '#imgTpl'},
            {field: 'nickname', title: '昵称'},
            {field: 'days', title: '连续签到次数'},
            {field: 'give_type', title: '获取类型', templet: '#give_typeTpl'},
            {field: 'give_value', title: '获取内容', templet: '#give_valueTpl'},
            {field: 'create_time', title: '获取时间'},
            {field: 'status', title: '奖励发放状态', templet: '#statusTpl', sort: true},
            {field: 'remark', title: '备注（可编辑）', edit: 'text'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];

        //备注编辑事件
        table.on('edit(pageTable)', function (obj) { //注：edit是固定事件名，test是table原始容器的属性 lay-filter="对应的值"
            arr = {
                method: 'merchantSignUserPrize/' + obj.data.id,
                type: 'put',
                data: {
                    remark: obj.value,
                }
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(setter.successMsg.put);
            }
        });

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            if (layEvent === 'edit') {//发放实物商品按钮
                layer.confirm('该商品确定已经发放了吗，请查看备注是否填写完善，如已发放，请点击确认', function (index) {
                    layer.close(index);
                    arr = {
                        method: 'merchantSignUserPrize/' + data.id,
                        type: 'put',
                        data: {
                            status: 1,
                        }
                    };
                    var res = getAjaxReturnKey(arr);
                    if (res) {
                        layer.msg(setter.successMsg.put);
                        render.reload();//表格局部刷新
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
            method: ajax_method + '?key=' + saa_key + '&sign_id=' + sign_in_id,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols],//加载的表格字段
        };
        render = getTableRender(arr);//变量名对应 arr 中的 name

        //搜索
        form.on('submit(find)', function (data) {//查询
            render.reload({
                where: {searchName: data.field.searchName},
                page: {curr: 1}
            });
        });

    });
    exports('voucher/signInPrize', {})
});
