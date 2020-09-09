/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/2/16  一直在更新，时间随时修改
 * js 选择页面链接-通用
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'element'], function () {
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var element = layui.element;//选项卡
        var baseUrl = setter.baseUrl;
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var saa_key = sessionStorage.getItem('saa_key');
        var choice_url_type = sessionStorage.getItem('choice_url_type');//wechat 公众号 mini 小程序
        if (choice_url_type != 'wechat' && choice_url_type != 'mini') {
            layer.msg('您的打开方式错误', {icon: 1, time: 1000});
            return;
        }

        /*diy设置开始*/
        //页面不同属性
        var key = '?key=' + saa_key;

        //最终保存时用到的类型、名称和链接
        var choice_type = 1;//默认1 1常用链接 2商品分组 3商品
        var choice_name = '';
        var choice_url = '';

        //监听Tab切换
        element.on('tab(choicePageUrl)', function () {
            var tabId = this.getAttribute('lay-id');
            choice_type = tabId;
            if (tabId == '1') {
                getUrl('merchantThemeLink');
            } else if (tabId == '2') {
                getUrl('merchantCategoryTypeSub');
            } else if (tabId == '3') {
                getUrl('merchantGoods');
            } else if (tabId == '4') {
                getUrl('customLinks');
            }else if (tabId == '5') {
                getUrl('supplier');
            }
        });

        var link_list = [];
        getUrl('merchantThemeLink');//默认加载常用链接列表
        //常用链接列表
        function getUrl(type) {
            $('.choicePageUrl').empty();//每次请求清空
            //如果是自定义链接，则单独处理，否则请求后台获取数据
            if (type === 'customLinks') {
                // //不管是微信还是小程序，都需要添加标题
                // $('.choicePageUrl').append('<div class="layui-form-item">\n' +
                //     '                            <label style="width: 55px;" class="layui-form-label">链接标题</label>\n' +
                //     '                            <div class="layui-input-inline">\n' +
                //     '                                <input name="url_title" required lay-verify="required" placeholder="请输入链接标题" class="layui-input">\n' +
                //     '                            </div>\n' +
                //     '                        </div>');
                // if (choice_url_type == 'mini') {//如果是小程序，则添加小程序id
                //     $('.choicePageUrl').append('<div class="layui-form-item">\n' +
                //         '                            <label style="width: 55px;" class="layui-form-label">小程序id</label>\n' +
                //         '                            <div class="layui-input-inline">\n' +
                //         '                                <input name="app_id" required lay-verify="required" placeholder="请输入小程序id" class="layui-input">\n' +
                //         '                            </div>\n' +
                //         '                        </div>');
                // }
                //不管是微信还是小程序，都需要添加链接地址
                $('.choicePageUrl').append('<div class="layui-form-item">\n' +
                    '                            <label style="width: 55px;" class="layui-form-label">链接地址</label>\n' +
                    '                            <div class="layui-input-inline">\n' +
                    '                                <input name="url_link" required lay-verify="required" placeholder="请输入小程序内部链接地址" class="layui-input">\n' +
                    '                            </div>\n' +
                    '                        </div>');
                return;
            }
            var theme_url = '';
            if (type === 'merchantThemeLink') {
                theme_url = '&type=' + choice_url_type;
            }
            $.ajax({
                url: baseUrl + '/' + type + key + theme_url,
                type: 'get',
                async: false,
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    layer.close(loading);//关闭加载图标
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
                    if (!res.data) {
                        return false;
                    }
                    var data = res.data;
                    var i = 0;
                    var name = ''
                    var link_url = '';
                    var pic_url = '';
                    if (type == 'merchantThemeLink') {//常用链接
                        link_list = data;
                        data = data.often_link;
                        for (i = 0; i < data.length; i++) {
                            name = data[i].name;
                            link_url = data[i].url;
                            $('.choicePageUrl').append(getChoicePageUrlList(0, name, link_url));
                        }
                    } else if (type == 'merchantCategoryTypeSub') {//商品分组
                        for (i = 0; i < data.length; i++) {
                            name = data[i].name;
                            link_url = link_list.type_link + '?id=' + data[i].id + '&name=' + name;
                            pic_url = data[i].pic_url;
                            $('.choicePageUrl').append(getChoicePageUrlList(1, name, link_url, pic_url));
                        }
                    } else if (type == 'merchantGoods') {//商品
                        for (i = 0; i < data.length; i++) { 
                            name = data[i].name;
                            link_url = link_list.goods_link + '?id=' + data[i].id;
                            pic_url = data[i].pic_urls.split(',')[0];
                            var is_flash_sale = data[i].is_flash_sale;
                            $('.choicePageUrl').append(getChoicePageUrlListGoods(1, name, link_url, pic_url, is_flash_sale));
                        }
                    }else if(type == 'supplier'){//门店
                        
                        for(i= 0;i<data.length; i++){
                         name=data[i].name;
                         link_url='supplier/index/index'+ '?id='+data[i].id;
                         $('.choicePageUrl').append(getChoicePageUrlList(0, name, link_url));
                        }
                    }
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }

        //radio标签点击事件
        $(document).off('click', '.radio').on('click', '.radio', function () {
            choice_name = $(this).attr('title');
            choice_url = $(this).attr('value');
        })

        //点击底部导航图标弹出窗 确认按钮执行事件
        form.on('submit(choicePageUrlSub)', function () {
            var type_name = '';
            if(choice_type == '1' || choice_type == '2' ||choice_type == '3') {
                if (choice_type == '1') {
                    type_name = '常用链接';
                } else if (choice_type == '2') {
                    type_name = '商品分类';
                } else if (choice_type == '3') {
                    type_name = '商品';
                }
                $('input[name=choice_page_name]').val(choice_name);
                $('input[name=choice_page_name_view]').val(type_name + '--' + choice_name);
                $('input[name=choice_page_url]').val(choice_url);
            } else if(choice_type == '4') {
                type_name = '自定义链接';
                choice_name = $('input[name=url_title]').val();
                $('input[name=choice_page_name]').val(choice_name);//如果是微信，该值存空，是小程序，存小程序appid
                $('input[name=choice_page_name_view]').val(type_name + '--' + choice_name);
                $('input[name=choice_app_id]').val($('input[name=app_id]').val());
                $('input[name=choice_page_url]').val($('input[name=url_link]').val());
            }
            $('input[name=choice_page_type]').val(choice_type);
			
			sessionStorage.setItem('menuLink',JSON.stringify({choice_page_name_view:type_name + '--' + choice_name,choice_page_url:choice_type == '4'?$('input[name=url_link]').val():choice_url}))

            //获取最后打开的，也就是当前的弹窗id，通过layui自带关闭方法关掉，大坑，div remove只删掉了遮罩 !_!
            var div_id = $($('.layui-layer-shade')[$('.layui-layer-shade').length - 1]).attr('times');
            layer.close(div_id)
            $('#choicePageUrl').hide();
        });
		window.addEventListener("storage", function(e) {
			console.log(e)
		})
    })
    exports('choicePageUrl', {})
});

function getChoicePageUrlList(type, name, url, pic_url) {
    //type判断是否有图片
    var img = '';
    if (type) {
        img = '<img src="' + pic_url + '"/>\n';
    }
    return '                <li class="choicePageUrlLi">\n' +
        '                        <input class="radio" type="radio" name="choicePageUrl" value="' + url + '" title="' + name + '"/>\n' +
        img +
        '                        <span>' + name + '</span>\n' +
        '                    </li>';
}

//商品添加是否秒杀显示
function getChoicePageUrlListGoods(type, name, url, pic_url, is_flash_sale) {
    var flash = '&nbsp;&nbsp;&nbsp;&nbsp;';
    if (is_flash_sale == '1') {
        flash = '秒杀';
    }
    //type判断是否有图片
    var img = '';
    if (type) {
        img = '<img src="' + pic_url + '"/>\n';
    }
    return '                <li class="choicePageUrlLi">\n' +
        '                        <input class="radio" type="radio" name="choicePageUrl" value="' + url + '" title="' + name + '"/>\n' +
        img +
        '                        <span style="color: red;">' + flash + '</span>&nbsp;<span>' + name + '</span>\n' +
        '                    </li>';
}
