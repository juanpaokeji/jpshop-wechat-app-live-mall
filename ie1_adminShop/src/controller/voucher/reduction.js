/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/12/5
 * 商城后台修改创建应用时的信息
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter', 'laydate'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var sucMsg = setter.successMsg;//成功提示 数组
        var openIndex;//定义弹出层，方便关闭
        var layDate = layui.laydate;
        var saa_id = sessionStorage.getItem('saa_id');
        var saa_key = sessionStorage.getItem('saa_key');
        var arr, res;

        //进入营销菜单必须执行方法，获取该应用的自定义版权状态，如果为1则显示自定义版权，为0则需要隐藏
        //之前写在layout里，太消耗性能，所以写在营销菜单下的所有页面里
        arr = {
            method: 'merchantCopyright',
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res && res.data && res.data.copyright && res.data.copyright === '1') {
            if ($('.copyright_li').length <= 0) {
                $('.voucher_ul').append('<li class="copyright_li"><a lay-href="voucher/copyright">自定义版权</a></li>');
            }
        } else {
            $('.copyright_li').remove();
        }

        //获取应用信息
        arr = {
            method: 'merchantAppInfo/' + saa_id,
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res) {
            if (res.data.reduction_info && res.data.reduction_info.is_reduction == 1) {
                $("input[name=is_reduction]").prop('checked', true);
            } else {
                $("input[name=is_reduction]").removeAttr('checked');
            }
            var achieve = res.data.reduction_info.reduction_achieve
            var decrease = res.data.reduction_info.reduction_decrease
            var shipping = res.data.reduction_info.free_shipping
            $("input[name=reduction_achieve]").val(achieve[0]);
            $("input[name=reduction_decrease]").val(decrease[0]);
            var checked = '';
            if (shipping[0] == 'true'){
                $("input[name=free_shipping]").prop('checked', true);
            }
            if (achieve.length>1){
                for (var i = 1; i < achieve.length; i++){
                    if (shipping[i] == 'true'){
                        checked = 'checked';
                    } else {
                        checked = '';
                    }
                    $('.reduction_list').append(
                        '<div class="layui-form-item">\n' +
                        '   <label class="layui-form-label"></label>\n' +
                        '   <div class="layui-input-inline" style="width: 20px;"> 满 </div>\n' +
                        '   <div class="layui-input-inline" style="width: 80px;">\n' +
                        '       <input type="text" value="' + achieve[i] + '" class="layui-input" lay-verify="required" name="reduction_achieve">\n' +
                        '   </div>\n' +
                        '   <div class="layui-input-inline" style="width: 20px;"> 减 </div>\n' +
                        '   <div class="layui-input-inline" style="width: 80px;">\n' +
                        '       <input type="text" value="' + decrease[i] + '" class="layui-input" lay-verify="required" name="reduction_decrease">\n' +
                        '   </div>\n' +
                        '   <div class="layui-input-inline" style="width: 80px;">\n' +
                        '      <input type="checkbox" name="free_shipping" '+ checked +' title="包邮">\n' +
                        '   </div>' +
                        '   <a href="javascript: void(0)" class="reduction_del" style="color: red;">删除</a>\n' +
                        '</div>\n'
                    );
                }
            }
            form.render();
        }

        //设置资料
        form.on('submit(setInfo)', function () {

            var subData = {}
            //满减数据
            var reduction_achieve = [];
            $('input[name=reduction_achieve]').each(function (i, j) {
                reduction_achieve.push(j.value);
            });
            var reduction_decrease = [];
            $('input[name=reduction_decrease]').each(function (i, j) {
                reduction_decrease.push(j.value);
            });
            var free_shipping = [];
            $('input[name=free_shipping]').each(function (i, j) {
                free_shipping.push(j.checked);
            });
            subData.reduction_info = {
                is_reduction: $('input[name=is_reduction]:checked').val() ? 1 : 0,
                reduction_achieve:reduction_achieve,
                reduction_decrease:reduction_decrease,
                free_shipping:free_shipping,
            }

            //提交修改
            arr = {
                method: 'merchantAppInfos/' + saa_id,
                type: 'put',
                data: subData
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg(sucMsg.put, {icon: 1, time: 2000});
                layer.close(openIndex);
            }
        });

        //添加满减规则
        $(document).off('click', '.reduction_add').on('click', '.reduction_add', function () {
            $('.reduction_list').append(
                '<div class="layui-form-item">\n' +
                '   <label class="layui-form-label"></label>\n' +
                '   <div class="layui-input-inline" style="width: 20px;"> 满 </div>\n' +
                '   <div class="layui-input-inline" style="width: 80px;">\n' +
                '       <input type="text" class="layui-input" lay-verify="required" name="reduction_achieve">\n' +
                '   </div>\n' +
                '   <div class="layui-input-inline" style="width: 20px;"> 减 </div>\n' +
                '   <div class="layui-input-inline" style="width: 80px;">\n' +
                '       <input type="text" class="layui-input" lay-verify="required" name="reduction_decrease">\n' +
                '   </div>\n' +
                '   <div class="layui-input-inline" style="width: 80px;">\n' +
                '      <input type="checkbox" name="free_shipping" title="包邮">\n' +
                '   </div>' +
                '   <a href="javascript: void(0)" class="reduction_del" style="color: red;">删除</a>\n' +
                '</div>\n'
            );
            form.render();
        });

        //删除满减规则
        $(document).off('click', '.reduction_del').on('click', '.reduction_del', function () {
            $(this).parent().remove();
        });

    });

    exports('voucher/reduction', {})
});