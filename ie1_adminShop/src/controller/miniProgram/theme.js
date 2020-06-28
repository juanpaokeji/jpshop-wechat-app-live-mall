/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/11/16
 * 小程序 主题配色
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
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var saa_key = sessionStorage.getItem('saa_key');
        form.render();

        var click_span = this;//当前点击图标中需要保存数据的span
        var default_color = '#fff';
        var choicePageUrlOpenIndex;//定义弹出层，方便关闭

        var filePut = '';//图标base64图片
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

        var filePutSelection = '';//选中状态图标base64图片
        $("#addImgPutSelection").change(function () {//加载图片至img
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    filePutSelection = e.target.result;
                    $("#imageSelection").attr("src", e.target.result);
                };
            }
            file = null;
        });

        //默认加载颜色选择器
        $(document).ready(function () {
            $('#bottom_text_colorpicker').farbtastic('#bottom_text');//底部文字颜色绑定
            $('#text_selection_colorpicker').farbtastic('#text_selection');//文字选中颜色绑定
            // $('#text_selection_form_colorpicker').farbtastic('#text_selection_form');//弹窗文字选中颜色绑定
            // $('#text_un_selection_form_colorpicker').farbtastic('#text_un_selection_form');//弹窗文字未选中颜色绑定

            //鼠标任意点击事件，如果找到两个需要显示颜色选择器的则执行，否则不执行
            document.onclick = function (e) {
                $('.colorpicker').hide();
                if (e.target.id === 'bottom_text') {
                    $('#bottom_text_colorpicker').show();
                } else if (e.target.id === 'text_selection') {
                    $('#text_selection_colorpicker').show();
                }
                // else if (e.target.id === 'text_selection_form') {
                //     $('#text_selection_form_colorpicker').show();
                // } else if (e.target.id === 'text_un_selection_form') {
                //     $('#text_un_selection_form_colorpicker').show();
                // }
            }
        });

        //获取鼠标移入移出事件
        function getMouseEnter() {
            //底部导航图标，鼠标移入事件
            $('.icon_img').mouseenter(function () {
                $(this).find('.icon_img_operation').show();
            })

            //底部导航图标，鼠标移出事件
            $('.icon_img').mouseleave(function () {
                $(this).find('.icon_img_operation').hide();
            })
        }

        getMouseEnter();

        var url = baseUrl + '/merchantTheme';
        var key = '?key=' + saa_key + '&type=mini';
        $.ajax({
            url: url + key,
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
                $("input[name='theme_text'][value='" + data.theme_text + "']").prop("checked", true);//主题文字颜色
                $("input[name='theme'][value='" + data.theme + "']").prop("checked", true);//主题颜色

                //底部导航图标
                var navigation = data.navigation;
                if (typeof navigation === 'string') {
                    layer.msg('底部导航图标格式错误', {icon: 1, time: 2000});
                    return;
                }
                if (navigation && navigation.length > 0) {
                    var navi_len = navigation.length;
                    //先将默认显示的两个删除
                    $('.icon_img_delete').each(function () {
                        $(this).remove();
                    })
                    //循环添加导航图标
                    var navi_span;
                    var navi_name;
                    var navi_file_put;
                    for (var i = 0; i < navi_len; i++) {
                        navi_name = navigation[i].name;
                        navi_file_put = navigation[i].filePut;
                        navi_span = JSON.stringify(navigation[i]);
                        $('.add_icon').parent().parent().before(setIcon(navi_span, navi_file_put, navi_name));
                    }
                }

                //底部文字和文字选中颜色
                if (data.bottom_text !== '') {
                    $('#bottom_text').val(data.bottom_text);
                    $('#bottom_text').css('background-color', data.bottom_text);
                }
                if (data.text_selection !== '') {
                    $('#text_selection').val(data.text_selection);
                    $('#text_selection').css('background-color', data.text_selection);
                }

                //获取鼠标移入移出事件
                getMouseEnter();
            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);
            }
        })

        //底部导航图标添加事件
        $(document).off('click', 'add_idon').on('click', '.add_icon', function () {
            //需要判断当前图片是否已经有5个，最多5个，超出提示
            var icon_num = 0;
            $('.icon_img').each(function () {
                icon_num++;
            })
            if (icon_num <= 5) {
                $(this).parent().parent().before(getIcon());
            } else {
                layer.msg('导航图标最多5个', {icon: 1, time: 1000});
            }
            //获取鼠标移入移出事件
            getMouseEnter();
        })

        //点击底部导航图标中的编辑按钮执行事件
        $(document).off('click', '.theme_edit').on('click', '.theme_edit', function () {
            click_span = $(this).parent().parent().children(":first")[0];
            //每次打开需要重置form标签
            $("#add_edit_form")[0].reset();//表单重置
            filePut = '';
            $('#image').attr('src', '');
            filePutSelection = '';
            $('#imageSelection').attr('src', '');
            // $('#text_selection_form').val(default_color);
            // $('#text_selection_form').css('background-color', default_color);
            // $('#text_un_selection_form').val(default_color)
            // $('#text_un_selection_form').css('background-color', default_color);
            //判断span中是否有值，有就存入指定标签
            var s_value = $(click_span).text();
            if (Trim(s_value) != '') {
                //如果值存在，则将值设置到form表单中
                //获取的时候需要将字符串转对象
                s_value = JSON.parse(s_value);
                $('input[name=name]').val(s_value.name);
                $('#image').attr('src', s_value.filePut);
                filePut = s_value.filePut;
                $('#imageSelection').attr('src', s_value.filePutSelection);
                filePutSelection = s_value.filePutSelection;
                // $('#text_selection_form').val(s_value.text_selection_form);
                // $('#text_selection_form').css('background-color', s_value.text_selection_form);
                // $('#text_un_selection_form').val(s_value.text_un_selection_form)
                // $('#text_un_selection_form').css('background-color', s_value.text_un_selection_form);
                //弹出窗页面保存的四个值
                $('input[name=choice_page_name_view]').val(s_value.choice_page_name_view);
                $('input[name=choice_page_type]').val(s_value.choice_page_type);
                sessionStorage.setItem('choice_page_type', s_value.choice_page_type);//将类型存入，方便选择链接页面使用
                $('input[name=choice_page_name]').val(s_value.choice_page_name);
                $('input[name=choice_app_id]').val(s_value.choice_app_id);
                $('input[name=choice_page_url]').val(s_value.choice_page_url);
            }
            openIndex = layer.open({
                type: 1,
                title: '导航菜单编辑',
                content: $('#add_edit_form'),
                shade: 0.1,
                offset: '100px',
                area: ['400px', 'auto'],
                success: function (i, j) {
                    if (i.length === 0) {
                        $("#layui-layer-shade" + j).remove();
                    }
                },
                cancel: function () {
                    $('#add_edit_form').hide();
                    $(".layui-layer-shade").remove();
                }
            })
        })

        //点击底部导航图标中的删除按钮执行事件
        $(document).off('click', '.theme_delete').on('click', '.theme_delete', function () {
            var that = this;
            var icon_num = -1;
            $('.icon_img').each(function () {
                icon_num++;
            })
            if (icon_num > 2) {
                layer.confirm('确定要删除该导航图标吗？', function (index) {
                    layer.close(index);
                    $(that).parent().parent().remove();
                })
            } else {
                layer.msg('导航图标至少2个', {icon: 1, time: 1000});
            }
        })

        //点击底部导航图标弹出窗 选择页面按钮执行事件
        $(document).off('click', '.choice_page').on('click', '.choice_page', function () {
            //设置需要选择链接的类型
            sessionStorage.setItem('choice_url_type', 'mini');
            //加载选择链接的页面，该页面为通用链接
            //选择链接页面如果需要有默认值，则在此处的url上传参，并在选择链接页面获取，该功能暂不需要
            $('#choicePageUrl').load(
                './src/views/choicePageUrl.html',
                function () {
                    choicePageUrlOpenIndex = layer.open({
                        type: 1,
                        title: '选择跳转页面',
                        content: $('#choicePageUrl'),
                        shade: 0.1,
                        offset: '100px',
                        area: ['50vw', '35vw'],
                        success: function (i, j) {
                            if (i.length === 0) {
                                $("#layui-layer-shade" + j).remove();
                            }
                        },
                        cancel: function () {
                            $('#choicePageUrl').hide();
                        }
                    })
                }
            );
        })

        //点击底部导航图标弹出窗 确认按钮执行事件
        form.on('submit(form_sub)', function () {
            if (Trim(filePut) === '') {
                layer.msg('请选择图标', {icon: 1, time: 1000});
                return;
            } else if (Trim(filePutSelection) === '') {
                layer.msg('请选择选中状态图标', {icon: 1, time: 1000});
                return;
            }
            if ($('input[name=mini_page_name_view]').val() == '') {
                layer.msg('请选择页面', {icon: 1, time: 1000});
                return;
            }
            layer.close(openIndex);
            var save_menu_arr = {};
            save_menu_arr.name = $('input[name=name]').val();
            save_menu_arr.filePut = filePut;
            save_menu_arr.filePutSelection = filePutSelection;
            // save_menu_arr.text_selection_form = $('#text_selection_form').val();
            // save_menu_arr.text_un_selection_form = $('#text_un_selection_form').val();
            save_menu_arr.choice_page_name_view = $('input[name=choice_page_name_view]').val();
            save_menu_arr.choice_page_type = $('input[name=choice_page_type]').val();
            save_menu_arr.choice_page_name = $('input[name=choice_page_name]').val();
            save_menu_arr.choice_app_id = $('input[name=choice_app_id]').val();
            save_menu_arr.choice_page_url = $('input[name=choice_page_url]').val();
            //需要将对象转为字符串存入span标签中
            $(click_span).text(JSON.stringify(save_menu_arr));
            //将图标和名称显示到页面
            $($(click_span).parent().find('img')[0]).attr('src', filePut);
            $($(click_span).parent().find('span')[1]).text($('input[name=name]').val());
            $("#add_edit_form")[0].reset();//表单重置
            $('#add_edit_form').hide();
        });

        //监听提交
        form.on('submit(sub)', function () {
            //获取底部导航对应的字符串数组
            var navigation = [];
            $('.save_value').each(function () {
                var save_text = $(this).text();
                if (Trim(save_text) !== '') {
                    navigation.push(save_text);
                }
            })
            if (navigation.length < 2) {
                layer.msg('请至少设置两个底部导航图标', {icon: 1, time: 1000});
                return;
            }
            var subData = {
                theme_text: $('input[name=theme_text]:checked').val(),
                theme: $('input[name=theme]:checked').val() || $('input[name=theme]:checked').next().val(),
                navigation: navigation,
                bottom_text: $('#bottom_text').val(),
                text_selection: $('#text_selection').val(),
                key: saa_key,
                type: 'mini'
            };
            $.ajax({
                url: url,
                type: 'put',
                data: subData,
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
                    layer.msg(res.message, {icon: 1, time: 1000});
                    if (res.status != 200) {
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.close(openIndex);
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

    });

    exports('miniProgram/theme', {})
});

function getIcon() {
    return '                    <div class="icon_img" ondrop="drop(event,this)" ondragover="allowDrop(event)" draggable="true" ondragstart="drag(event, this)">\n' +
        '                        <span class="save_value" style="display: none;"></span>\n' +
        '                        <img src="./src/images/add_icon.png"/><br/>\n' +
        '                        <span>未设置</span>\n' +
        '                        <div class="icon_img_operation" style="display: none;">\n' +
        '                            <a class="theme_edit" href="javascript:void(0)">编辑</a>\n' +
        '                            <a class="theme_delete" href="javascript:void(0)">删除</a>\n' +
        '                        </div>\n' +
        '                    </div>';
}

function setIcon(span_value, img_src, name) {
    return '                    <div class="icon_img" ondrop="drop(event,this)" ondragover="allowDrop(event)" draggable="true" ondragstart="drag(event, this)">\n' +
        '                        <span class="save_value" style="display: none;">' + span_value + '</span>\n' +
        '                        <img src="' + img_src + '"/><br/>\n' +
        '                        <span>' + name + '</span>\n' +
        '                        <div class="icon_img_operation" style="display: none;">\n' +
        '                            <a class="theme_edit" href="javascript:void(0)">编辑</a>\n' +
        '                            <a class="theme_delete" href="javascript:void(0)">删除</a>\n' +
        '                        </div>\n' +
        '                    </div>';
}

//以下方法为移动菜单需要
var srcdiv = null;
var temp = null;

function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev, divdom) {
    srcdiv = divdom;
    temp = divdom.innerHTML;
}

function drop(ev, divdom) {
    ev.preventDefault();
    if (!srcdiv || !divdom) {
        return;
    }
    if (srcdiv != divdom) {
        srcdiv.innerHTML = divdom.innerHTML;
        divdom.innerHTML = temp;
    }
    return false;
}
