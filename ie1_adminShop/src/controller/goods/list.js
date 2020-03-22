/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/9 10:10  一直在更新，时间随时修改
 * js 商品列表
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laypage', 'element'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var laypage = layui.laypage; //分页
        var element = layui.element; //选项卡
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var tabPage = 1;
        var limit = 10;//列表中每页显示数量
        var pageLimit = 10;
        var saa_key = sessionStorage.getItem('saa_key');

        /*diy设置开始*/
        //页面不同属性
        var arr = {};
        var url = "merchantGoods";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var filePut = '';//base64图片

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
        /*diy设置结束*/

        //新增按钮点击事件
        $(document).on('click', '.showAdd', function () {
            //新增前查询是否有在使用的模板
            $.ajax({
                url: baseUrl + '/merchantShopExpressTemplate' + key + '&status=1',
                type: 'get',
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);
                    if (res.status != 200) {
                        layer.confirm('未设置运费模板，是否跳转运费模板设置页面?', function (index) {
                            layer.close(index);
                            var href = 'logistics/express';
                            //循环查找当前页面路由对应的子菜单路由
                            $('.id_menu').find('a').each(function (index, j) {
                                var lay_href = $(j).attr('lay-href');
                                //当当前页面路由等于这个子菜单路由时，设置一级和二级背景色
                                if (href === lay_href) {
                                    var parent = $(this).parent().parent().parent();
                                    parent.show();//显示当前菜单的兄弟菜单
                                    $(this).parent().addClass('current').siblings().removeClass("current");//设置当前二级菜单的背景
                                    parent.parent().eq(0).addClass("active").siblings().removeClass("active");//设置当前一级菜单的背景
                                }
                            })
                            location.hash = '/' + href;
                        })
                        return false;
                    }
                    //跳转新增页面
                    sessionStorage.removeItem('goods_id');//当前操作 id ,编辑时可用到
                    sessionStorage.setItem('goods_type', '0');//1代表该商品为门店商品 0 为普通商品
                    location.hash = '/goods/add';
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        })

        //编辑按钮
        $(document).off('click', '.btn1').on('click', '.btn1', function () {
            var id = $(this).data("id");
            sessionStorage.setItem('goods_id', id);
            sessionStorage.setItem('goods_type', '0');//1代表该商品为门店商品 0 为普通商品
            location.hash = '/goods/add';
        })

        //删除按钮
        $(document).off('click', '.btn2').on('click', '.btn2', function () {
            var id = $(this).data("id");
            layer.confirm('确定要将这个商品移到回收站么?', function (index) {
                layer.close(index);
                $.ajax({
                    url: baseUrl + '/merchantGoods/' + id,
                    data: {key: saa_key},
                    type: 'delete',
                    async: false,
                    headers: headers,
                    beforeSend: function () {
                        loading = layer.load(loadType, loadShade);//显示加载图标
                    },
                    success: function (res) {
                        if (res.status == timeOutCode) {
                            layer.msg(timeOutMsg);
                            admin.exit();
                            return false;
                        }
                        layer.close(loading);
                        if (res.status != 200) {
                            if (res.status != 204) {
                                layer.msg(res.message);
                            }
                            return false;
                        }
                        layer.msg(sucMsg.delete);
                        getList()
                        dataMosaic();
                    },
                    error: function () {
                        layer.msg(errorMsg);
                        layer.close(loading);
                    }
                })
            })
        })

        //微信二维码
        $(document).off('click', '.btn4').on('click', '.btn4', function () {
            var id = $(this).data("id");
            arr = {
                method: 'merchantGoodsQCode/' + id,
                type: 'get',
                data: {url: 'pages/goodsItem/goodsItem/goodsItem'}
            };
            res = getAjaxReturnKey(arr);
            if (res && res.data) {
                layer.open({
                    type: 1,
                    title: '商品二维码',
                    content: '<div><img src="' + res.data.url + '" /></div>',
                    shade: 0.3,
                    shadeClose: true,
                    offset: '200px',
                    area: ['285px', '322px']
                })
            }
        });

        //监听Tab切换
        var tabId = 1;
        element.on('tab(goods_list)', function () {
            tabId = this.getAttribute('lay-id');
            tabPage = 1;
            getList()
            dataMosaic();
            // if (tabId == '1') {
            // } else if (tabId == '0') {
            //     dataMosaic(getList());
            // }
            getPage();
        });

        //以下基本不动
        //加载列表
        tabPage = 1;
        var dataList = {};
        shipClass();

        getList();
        function getList() {
            arr = {
                method: url,
                type: 'get',
                data: {
                    limit: pageLimit,
                    page: tabPage,
                    status: tabId
                }
            };
            dataList = getAjaxReturnKey(arr);
        }

        dataMosaic()

        //列表拼接
        function dataMosaic() {
            var data = dataList.data;
            var dataString = '';//需要拼接的字符串
            data && data.forEach(function (e) {
                //处理label 改为处理 预售，限量，秒杀 属性 待修改
                // var label = '';
                // e.label.split(',').forEach(function (ev) {
                //     label += ev + ' ';
                // })
                var property = '';
                if (Number(e.is_sale) > 0) {
                    property += '<span style="color: #436be5; margin-left: 5px">预售</span>';
                } else {
                    property += '<span style="margin-left: 5px">预售</span>';
                }
                if (Number(e.limit_number) > 0) {
                    property += '<span style="color: #436be5; margin-left: 5px">限量</span>';
                } else {
                    property += '<span style="margin-left: 5px">限量</span>';
                }
                if (Number(e.is_flash) > 0) {
                    property += '<span style="color: #436be5; margin-left: 5px">秒杀</span>';
                } else {
                    property += '<span style="margin-left: 5px">秒杀</span>';
                }
                if (Number(e.is_open_assemble) > 0) {
                    property += '<span style="color: #436be5; margin-left: 5px">拼团</span>';
                } else {
                    property += '<span style="margin-left: 5px">拼团</span>';
                }

                //判断是否上架
                var shelves = '', type = '';
                if (e.status == 1) {
                    shelves = '<td><a style="cursor:pointer;" class="btn-switch on" data-startTime="' + e.start_time + '" data-id="' + e.id + '"></a></td>';
                    type = '<td><a style="cursor:pointer;" class="btn shelves">上架</a></td>'
                } else {
                    shelves = '<td><a style="cursor:pointer;" class="btn-switch" data-id="' + e.id + '"></a></td>';
                    type = '<td><a style="cursor:pointer;" class="btn shelves">下架</a></td>'
                }
                dataString += '<tr class="dataList">' +
                    '<td>' + e.sort + '</td>' +
                    '<td>' + e.id + '</td>' +
                    '<td class="cf">' +
                    '<div class="l"><a style="cursor:pointer;"><img src="' + e.pic_urls.split(',')[0] + '" alt="" class="img"></a></div>' +
                    '<div class="r">' +
                    '<div class="name"><a style="cursor:pointer;">' + e.name + '</a></div>' +
                    '<div class="tag"><i class="blue">[' + e.m_category_name + ']</i></div>' +
                    '</div>' +
                    '</td>' +
                    '<td>￥' + e.price + '</td>' +
                    '<td>' + e.stocks + '</td>' +
                    '<td>' + (e.number ? e.number : 0) + '</td>' +
                    type +
                    '<td>' + property + '</td>' +
                    shelves +
                    '<td>' + e.create_time + '</td>' +
                    '<td>' +
                    '<a style="cursor:pointer;" class="btns btn1" data-id="' + e.id + '"></a>' +
                    '<a style="cursor:pointer;" class="btns btn2" data-id="' + e.id + '"></a>' +
                    '<a style="cursor:pointer;" class="btns btn4" data-id="' + e.id + '"></a>' +
                    '</td>' +
                    '</tr>';
            })
            $('.dataList').remove();
            $('tr').after(dataString);
        }

        getPage();

        //重写分页
        function getPage() {
            laypage.render({
                elem: 'page' //注意，这里的 test1 是 ID，不用加 # 号
                , count: dataList.count //数据总数，从服务端得到
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
                    // is_page = 1;
                    tabPage = obj.curr;
                    //首次不执行
                    if (!first) {
                        getList()
                        dataMosaic();
                    }
                }
            });
        }

        //获取商品的分类
        function shipClass() {
            $.ajax({
                url: baseUrl + '/merchantCategoryTypeMini' + '?key=' + saa_key,
                type: "get",
                headers: headers,
                async: false,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);
                    var merchantShopCategoryList = [];
                    var selectString = '<option value="">未选择</option>';
                    res.data && res.data.forEach(function (e) {
                        if (e.sub && e.sub.length > 0) {
                            e.sub.forEach(function (ev) {
                                selectString += '<option value="' + ev.id + '">' + ev.name + '</option>';
                                merchantShopCategoryList.push(ev);
                            })
                        }
                    })
                    $(".select").append(selectString);
                    $(".select option:first").prop("selected", 'selected');
                    //merchantShopCategory = merchantShopCategoryList;
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }

        //搜索按钮
        $(document).off('click', '.find').on('click', '.find', function () {
            arr = {
                method: url,
                type: 'get',
                data: {
                    limit: pageLimit,
                    page: tabPage,
                    status: tabId
                }
            };
            if (Trim($(".select").val()) !== '') {
                arr.data.m_category_id = $(".select").val();
            }
            if (Trim($(".selectId").val()) !== '') {
                arr.data.searchName = $(".selectId").val();
            }
            dataList = getAjaxReturnKey(arr);
            dataMosaic();
            getPage();
        })

        //修改是否上架
        $(document).off('click', '.btn-switch').on('click', '.btn-switch', function () {
            var start_time = '', status = '';
            var that = $(this);
            if (that.attr("class") == 'btn-switch') {
                status = 1;
            } else {
                status = 0;
                if (that.data("startTime")) {
                    start_time = Date.parse(new Date()) / 1000;
                }
            }
            $.ajax({
                url: baseUrl + "/merchantGood/" + $(this).data("id"),
                type: 'put',
                async: false,
                data: {status: status, start_time: start_time, key: saa_key},
                headers: headers,
                success: function (res) {
                    if (that.attr("class") != 'btn-switch') {
                        that.removeClass("on")
                        that.parent().prev().prev().children().text('下架')
                    } else {
                        that.addClass("on")
                        that.parent().prev().prev().children().text('上架')
                    }
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);//关闭加载图标
                    if (res.status != 200) {
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg(sucMsg.put);
                    layer.close(openIndex);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                },
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                }
            })
        })

        //修改是否推荐
        form.on('switch(is_topTpl)', function (obj) {
            var is_topCode = obj.elem.checked ? 1 : 0;
            $.ajax({
                url: baseUrl + "/merchantGood/" + this.value,
                type: 'put',
                async: false,
                data: {
                    is_top: is_topCode,
                    key: saa_key
                },
                headers: headers,
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);//关闭加载图标
                    if (res.status != 200) {
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg(sucMsg.put);
                    layer.close(openIndex);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                },
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                }
            })
        });

        //点击图片打开预览
        $(document).off('click', '.imgClickEvent').on('click', '.imgClickEvent', function () {
            imgClickEvent(this);
        })

        $("body").on('click', '.checkbox', function (event) {
            event.preventDefault();
            $(this).toggleClass('on');
        });

        // //上架下架筛选点击事件
        // $(document).off('change', '#status').on('change', '#status', function () {
        //     console.log(this.value)
        //     var data = {};
        //     if (this.value === '1') {
        //         data.status = '1';
        //     } else if (this.value === '0') {
        //         data.status = '0';
        //     }
        //     arr = {
        //         type: 'get',
        //         method: url,//必传参
        //         data: data
        //     };
        //     var data = getAjaxReturnKey(arr);
        //     dataMosaic(data.data);
        //     getPage();
        // })

    })
    exports('goods/list', {})
});
