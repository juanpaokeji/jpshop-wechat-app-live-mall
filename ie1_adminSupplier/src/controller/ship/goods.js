/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/5/11
 * js 供货商商品列表
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laypage'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var laypage = layui.laypage; //分页
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');
        var add_edit_type;//弹窗类型 新增 编辑
        var operationId;//当前操作 id ,编辑时可用到
        var arr, res;
        form.render();

        /*diy设置开始*/
        //页面不同属性
        var arr = {};
        var url = "supplierGoods";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var tabPage = '';
        var pageLimit = 10;
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
                url: baseUrl + '/supplierShopExpressTemplate?status=1',
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
                    location.hash = '/ship/add';
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
            location.hash = '/ship/add';
        });

        //删除按钮
        $(document).off('click', '.btn2').on('click', '.btn2', function () {
            var id = $(this).data("id");
            layer.confirm('确定要将这个商品移到回收站么?', function (index) {
                layer.close(index);
                $.ajax({
                    url: baseUrl + '/supplierGoods/' + id,
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
                            layer.msg(res.message);
                            return false;
                        }
                        layer.msg(sucMsg.delete);
                        dataMosaic(getList().data);
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
            layer.open({
                type: 1,
                title: '商品二维码',
                content: '<div id="qrcode"></div>',
                shade: 0.3,
                shadeClose: true,
                offset: '200px',
                area: ['400px', '422px']
            })
            var qrcode = new QRCode('qrcode', {
                width: 400, height: 380,
            })
            qrcode.clear(); //goodsDetails?id='商品id'
            qrcode.makeCode(baseUrl + '/goodDetails?id=' + id);
        })

        //以下基本不动
        //加载列表
        tabPage = '1';
        var dataList = getList();
        shipClass();

        function getList() {
            arr = {
                type: 'get',
                method: url,//必传参
                data: {limit: pageLimit, page: tabPage},
            };
            var render = getAjaxReturn(arr);
            return render;
        }

        dataMosaic(dataList.data);
        var selectString = '';

        //列表拼接
        function dataMosaic(data) {
            var dataString = '';//需要拼接的字符串
            data && data.forEach(function (e) {
                //处理label
                var label = '';
                e.label.split(',').forEach(function (ev) {
                    label += ev + ' ';
                })
                //判断是否上架
                var shelves = '', type = '';
                if (e.status == 1) {
                    shelves = '<td><a style="cursor:pointer;" class="btn-switch on" data-startTime="' + e.start_time + '" data-id="' + e.id + '"></a></td>';
                    type = '<td><a style="cursor:pointer;" class="btn shelves">上架</a></td>'
                } else {
                    shelves = '<td><a style="cursor:pointer;" class="btn-switch" data-id="' + e.id + '"></a></td>';
                    type = '<td><a style="cursor:pointer;" class="btn shelves">下架</a></td>'
                }
                if (e.is_check === '0') {
                    shelves = '<td>审核中</td>';
                } else if (e.is_check === '1') {
                    shelves = '<td>审核成功</td>';
                } else if (e.is_check === '2') {
                    shelves = '<td>审核失败</td>';
                } else {
                    shelves = '<td>类型错误</td>';
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
                    '<td>' + label + '</td>' +
                    shelves +
                    '<td>' + e.create_time + '</td>' +
                    '<td>' +
                    '<a style="cursor:pointer;" class="btns btn1" data-id="' + e.id + '"></a>' +
                    // '<a style="cursor:pointer;" class="btns btn4" data-id="'+e.id+'"></a>'+
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
                    is_page = 1;
                    tabPage = obj.curr;
                    //首次不执行
                    if (!first) {
                        dataMosaic(getList().data);
                    }
                }
            });
        }

        //获取商品的分类
        function shipClass() {
            arr = {
                method: 'supplierCategoryTypeMini',
                type: 'get'
            };
            res = getAjaxReturn(arr);
            if (res && res.data) {
                layer.close(loading);
                var merchantShopCategoryList = [];
                res.data && res.data.forEach(function (e) {
                    if (e.sub && e.sub.length > 0) {
                        e.sub.forEach(function (ev) {
                            selectString += '<option value="' + ev.id + '">' + ev.name + '</option>';
                            merchantShopCategoryList.push(ev);
                        })
                    }
                })
                $(".select").append(selectString);
                //merchantShopCategory = merchantShopCategoryList;
            }
        }

        //搜索按钮
        $(document).off('click', '.find').on('click', '.find', function () {
            arr = {
                method: url + '?limit=' + pageLimit + '&page=' + tabPage,
                type: 'get',
                data: {id: $(".selectId").val(), m_category_id: $(".select").val()},
            };
            var searchData = getAjaxReturnKey(arr);
            dataMosaic(searchData.data);
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
            arr = {
                method: 'supplierGoods/' + $(this).data('id'),
                type: 'put',
                data: {status: status, start_time: start_time}
            };
            res = getAjaxReturn(arr);
            if (res && res.data) {
                if (that.attr("class") != 'btn-switch') {
                    that.removeClass("on")
                    that.parent().prev().prev().children().text('下架')
                } else {
                    that.addClass("on")
                    that.parent().prev().prev().children().text('上架')
                }
                layer.msg(sucMsg.put);
                layer.close(openIndex);
            }
        });

        //修改是否推荐
        form.on('switch(is_topTpl)', function (obj) {
            var is_topCode = obj.elem.checked ? 1 : 0;
            $.ajax({
                url: baseUrl + "/supplierGoods/" + this.value,
                type: 'put',
                async: false,
                data: {is_top: is_topCode},
                headers: headers,
                success: function (res) {
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    layer.close(loading);//关闭加载图标
                    if (res.status != 200) {
                        layer.msg(res.message);
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
    })
    exports('ship/goods', {})
});
