/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/2/13
 * 自定义版权
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var openIndex;//定义弹出层，方便关闭
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
        //刷新页面时显示对应的一级和二级菜单
        var href = window.location.href;
        href = href.split('#');

        if (href.length == 2) {
            href = href[1].substr(1, href[1].length);
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
        }
        form.render();

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }

        //页面不同属性
        var ajax_method = 'merchantUnits';
        var route = 'copyright';

        var operationId;
        //获取版权状态
        var copyright_status = 0;
        arr = {
            method: 'merchantCopyright',
            type: 'get'
        };
        res = getAjaxReturnKey(arr);
        if (res.data.copyright !== '1') {
            $('.copyright_li').remove();
            layer.msg('获取页面错误，没有该页面', {icon: 1, time: 2000});
            return false;
        } else {
            copyright_status = 1;
        }

        //当自定义版权开启，获取版权配置
        if (copyright_status === 1) {
            //获取版权配置
            arr = {
                method: ajax_method,
                type: 'get',
                data: {route: route}
            };
            res = getAjaxReturnKey(arr);
            if (res && res.data) {
                operationId = res.data.id;
                $("#image").append('<img src="' + res.data.config + '" width="200px" height="100px">');
            }
        }

        //执行版权编辑
        form.on('submit(sub)', function () {
            arr = {
                method: ajax_method + '/' + operationId,
                type: 'put',
                data: {
                    route: route,
                    pic_url: $('#image img').attr('src')
                }
            };
            res = getAjaxReturnKey(arr);
            if (res) {
                layer.msg('编辑成功');
                layer.close(openIndex);
            }
        });

        //上传图片现方法
        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '.putAddBtn').on('click', '.putAddBtn', function () {
            sessionStorage.setItem('images_common_div', '#image');
            sessionStorage.setItem('images_common_div_info', '<img width="200px" height="100px">');
            sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
            sessionStorage.setItem('images_common_type_append', 'cover');//设置类型为覆盖 cover 覆盖原图片 add 添加新图片
            images_open_index_fun();
        });

    });
    exports('voucher/copyright', {})
});
