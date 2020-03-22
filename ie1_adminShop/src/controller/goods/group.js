/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/8/10 18:00  一直在更新，时间随时修改
 * js model
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laypage'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        var laypage = layui.laypage; //分页
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量
        var tabPage = '';
        var pageLimit = 10;
        var limit = 10;//列表中每页显示数量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        var page = '';

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }
        var set_image_width = '100px';//设置添加的图片宽度
        var set_image_height = '100px';//设置添加的图片高度

        /*diy设置开始*/
        //页面不同属性
        var ajax_method = 'merchantCategory';//新ajax需要的参数 method
        var cols = [//加载的表格
            {field: 'sort', title: '排序', width: '12%'},
            {field: 'name', title: '分类名称', width: '15%', templet: '#treeTpl'},
            {field: 'pic_url', title: '分类图标', width: '12%', templet: '#imgTpl'},
            {field: 'img_url', title: '分类海报', width: '15%', templet: '#posterTpl'},
            {field: 'status', title: '默认显示', width: '12%', templet: '#statusTpl'},
            {field: 'create_time', title: '创建时间', width: '14%'},
            {field: 'operations', title: '操作', width: '19.1%', toolbar: '#operations'}
        ];
        var groupData = 0;//是否已加载分组 是 1 否 0
        /*diy设置结束*/

        //显示新增窗口
        form.on('submit(showAdd)', function () {
            add_edit_form[0].reset();//表单重置  必须
            $(".typeIconImg").empty();
            $(".typePosterImg").empty();
            $("input[name='status']").prop('checked', true);//还原状态设置为true

            /*diy设置开始*/
            ajax_type = 'post';//设置类型为新增
            /*diy设置结束*/

            //下拉请求接口必须，未请求过，则请求接口并保存，已请求过，获取保存的信息，减少加载时间
            if (!groupData) {
                getGroups(0);
            } else {
                var category = document.getElementById('parent_id');
                category.options[0].selected = true;
            }
            open_index = layer.open({
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
        });

        //执行添加或编辑
        form.on('submit(sub)', function () {
            var status = 0;
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
            arr = {
                method: method,
                type: ajax_type,
                data: {
                    parent_id: $('select[name=parent_id]').val(),
                    name: $('input[name=name]').val(),
                    pic_url: $('.typeIconImg img').attr('src'),
                    img_url: $('.typePosterImg img').attr('src'),
                    detail_info: $('textarea[name=detail_info]').val(),
                    status: status,
                    sort: $('input[name=sort]').val()
                }
            };
            if (getAjaxReturnKey(arr)) {
                // dataMosaic(getList().data);
                layer.close(open_index);
                add_edit_form[0].reset();//表单重置
                add_edit_form.hide();
                layer.msg(success_msg, {icon: 1, time: 1000}, function () {
                    location.reload();
                });
            }
        });

        //编辑按钮
        $(document).off('click', '.edit').on('click', '.edit', function () {
            var grandpaNode = $(this).parent().parent();
            operation_id = grandpaNode.data("id");
            ajax_type = 'put';
            arr = {
                method: ajax_method + '/' + operation_id,
                type: 'get',
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                /*diy设置开始*/
                if (!groupData) {
                    !(res.data.parent_id === '0') && getGroups(res.data.parent_id);
                    res.data.parent_id === '0' && $('.isShow').hide()
                } else {
                    $("#parent_id").val(res.data.parent_id);
                }
                $("input[name=name]").val(res.data.name);
                $(".typeIconImg").empty().append('<img src="' + res.data.pic_url + '" width="' + set_image_width + '" height="' + set_image_height + '">');
                $(".typePosterImg").empty().append('<img src="' + res.data.img_url + '" width="110px" height="40px">');
                $("textarea[name=detail_info]").val(res.data.detail_info);
                if (res.data.status == 1) {
                    $("input[name=status]").prop('checked', true);
                } else {
                    $("input[name=status]").removeAttr('checked');
                }
                $("input[name=sort]").val(res.data.sort);
                /*diy设置结束*/

                form.render();//设置完值需要刷新表单
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
        })

        //删除按钮
        $(document).off('click', '.delete').on('click', '.delete', function () {
            var grandpaNode = $(this).parent().parent();
            var dataId = grandpaNode.data("id");
            layer.confirm('确定要删除这条数据么?', function (index) {
                layer.close(index);
                arr = {
                    method: ajax_method + '/' + dataId,
                    type: 'delete',
                };
                if (getAjaxReturnKey(arr)) {
                    layer.msg(sucMsg.delete, {icon: 1, time: 2000}, function () {
                        dataMosaic(getList().data);
                    });
                }
            })
        })

        /*动态添加单选框 应用分组*/
        function getGroups(group_id) {
            $('.isShow').show()
            arr = {
                method: 'merchantCategoryParent',
                type: 'get',
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                res.data.length === 0 && $('.isShow').hide()
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
                        $('select[name=parent_id]').append("<option value=" + id + selected + ">" + name + "</option>");
                    } else {
                        $('select[name=parent_id]').append("<option value=" + id + ">" + name + "</option>");
                    }
                    form.render();
                }
                groupData = 1;
            }
        }

        tabPage = 1;
        //以下基本不动
        // 默认加载列表
        var dataList = getList();

        function getList() {
            arr = {
                name: 'render',//必传参
                elem: '#pageTable',//必传参
                type: 'get',
                method: ajax_method,//必传参
                data: {limit: pageLimit, page: tabPage},
                cols: [cols]//必传参
            };
            var render = getAjaxReturnKey(arr);
            page = render.count;
            return render;
        }

        dataMosaic(dataList.data);

        //列表数据拼接
        function dataMosaic(data) {
            var fatherStr = '',//一级分类字符串
                str = '',//最后向页面上添加的字符串
                //页面上编辑和删除按钮的变量字符串
                editAnddelButtonStr = '<a style="cursor:pointer;" class="btns blue edit">编辑</a><a class="btns blue delete" style="cursor:pointer;">删除</a>';
            //遍历树结构数组展示到页面上
            data && data.forEach(function (a) {
                var childStr = '';//二级分类字符串
                var status = '';
                if (a.status == 1) {
                    status = '<div class="sp5"><a style="cursor:pointer;" class="btn-switch on" data-status="' + a.status + '" data-id="' + a.id + '"></a></div>';
                } else {
                    status = '<div class="sp5"><a style="cursor:pointer;" class="btn-switch" data-status="' + a.status + '" data-id="' + a.id + '"></a></div>';
                }
                if (a.parent_id == '0') {
                    //根据status的值判断按钮的样式
                    fatherStr = '<li class="td">' +
                        '<div class="item" data-id="' + a.id + '">' +
                        '<div class="sp1">' + a.sort + '</div>' +
                        '<div class="sp2"><a style="cursor:pointer;" class="btn-showSub" ></a>' + a.name + '</div>' +
                        '<div class="sp3"><img src="' + a.pic_url + '" alt="" class="icon"></div>' +
                        '<div class="sp4"><img src="' + a.img_url + '" alt="" class="icon2"></div>' +
                        status +
                        '<div class="sp6">' + a.create_time + '</div>' +
                        '<div class="sp7">' + editAnddelButtonStr + '</div>' +
                        '</div>';
                    if (a.data && a.data.length > 0) {
                        a.data.forEach(function (e) {
                            var str = '';
                            if (e.status == 1) {
                                str = '<div class="sp5"><a style="cursor:pointer;" class="btn-switch on" data-status="' + e.status + '" data-id="' + e.id + '"></a></div>';
                            } else {
                                str = '<div class="sp5"><a style="cursor:pointer;" class="btn-switch" data-status="' + e.status + '" data-id="' + e.id + '"></a></div>';
                            }
                            childStr += '<div class="item subItem ' + e.id + '" data-id="' + e.id + '">' +
                                '<div class="sp1">' + e.sort + '</div>' +
                                '<div class="sp2"><span class="line"></span>' + e.name + '</div>' +
                                '<div class="sp3"><img src="' + e.pic_url + '" alt="" class="icon"></div>' +
                                '<div class="sp4"><img src="' + e.img_url + '" alt="" class="icon2"></div>' +
                                str +
                                '<div class="sp6">' + e.create_time + '</div>' +
                                '<div class="sp7">' + editAnddelButtonStr + '</div>' +
                                '</div>';
                        })
                    }
                } else {
                    fatherStr = '<li class="td">' +
                        '<div class="item subItem" data-id="' + a.id + '" style="display:block;">' +
                        '<div class="sp1">' + a.sort + '</div>' +
                        '<div class="sp2"><span class="line"></span>' + a.name + '</div>' +
                        '<div class="sp3"><img src="' + a.pic_url + '" alt="" class="icon"></div>' +
                        '<div class="sp4"><img src="' + a.img_url + '" alt="" class="icon2"></div>' +
                        status +
                        '<div class="sp6">' + a.create_time + '</div>' +
                        '<div class="sp7">' + editAnddelButtonStr + '</div>' +
                        '</div>';
                }
                str += fatherStr + childStr + '</li>'
                status = null
            })
            $('.td').remove();
            $('.th').after(str);
        }

        getPage();

        //重写分页
        function getPage() {
            laypage.render({
                elem: 'page' //注意，这里的 test1 是 ID，不用加 # 号
                , count: page //数据总数，从服务端得到
                , prev: '<'
                , next: '>'
                , limit: limit
                , limits: [limit, limit * 2, limit * 3]
                , layout: ['prev', 'page', 'next', 'refresh', 'skip', 'limit']
                , jump: function (obj, first) {
                    //obj包含了当前分页的所有参数，比如：
                    // console.log(obj.curr);
                    //得到当前页，以便向服务端请求对应页的数据。
                    // console.log(obj.limit);
                    //得到每页显示的条数
                    pageLimit = obj.limit;
                    is_page = 1;
                    tabPage = obj.curr;
                    //首次不执行
                    if (!first) {
                        dataMosaic(getList().data);
                    }
                }
            });
        }

        //搜索
        form.on('submit(find)', function (data) {
            arr = {
                method: ajax_method + '?key=' + saa_key + '&limit=' + pageLimit + '&page=' + tabPage,
                type: 'get',
                data: {searchName: data.field.searchName},
            };
            var searchData = getAjaxReturnKey(arr);
            page = searchData.count
            dataMosaic(searchData.data);
            getPage();
        });

        //修改状态
        $(document).off('click', '.btn-switch').on('click', '.btn-switch', function () {
            var status = $(this).data("status");
            arr = {
                method: 'merchantCategoryStatus/' + $(this).parent().parent().data("id"),
                type: 'put',
                data: {status: (status == 1) ? 0 : 1},
            };
            if (getAjaxReturnKey(arr)) {
                if ($(this).attr("class") != 'btn-switch') {
                    $(this).removeClass("on")
                } else {
                    $(this).addClass("on")
                }
                layer.msg(sucMsg.put);
                layer.close(open_index);
            }
        });
        //状态切换
        $("body").off('click', '.btn-showSub').on('click', '.btn-showSub', function (event) {
            event.preventDefault();
            $(this).toggleClass('on').parents(".item").siblings('.subItem').slideToggle(300, function () {
                $(this).removeClass('show')
            })
        });
        $(".btn-showSub").toggleClass('on').parents(".item").siblings('.subItem').slideToggle(300, function () {
            $(this).removeClass('show')
        });

        //上传图片现方法
        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.typeIcon').on('click', '.typeIcon', function () {
            sessionStorage.setItem('images_common_div', '.typeIconImg');
            sessionStorage.setItem('images_common_div_info', '<img width="' + set_image_width + '" height="' + set_image_height + '">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.typePoster').on('click', '.typePoster', function () {
            sessionStorage.setItem('images_common_div', '.typePosterImg');
            sessionStorage.setItem('images_common_div_info', '<img width="110px" height="40px">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

    });
    exports('goods/group', {})
});
