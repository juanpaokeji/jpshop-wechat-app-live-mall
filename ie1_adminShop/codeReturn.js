/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/9 10:10  一直在更新，时间随时修改
 * js model
 */

var num = 5;
var type = 2;//默认为公众号

layui.define(function (exports) {
    layui.use(['jquery'], function () {
        var $ = layui.$;
        var headers = {'Access-Token': layui.data('juanpao').access_token};
        var saa_key = sessionStorage.getItem('saa_key');
        var key = '?key=' + saa_key;

        var url = 'https://api.juanpao.com/wechat/officialAccount/openplat/callback';
        var str = window.location.search.substring(1);

        $.ajax({
            url: url + key + '&' + str,
            type: "get",
            headers: headers,
            success: function (res) {
                if (typeof res == 'string') {
                    res = eval('(' + res + ')');
                }
                if (res.status != '200') {
                    //回调失败给用户提示
                    $('.fail').show();
                } else {
                    $('.success').show();
                }
                if (!res.data) {
                    return false;
                }
                $('.jump').show();
                //res.data 1是小程序 2是公众号
                if (res.data == '1') {
                    type = 1;
                }
                console.log(res);
            },
            error: function () {
                $('.fail').show();
                $('.jump').show();
            }
        });
        num--;
        window.setInterval(refreshCount, 1000);
    });
    exports('codeReturn', {})
});

function refreshCount() {
    if (num === 0) {
        if (type == 1) {
            //跳转小程序授权页面
            window.location.href = '/adminShop/#/miniProgram/base';
        } else if (type == 2) {
            //跳转公众号授权页面
            window.location.href = '/adminShop/#/wechat/base';
        }
    }
    $('.num').text(num);
    num--;
}
