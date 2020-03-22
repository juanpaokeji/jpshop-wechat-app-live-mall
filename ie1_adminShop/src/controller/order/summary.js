/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/10/31  一直在更新，时间随时修改
 * js 订单概述
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var saa_key = sessionStorage.getItem('saa_key');

        var url = baseUrl + "/merchantOrderSummary";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var xData = [];
        var yDataNum = [];
        var yDataPrice = [];
        $.ajax({
            url: url + key,
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
                layer.close(loading);//关闭加载图标
                if (res.status != 200) {
                    if (res.status != 204) {
                        layer.msg(res.message);
                    }
                    return false;
                }
                layer.close(openIndex);
                if (!res.data) {
                    xData = [];
                    yDataNum = [];
                    yDataPrice = [];
                    return;
                }

                $('.order').html(res.data.total.order);
                $('.payment').html(res.data.total.payment);
                $('.delivery').html(res.data.total.delivery);
                $('.evaluate').html(res.data.total.evaluate);
                $('.safeguardingRights').html(res.data.total.safeguardingRights);
                $('.todayOrder').html(res.data.total.todayOrder);
                $('.todayPrice').html(res.data.total.todayPrice);
                xData = res.data.day;
                yDataNum = res.data.num;
                yDataPrice = res.data.price;

                xData.reverse();
                yDataNum.reverse();
                yDataPrice.reverse();
            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);
            }
        })

        // 基于准备好的dom，初始化echarts实例
        var myChartNum = echarts.init(document.getElementById('mainNum'));

        // 指定图表的配置项和数据
        var optionNum = {
            title: {
                text: '订单数量',
            },
            tooltip: {
                trigger: 'axis' //显示对比线
            },
            xAxis: {
                boundaryGap: false,
                data: xData
            },
            yAxis: {
                minInterval: 1
            },
            series: [{
                name: '订单数量',
                type: 'line',
                data: yDataNum
            }],
            color: '#61a0a8',
        };

        // 使用刚指定的配置项和数据显示图表。
        myChartNum.setOption(optionNum);


        // 基于准备好的dom，初始化echarts实例
        var myChartPrice = echarts.init(document.getElementById('mainPrice'));

        // 指定图表的配置项和数据
        var optionPrice = {
            title: {
                text: '订单金额',
            },
            tooltip: {
                trigger: 'axis' //显示对比线
            },
            xAxis: {
                boundaryGap: false,
                data: xData
            },
            yAxis: {
                minInterval: 1
            },
            series: [{
                name: '订单金额',
                type: 'line',
                data: yDataPrice
            }],
            color: '#61a0a8',
        };

        // 使用刚指定的配置项和数据显示图表。
        myChartPrice.setOption(optionPrice);

    })
    exports('order/summary', {})
});
