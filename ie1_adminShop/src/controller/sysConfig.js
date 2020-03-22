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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'element'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var add_edit_form = $('#add_edit_form');//常用的表单
        var config_type = 0;//默认 0 我的配置， 1 系统配置

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }
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
        var ajax_method = 'merchantDiy';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'title', title: '标题'},
            // {
            //     field: 'type', title: '类型', templet: function (d) {
            //         var type = '类型错误';
            //         // 类型 1=数值 2=字符串 3=数组，目前只有2
            //         if (d.type === '1') {
            //             type = '数值';
            //         } else if (d.type === '2') {
            //             type = '字符串';
            //         } else if (d.type === '3') {
            //             type = '数组';
            //         }
            //         return type;
            //     }
            // },
            {field: 'value', title: '内容'},
            {field: 'format_create_time', title: '创建时间'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];
        /*diy设置结束*/

        //执行编辑
        form.on('submit(sub)', function () {
            if (ue.getContent() === '') {
                layer.msg('value不能为空', {icon: 1, time: 2000});
                return;
            }
            var ajax_type = 'put';//请求方式，如果是个人则put，系统post
            var a_method = ajax_method;
            if (config_type) {
                ajax_type = 'post';
            } else {
                a_method = ajax_method + '/' + operation_id;
            }
            arr = {
                method: a_method,
                type: ajax_type,
                data: {
                    value: ue.getContent()
                }
            };
            if (config_type) {
                arr.data.system_diy_config_id = operation_id;
            }
            var res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('保存成功', {icon: 1, time: 2000});
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
                config_type = data.config_type;
                /*diy设置开始*/
                setTimeout(function () {
                    ue.setContent(data.value);
                }, 600);
                /*diy设置结束*/

                form.render();//设置完值需要刷新表单
                open_index = layer.open({
                    type: 1,
                    title: '编辑',
                    content: add_edit_form,
                    shade: 0,
                    offset: '100px',
                    area: ['650px', '700px'],
                    cancel: function () {
                        add_edit_form.hide();
                    }
                })
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

        //以下基本不动
        //默认加载列表
        var render;

        //获取我的配置表
        function getMyConfig() {
            arr = {
                name: 'render',//可操作的 render 对象名称
                elem: '#pageTable',//需要加载的 table 表格对应的 id
                method: ajax_method + '?key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
                cols: [cols]//加载的表格字段
            };
            render = getTableRender(arr);//变量名对应 arr 中的 name
        }

        getMyConfig();

    });
    exports('sysConfig', {})
});
