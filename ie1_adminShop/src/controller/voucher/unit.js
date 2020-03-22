/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/5/17 9:50
 * 员工管理
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
        var baseUrl = setter.baseUrl;
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var successMsg;//成功提示，仅用于判断新增编辑
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');

        //营销菜单获取已购买的插件菜单，插件菜单必加
        // getVoucherMenu();

        //页面不同属性
        var url = baseUrl + "/merchantUnit";//当前页面主要使用 url
        var key = '?key=' + saa_key;

        //删除默认追加的菜单和已购买插件
        $('.default_delete').each(function () {
            $(this).remove();
        })

        //获取当前应用已购买插件
        $.ajax({
            url: url + key,
            type: 'get',
            async: false,
            headers: headers,
            beforeSend: function () {
                loading = layer.load(loadType, loadShade);//显示加载图标
            },
            success: function (res) {
                layer.close(loading);
                if (res.status == timeOutCode) {
                    layer.msg(timeOutMsg);
                    admin.exit();
                    return false;
                }
                if (res.status == 500) {
                    layer.msg(res.message);
                    return false;
                }

                if (res.status == 204) {
                    $('.unit_div').show();//将默认隐藏并且未删除的插件显示出来，最后执行
                    return false;
                }

                var data = res.data;
                var routes = '';//保存到缓存中的路由数据，以便在其他页面刷新时用
                var titles = '';//保存到缓存中的插件标题数据，以便在其他页面刷新时用
                for (var i = 0; i < data.length; i++) {
					var title = data[i].title;//插件标题
                    var route = data[i].route;//插件路由
                    titles += title + ',';
                    routes += route + ',';
                    var expire_time = data[i].expire_time;//1未过期 0已过期
                    if (expire_time) {
                        $('.units_ul').append('<li class="default_delete"><a lay-href="voucher/' + route + '">' + title + '</a></li>');
						$('.' + route + '_div').remove();
                        $('.purchase').append(getAlreadyPurchasedDiv(title, route));
                    }
                }
                sessionStorage.setItem('titles', titles);
                sessionStorage.setItem('routes', routes);
                $('.unit_div').show();//将默认隐藏并且未删除的插件显示出来，最后执行

            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);
            }
        })

        //已购买插件点击事件
        $(document).off('click', '.purchase_click').on('click', '.purchase_click', function () {
            var href = 'voucher/' + $(this).attr('name');
            location.hash = '/voucher/' + $(this).attr('name');

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
        })

        //未购买插件点击事件
        var save_res;
        $(document).off('click', '.no_purchase_click').on('click', '.no_purchase_click', function () {
            var route = $(this).attr('name');
            layer.confirm('是否购买该插件?', function (index) {
                layer.close(index);
                $.ajax({
                    url: baseUrl + '/merchantUnitPay',
                    data: {
                        key: saa_key,
                        route: route
                    },
                    type: 'post',
                    async: false,
                    headers: headers,
                    beforeSend: function () {
                        loading = layer.load(loadType, loadShade);//显示加载图标
                    },
                    success: function (res) {
						console.log(res)
                        layer.close(loading);
                        if (res.status == timeOutCode) {
                            layer.msg(timeOutMsg);
                            admin.exit();
                            return false;
                        }
                        if (res.status != 200) {
                            if (res.status != 204) {
                                layer.msg(res.message);
                            }
                            return false;
                        }
                        $('.wxCode').attr('src', res.data.data);
                        save_res = res.data;
                        openIndex = layer.open({
                            type: 1,
                            title: '微信二维码',
                            content: $('#wxCode'),
                            shade: 0,
                            offset: '100px',
                            area: ['400px', 'auto'],
                            cancel: function () {
                                $('#wxCode').hide();
                            }
                        })
                    },
                    error: function () {
                        layer.msg(errorMsg);
                        layer.close(loading);
                    }
                })
            })
        })

        //点击完成支付执行事件
        form.on('submit(sub)', function () {
            $.ajax({
                url: baseUrl + '/merchantUnitPay?out_trade_no=' + save_res.out_trade_no,
                type: 'get',
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    layer.close(loading);
                    if (res.status == 200 && res.data.trade_state == 'SUCCESS') {
                        layer.close(openIndex);
                        layer.msg('付款成功', {icon: 1, time: 1000}, function () {
                            location.hash = '/voucher/unit';
                            location.reload();
                        })
                    } else {
                        layer.confirm('未完成付款，确定取消该订单吗？', function (index1) {
                            layer.confirm('订单取消后无法恢复，是否确定取消该订单？', function (index2) {
                                layer.close(openIndex);
                                layer.close(index2);
                                layer.close(index1);
                                $('#wxCode').hide();
                            });
                        });
                    }
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                    $('#wxCode').hide();
                }
            })
        })

    })
    exports('voucher/unit', {})
});

//获取已购买的插件div
function getAlreadyPurchasedDiv(title, route) {
    // return '<div class="layui-col-md2 unit_div default_delete">\n' +
    //     '            <span class="layui-col-md12 unit_title">' + title + '</span>\n' +
    //     '            <a class="purchase_click" name="' + route + '" href="javascript:void(0)">\n' +
    //     '                <img src="http://juanpao999-1255754174.cos.cn-south.myqcloud.com/merchantapp%2F2019%2F02%2F01%2F15490128485c540f701faf6.png"/>\n' +
    //     '            </a>\n' +
    //     '        </div>';
	return '<div class="item purchase_click unit_div" name="' + route + '">'+
				'<a class="btn-edit"><img src="./group/images/icon-edit-blue.png" alt=""></a>'+
				'<h3>' + title + '</h3>'+
				'<div class="txt">这一块做商品介绍,这一块做商品介绍,这一块做商品介绍...</div>'+
				'<div class="btm">'+
					'<a class="btn btn-green">进 入</a>'+
				'</div>'+
			'</div>';
}
