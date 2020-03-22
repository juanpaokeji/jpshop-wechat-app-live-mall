/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/8/10  一直在更新，时间随时修改
 * js model
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var saa_key = sessionStorage.getItem('saa_key');
        var arr, res;

        //页面不同属性
        var url = baseUrl + "/merchantShopTotal";//当前页面主要使用 url
        var key = '?key=' + saa_key;

        var total_day = [];
        var total_month = [];

        // echarts 需要的参数
        var xData = [];
        var yDataTurnover = [];
        var yDataVisitor = [];
        var yDataVisit = [];

        arr = {
            method: 'miniProgram',
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data && res.data.merchantVersionNumber !== res.data.adminVersionNumber) {
            layer.confirm('小程序有更新版本啦，是否跳转上传发布页面？', {
                btn: ['确定', '取消'] //可以无限个按钮
            }, function () {
                location.hash = '/miniProgram/formal';
            }, function () {
                getIndexInfo();
            });
        } else {
            getIndexInfo();
        }

        function getIndexInfo() {
            $.ajax({
                url: url + key,
                type: "get",
                headers: headers,
                async: true,
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
                    if (res.status === 500) {
                        layer.msg(res.message);
                        return false;
                    }
                    if (res.status !== 200) {
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    var data = res.data;
                    //开始设置页面值
                    //设置今日营业额一栏
                    var today = data.today;
                    $('.today_turnover').text(today.today_turnover);
                    $('.today_visitor').text(today.today_visitor);
                    $('.today_order').text(today.today_order);
                    $('.today_average_price').text(today.today_average_price);
                    //设置近7天营业额一栏
                    var week = data.week;
                    $('.seven_day_turnover').text(week.seven_day_turnover);
                    $('.seven_day_visitor').text(week.seven_day_visitor);
                    $('.seven_day_order').text(week.seven_day_order);
                    $('.seven_day_average_price').text(week.seven_day_average_price);
                    //设置近30天营业额一栏
                    var month = data.month;
                    $('.thirty_days_turnover').text(month.thirty_days_turnover);
                    $('.thirty_days_visitor').text(month.thirty_days_visitor);
                    $('.thirty_days_order').text(month.thirty_days_order);
                    $('.thirty_days_average_price').text(month.thirty_days_average_price);
                    //设置待处理事项一栏
                    var matter = data.matter;
                    $('.un_shipped_order').text(matter.un_shipped_order);
                    $('.refund_order').text(matter.refund_order);
                    $('.warehouse_order').text(matter.warehouse_order);
                    //设置小程序二维码
                    $('#qr_code').attr('src', data.qcode);

                    //获取数据概览的数据
                    total_day = data.total_day;
                    total_month = data.total_month;
                    var h_num = total_day.h.length;
                    for (var i = 0; i < h_num; i++) {
                        total_day.h[i] += '时';
                    }
                    //默认显示时时
                    xData = total_day.h;
                    yDataTurnover = total_day.turnover;
                    yDataVisitor = total_day.visitor;
                    yDataVisit = total_day.visit;
                    // //如果需要默认显示按天，则取消注释以下代码，并注释时时代码
                    // xData = total_month.day;
                    // yDataTurnover = total_month.turnover;
                    // yDataVisitor = total_month.visitor;
                    // yDataVisit = total_month.visit;
                    getMyChart(xData, yDataTurnover, yDataVisitor, yDataVisit);//默认显示时时
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }


        // //点击进入客服
        // $(document).off('click', '.entering_customer_service').on('click', '.entering_customer_service', function () {
        //     var is_admin_login = localStorage.getItem('is_admin_login');
        //     var saa_key = sessionStorage.getItem('saa_key');
        //     var password = sessionStorage.getItem('password');//当前登陆人密码
        //     var username;
        //     if (is_admin_login) {
        //         username = saa_key;
        //     } else {
        //         username = localStorage.getItem('username');
        //     }
        //     // window.open(//本地
        //     //     "http://192.168.188.236:8080/admin/login/loading.html?u=" + username + '&p=' + password,
        //     //     "_blank"
        //     // );
        //     window.open(//线上
        //         "https://chat.juanpao.com/admin/login/loading.html?u=" + username + '&p=' + password,
        //         "_blank"
        //     );
        // })

        //点击跳转订单页
        $(document).off('click', '.dump_order').on('click', '.dump_order', function () {
            sessionStorage.setItem('order_list_tab_id', $(this).attr('data'));
            location.hash = '/order/list';
            location.reload();
        })

        //点击跳转等待上架宝贝页面
        $(document).off('click', '.dump_goods_recycleBin').on('click', '.dump_goods_recycleBin', function () {
            location.hash = '/goods/recycleBin';
            location.reload();
        })

        //点击进入面单打印系统
        $(document).off('click', '.go_to_print').on('click', '.go_to_print', function () {
            window.open(//线上
                document.location.protocol + '//' + window.location.host + '/adminPrint/#/print/list/key=' + saa_key,
                "_blank"
            );
        })

        // initEchart('always');

        function initEchart(data) {
            if (data === 'always') {
                xData = total_day.h;
                yDataTurnover = total_day.turnover;
                yDataVisitor = total_day.visitor;
                yDataVisit = total_day.visit;
            } else if (data === 'day') {
                xData = total_month.day;
                yDataTurnover = total_month.turnover;
                yDataVisitor = total_month.visitor;
                yDataVisit = total_month.visit;
            }
            getMyChart(xData, yDataTurnover, yDataVisitor, yDataVisit);
        }

        //选择时时或者按天 按钮点击事件
        $(document).off('click', '.time_type_change').on('click', '.time_type_change', function () {
            //修改选中样式
            $('.time_type_change').removeClass('onclick');
            $(this).addClass('onclick');
            //设置echarts数据
            var time_type = $(this).attr('data');
            initEchart(time_type);
        })

        // $('.news').empty().append('<h3>产品动态<a href="http://www.juanpao.com/news.html" target="_blank">更多</a></h3>');
        //加载产品动态列表
        // $.ajax({
        //     url: baseUrl + '/news?type=1',
        //     type: 'get',
        //     headers: headers,
        //     beforeSend: function () {
        //         loading = layer.load(loadType, loadShade);//显示加载图标
        //     },
        //     success: function (res) {
        //         if (res.status == timeOutCode) {
        //             layer.msg(timeOutMsg);
        //             admin.exit();
        //             return false;
        //         }
        //         layer.close(loading);//关闭加载图标
        //         if (res.status === 500) {
        //             layer.msg(res.message);
        //             return false;
        //         }
        //         var data = res.data;
        //         for (var i = 0; i < res.count; i++) {
        //             $('.news').append('<a href="http://www.juanpao.com/detail.html?id=' + data[i].id + '" target="_blank">' + data[i].title + '</a>');
        //         }
        //     },
        //     error: function () {
        //         layer.msg(errorMsg);
        //         layer.close(loading);
        //     }
        // })

        // 基于准备好的dom，初始化echarts实例
        function getMyChart(xd, yt, yd, ydv) {
            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '数据概览',
                },
                tooltip: {
                    trigger: 'axis' //显示对比线
                },
                xAxis: {
                    boundaryGap: false,
                    data: xd
                },
                yAxis: {
                    minInterval: 0.1
                },
                legend: {
                    left: 'center',
                    data: ['成交额', '访客', '访问量']
                },
                series: [{
                    name: '成交额',
                    type: 'line',
                    data: yt,
                    smooth: true,
                    itemStyle: {
                        lineStyle: {
                            color: '#f85959'
                        },
                        color: '#f85959'
                    }
                }, {
                    name: '访客',
                    type: 'line',
                    data: yd,
                    smooth: true,
                    itemStyle: {
                        lineStyle: {
                            color: '#feb822'
                        },
                        color: '#feb822'
                    }
                }, {
                    name: '访问量',
                    type: 'line',
                    data: ydv,
                    smooth: true,
                    itemStyle: {
                        lineStyle: {
                            color: '#009afe'
                        },
                        color: '#009afe'
                    }
                }],
            };
            var myChart = echarts.init(document.getElementById('main'));
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
        }

    })
    exports('overview/index', {})
});
