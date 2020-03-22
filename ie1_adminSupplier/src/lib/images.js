/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/6/20
 * js 应用使用到的图片库，使用时需引入
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'laypage'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var layPage = layui.laypage;
        var sucMsg = setter.successMsg;//成功提示 数组
        var limit = 15;//列表中每页显示数量
        var tabPage = 1;//获取当前分页的页数
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var images_open_index;//定义弹出层，方便关闭
        var operation_id;//数据表格操作需要用到单条 id
        var arr;//全局ajax请求参数
        var res;//ajax返回值
        var sub_image_url;
        var images_group_id = 0;//设置默认分组为未分组
        /**
         * 百度编辑器的类型暂只支持一个页面只有一个百度编辑器的情况
         * 如有多个，可能必须修改session中的images_common_div值，以免产生bug
         */

        //上传图片现方法
        var images_div = $('.images_div');

        window.images_open_index_fun = function () {
            images_open_index = layer.open({
                type: 1,
                title: '图片',
                content: images_div,
                shade: 0.1,
                offset: '100px',
                area: ['950px', '620px'],
                cancel: function () {
                    images_div.hide();
                    $(".layui-layer-shade").remove();
                    // $(".layui-layer-page").remove();
                },
                success: function (i, j) {
                    $("#layui-layer-shade" + j).remove();
                    // console.log(i, j)
                    // if (i.length === 0) {
                    //     $("#layui-layer-shade" + j).remove();
                    // }
                }
            });
        };

        var ajax_method_images_group = 'supplierPictureGroup';

        //获取分类列表
        function getImagesGroup() {
            arr = {
                method: ajax_method_images_group,
                type: 'get',
                data: {status: 1}
            };
            res = getAjaxReturn(arr);
            if (res && res.data) {

                var data = res.data;
                var data_len = data.length;
                $('.images_group_list').empty();
                $('.images_group_li_ungrouped').addClass('check');
                images_group_id = 0;
                for (var i = 0; i < data_len; i++) {
                    var id = data[i].id;
                    var name = data[i].name;
                    var number = data[i].number;
                    $('.images_group_list').append(
                        '<li class="images_group_li" data="' + id + '">' +
                        '    <span class="left_str grouped_name">' + name + '</span>' +
                        '    <span class="right_str grouped_number">' + number + '</span>' +
                        '</li>'
                    );
                }
            }
        }

        getImagesGroup();

        //获取未分组图片（打开页面默认）
        var images_count = 0;

        //获取图片列表
        function getImages(picture_group_id) {
            arr = {
                method: 'supplierPicture/' + picture_group_id,
                type: 'get',
                data: {
                    limit: limit,
                    page: tabPage
                }
            };
            $('.images_img_list').empty();
            res = getAjaxReturn(arr);
            if (res && res.data) {
                var data = res.data;
                var data_len = data.length;
                if (picture_group_id === 0) {
                    $('.ungrouped').html(res.count);
                }
                //判断当前选中的分组，修改分组右侧数量
                $('.images_group_li').each(function () {
                    if ($(this).attr('data') === picture_group_id) {
                        $($(this).find('.right_str')[0]).html(res.count);
                    }
                });
                images_count = res.count;
                for (var i = 0; i < data_len; i++) {
                    var id = data[i].id;
                    var name = data[i].name;
                    var pic_url = data[i].pic_url;
                    var width = data[i].width;
                    var height = data[i].height;
                    $('.images_img_list').append(
                        '<div class="image_one_div" data="' + id + '">\n' +
                        '    <img src="' + pic_url + '">\n' +
                        '    <a href="javascript:void(0)" class="images_delete_icon layui-icon-close layui-icon" data="' + id + '"></a>\n' +
                        '    <span class="images_size">' + parseFloat(height) + '*' + parseFloat(width) + '</span>\n' +
                        '    <span class="images_name">' + name + '</span>\n' +
                        '</div>'
                    );
                }
            }
        }

        getImages(0);//页面加载默认执行获取图片列表方法，获取未分组图片
        getPage();

        //点击图片删除图标执行方法
        $(document).off('click', '.images_delete_icon').on('click', '.images_delete_icon', function () {
            var that = $(this);
            var id = that.attr('data');
            layer.confirm('确定要删除这张图片么?', function (index) {
                layer.close(index);
                arr = {
                    method: 'supplierGoodsPicture/' + id,
                    type: 'delete'
                };
                var res = getAjaxReturn(arr);
                if (res) {
                    layer.msg('图片删除成功', {icon: 1, time: 2000});
                    that.parent().remove();
                    //判断当前选中的分组，修改分组右侧数量
                    $('.images_group_li').each(function () {
                        if (Number($(this).attr('data')) === images_group_id) {
                            var num = Number($($(this).find('.right_str')[0]).html()) - 1;
                            $($(this).find('.right_str')[0]).html(num);
                        }
                    });
                }
            });
            return false;//阻止冒泡
        });

        //默认列表分页
        function getPage() {
            if (images_count <= limit) {
                return;
            }
            layPage.render({
                elem: 'images_page' //注意，这里的 test1 是 ID，不用加 # 号
                , count: images_count //数据总数，从服务端得到
                , prev: '<'
                , next: '>'
                , limit: limit
                , layout: ['prev', 'page', 'next', 'refresh', 'skip']
                , jump: function (obj, first) {
                    //obj包含了当前分页的所有参数，比如：
                    // console.log(obj.curr); //得到当前页，以便向服务端请求对应页的数据。
                    // console.log(obj.limit); //得到每页显示的条数
                    tabPage = obj.curr;
                    //首次不执行
                    if (!first) {
                        getImages(images_group_id);
                    }
                }
            });
        }

        //点击分组执行事件
        $(document).off('click', '.images_group_li').on('click', '.images_group_li', function () {
            $('.sub_images_class').addClass('layui-btn-disabled');
            var that = $(this);
            var id = that.attr('data');
            images_group_id = Number(id);
            $('.images_group_li').each(function () {
                $(this).removeClass('check');
            });
            that.addClass('check');
            getImages(id);
            getPage();
        });

        //点击图片执行事件
        $(document).off('click', '.image_one_div').on('click', '.image_one_div', function () {
            var that = $(this);
            $('.image_one_div').each(function () {
                $(this).removeClass('check');
            });
            that.addClass('check');
            sub_image_url = $(that.find('img')[0]).attr('src');
            $('.sub_images_class').removeClass('layui-btn-disabled');
        });

        //选择图片点击确定执行事件
        form.on('submit(sub_images)', function () {
            if ($(this).hasClass('layui-btn-disabled')) {
                layer.msg('还未选择图片', {icon: 1, time: 1000});
                return;
            }
            var images_common_div = sessionStorage.getItem('images_common_div');
            var images_common_div_info = sessionStorage.getItem('images_common_div_info');
            //获取传递过来放图片的div，匹配当中的 img
            var img_position = images_common_div_info.indexOf('<img');//img在字符串中的位置
            if (img_position >= 0) {
                images_common_div_info = images_common_div_info.replace('<img', '<img src="' + sub_image_url + '"');
            } else {
                //没有，则传递过来的字符串不符合标准
                layer.msg('需要添加的标签不符合标准，标签中必须含有img', {icon: 1, time: 2000});
                return;
            }
            var images_common_type_uEditor = sessionStorage.getItem('images_common_type_uEditor');
            //判断是否为百度编辑器，如果是则需要另一种添加方式
            if (images_common_type_uEditor === '1') {
                UE.getEditor('editor').execCommand('inserthtml', images_common_div_info);
            } else {
                //获取append类型，是覆盖原图片，还是追加图片 cover 覆盖 add 追加
                var images_common_type_append = sessionStorage.getItem('images_common_type_append');
                if (images_common_type_append) {
                    if (images_common_type_append === 'cover') {
                        $(images_common_div).empty();
                    }
                    sessionStorage.removeItem('images_common_type_append');
                }
                $(images_common_div).append(images_common_div_info);
            }
            layer.close(images_open_index);
            images_div.hide();
            $(".layui-layer-shade").remove();
            // $(".layui-layer-page").remove();
        });

        var images_upload_url = [];
        var images_upload_name = [];
        var images_upload_width = [];
        var images_upload_height = [];
        var images_upload_number = 20;//图片数量限制 20张
        var images_upload_size = 2 * 1024 * 1024;//图片大小限制 2M
        var images_upload_format = ['image/png', 'image/jpeg'];//图片格式限制 image/png 和 image/jpeg
        //该方法中请求接口前，需要判断上传的是否图片，图片大小是否超过限制，图片书来那个属否超过限制
        $("#addImgPutImagesUpload").change(function () {//加载图片至img
            if (this.files.length > 0) {
                loading = layer.load(1, {shade: 0.3, time: 1000});//layer.open 类型和 shade 属性 加载
            }
            images_upload_url = [];//每次点击需要清空
            images_upload_name = [];//每次点击需要清空
            images_upload_width = [];//每次点击需要清空
            images_upload_height = [];//每次点击需要清空
            var files_len = this.files.length;//用户选择的文件数量
            var flag_num = files_len;//本地准备剩余的数量
            if (files_len > images_upload_number) {
                layer.msg('图片数量超出限制', {icon: 1, time: 2000});
                layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
                return;
            }
            for (var j = 0; j < files_len; j++) {
                var file_type = this.files[j].type;//文件类型
                var file_size = this.files[j].size;//文件大小
                // if (file_type !== 'image/png' && file_type !== 'image/jpeg') {
                //     console.log(file_type);
                //     layer.msg('图片格式错误', {icon: 1, time: 2000});
                //     layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
                //     return;
                // }
                if (images_upload_format.indexOf(file_type) === -1) {
                    console.log(file_type);
                    layer.msg('图片格式错误', {icon: 1, time: 2000});
                    layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
                    return;
                }
                if (file_size > images_upload_size) {
                    layer.msg('图片大小超出限制', {icon: 1, time: 2000});
                    layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
                    return;
                }
            }
            for (var i = 0; i < files_len; i++) {
                var file = this.files[i];
                images_upload_name.push(file.name);
                //下面这个 if 里有个严重bug，reader.onloadend 方法有时候会延迟执行，导致图片url未存入数组中 待修复
                if (window.FileReader) {
                    var reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onloadend = function (e) {
                        images_upload_url.push(e.target.result);
                    };
                }
                var _URL = window.URL || window.webkitURL;
                var img;
                if ((file = this.files[i])) {
                    img = new Image();
                    img.onload = function () {
                        images_upload_width.push(this.width);
                        images_upload_height.push(this.height);
                        flag_num--;
                        //当本地图片都已准备好的时候请求接口上传图片
                        if (flag_num === 0) {
                            layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
                            doPictureUploadImagesGroup();
                        }
                    };
                    img.src = _URL.createObjectURL(file);
                }
                file = null;
            }
        });

        //执行图片上传操作，必须在图片 onload完之后执行
        function doPictureUploadImagesGroup() {
            arr = {
                method: 'supplierGoodsPicture',
                type: 'post',
                data: {
                    picture_group_id: images_group_id,
                    pic_url: images_upload_url,
                    name: images_upload_name,
                    width: images_upload_width,
                    height: images_upload_height
                }
            };
            var res = getAjaxReturn(arr);
            if (res) {
                $('.images_group_li').each(function () {
                    if (Number($(this).attr('data')) === images_group_id) {
                        $(this).click();
                    }
                });
            }
        }

        //点击分组管理执行事件
        var imagesGroupRender;
        var pageTableImagesGroup = $('.pageTableImagesGroup');
        var images_group_open_index;
        form.on('submit(group_manage)', function () {
            var cols = [//加载的表格
                {field: 'name', title: '分组名称', width: '150'},
                {field: 'status', title: '状态', templet: '#imagesGroupStatusTpl', width: '95'},
                {field: 'format_create_time', title: '创建时间', width: '180'},
                {field: 'operations', title: '操作', toolbar: '#operationsImagesGroup', width: '170'}
            ];
            arr = {
                name: 'imagesGroupRender',//可操作的 render 对象名称
                elem: '#pageTableImagesGroup',//需要加载的 table 表格对应的 id
                method: ajax_method_images_group,//请求的 api 接口方法和可能携带的参数 key
                cols: [cols]//加载的表格字段
            };
            imagesGroupRender = getTableRender(arr);//变量名对应 arr 中的 name
            images_group_open_index = layer.open({
                type: 1,
                title: '新增',
                content: pageTableImagesGroup,
                // shade: 0.1,
                offset: '100px',
                area: ['630px', '500px'],
                cancel: function () {
                    pageTableImagesGroup.hide();
                },
                success: function (i, j) {
                    $("#layui-layer-shade" + j).remove();
                }
            })
        });

        //分组管理搜索
        form.on('submit(find_images_group)', function (data) {//查询
            imagesGroupRender.reload({
                where: {searchName: data.field.searchNameImagesGroup},
                page: {curr: 1}
            });
        });

        //分组管理修改状态
        form.on('switch(statusImagesGroup)', function (obj) {
            arr = {
                method: ajax_method_images_group + '/' + this.value,
                type: 'put',
                data: {status: obj.elem.checked ? 1 : 0},
            };
            if (getAjaxReturn(arr)) {
                layer.msg('编辑成功', {icon: 1, time: 2000});
                getImagesGroup();
            }
        });

        //分组管理显示新增窗口
        var add_edit_form_images_group = $("#add_edit_form_images_group");
        var images_group_ajax_type;
        var images_group_open_index_add_edit;
        form.on('submit(showAddImagesGroup)', function () {
            add_edit_form_images_group[0].reset();//表单重置  必须
            $("input[name='status_images_group']").prop('checked', true);//还原状态设置为true
            form.render();//还原后需要重置表单
            images_group_ajax_type = 'post';//设置类型为新增
            add_edit_form_images_group.show();
            images_group_open_index_add_edit = layer.open({
                type: 1,
                title: '新增',
                content: add_edit_form_images_group,
                shade: 0.1,
                offset: '100px',
                area: ['600px', 'auto'],
                cancel: function () {
                    add_edit_form_images_group.hide();
                },
                success: function (i, j) {
                    $("#layui-layer-shade" + j).remove();
                }
            })
        });

        //分组管理执行添加或编辑
        form.on('submit(sub_images_group)', function () {
            var status = 0;
            if ($('input[name=status_images_group]:checked').val()) {
                status = 1;
            }
            var success_msg;
            var method = ajax_method_images_group;
            if (images_group_ajax_type === 'post') {
                success_msg = sucMsg.post;
            } else if (images_group_ajax_type === 'put') {
                method += '/' + operation_id;
                success_msg = sucMsg.put;
            }
            arr = {
                method: method,
                type: images_group_ajax_type,
                data: {
                    name: $('input[name=name_images_group]').val(),
                    status: status,
                }
            };
            var res = getAjaxReturn(arr);
            if (res) {
                layer.msg(success_msg, {icon: 1, time: 2000});
                layer.close(images_group_open_index_add_edit);
                add_edit_form_images_group[0].reset();//表单重置
                add_edit_form_images_group.hide();
                imagesGroupRender.reload();//表格局部刷新
                getImagesGroup();
            }
        });

        //分组管理表格操作点击事件
        table.on('tool(pageTableImagesGroup)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            operation_id = data.id;
            if (layEvent === 'edit') {//修改
                images_group_ajax_type = 'put';
                arr = {
                    method: ajax_method_images_group + '/' + data.id,
                    type: 'get',
                };
                var res = getAjaxReturn(arr);
                if (res && res.data) {
                    /*diy设置开始*/
                    $("input[name=name_images_group]").val(res.data.name);
                    if (res.data.status == 1) {
                        $("input[name=status_images_group]").prop('checked', true);
                    } else {
                        $("input[name=status_images_group]").removeAttr('checked');
                    }
                    /*diy设置结束*/

                    form.render();//设置完值需要刷新表单
                    images_group_open_index_add_edit = layer.open({
                        type: 1,
                        title: '编辑',
                        content: add_edit_form_images_group,
                        shade: 0,
                        offset: '100px',
                        area: ['600px', 'auto'],
                        cancel: function () {
                            add_edit_form_images_group.hide();
                        }
                    })
                }
            } else if (layEvent === 'del') {
                layer.confirm('确定要删除这条数据么?', function (index) {
                    layer.close(index);
                    arr = {
                        method: ajax_method_images_group + '/' + data.id,
                        type: 'delete',
                    };
                    if (getAjaxReturn(arr)) {
                        layer.msg(sucMsg.delete, {icon: 1, time: 2000});
                        obj.del();
                        getImagesGroup();
                    }
                })
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

    });
    exports('images', {});
});
