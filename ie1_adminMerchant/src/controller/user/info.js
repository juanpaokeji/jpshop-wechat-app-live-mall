/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/12/24  一直在更新，时间随时修改
 * js 商户信息修改
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var arr, res;

        //获取该商户是否绑定手机，如果未绑定则提示绑定
        var is_bind_phone = sessionStorage.getItem('is_bind_phone');
        if (!is_bind_phone) {
            arr = {
                method: 'checkPhone',
                type: 'get',
                data: {phone: localStorage.getItem('name')}
            };
            res = getAjaxReturn(arr);
            if (res && res.status === 200) {
                is_bind_phone = '1';
                sessionStorage.setItem('is_bind_phone', '1');
            } else {
                is_bind_phone = '0';
            }
        }
        if (is_bind_phone === '0') {
            $('.bind_phone').show().html('绑定手机');
        } else {
            $('.bind_phone').remove();
        }

        arr = {
            method: 'merchantInfo',
            type: 'get'
        };
        res = getAjaxReturn(arr);
        if (res && res.data && res.data.phone) {
            $(".name").html(res.data.phone);
            localStorage.setItem('name', res.data.phone);
        }

    });
    exports('user/info', {})
});
