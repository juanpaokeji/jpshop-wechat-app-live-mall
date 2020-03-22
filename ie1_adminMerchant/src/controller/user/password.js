/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/12/24  一直在更新，时间随时修改
 * js 修改密码
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;

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
            $('.is_bind_phone').append('<label class="layui-form-label"> 绑定手机 </label>\n' +
                '                <div class="layui-input-inline">\n' +
                '                    <input name="phone" required lay-verify="required" placeholder="请输入姓名" autocomplete="off"\n' +
                '                           class="layui-input">\n' +
                '                </div>');
        } else {
            $('.is_bind_phone').empty();
        }

        //执行编辑商户密码
        form.on('submit(sub)', function () {
            var old_pw = $('input[name=old_pw]').val();
            if (old_pw !== sessionStorage.getItem('password')) {
                layer.msg('原密码错误', {icon: 1, time: 2000});
                return;
            }
            var pw = $('input[name=pw]').val();
            var confirm_pw = $('input[name=confirm_pw]').val();
            if (pw !== confirm_pw) {
                layer.msg('输入的新密码不一致', {icon: 1, time: 2000});
                return;
            }
            arr = {
                method: 'merchantInfo',
                type: 'put',
                data: {password: pw}
            };
            res = getAjaxReturn(arr);
            if (res) {
                layer.msg('密码修改成功，需要重新登录', {icon: 1, time: 2000}, function () {
                    layui.admin.exit();
                });
            }
        })

    });
    exports('user/password', {})
});
