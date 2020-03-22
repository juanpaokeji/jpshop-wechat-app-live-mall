/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/10/29  一直在更新，日期随时修改
 * js 新增、编辑快递模板
 * 备注：  设置地区的时候，两种做法
 *          1：按照当前做法，每次点击选择，则隐藏当前选择，新增元素到右侧，功能删除
 *          2：左右都加载地区列表，左侧默认显示，右侧默认隐藏，当点击左侧选择时，当前点击的隐藏，右侧对应的显示
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
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
        var saa_key = sessionStorage.getItem('saa_key');
        var ajaxType;
        var expressId = sessionStorage.getItem('expressId');
        var isAreaEdit = 0;//判断是否为选择区域编辑
        form.render();
        getGroups();//由于每次进入页面设置的已加载被清空，所以每次加载页面就调一次接口

        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantShopExpressTemplate";//当前页面主要使用 url
        var key = '?key=' + saa_key;
        var choiceArea = [];//已选择的省
        /*diy设置结束*/

//可配送区域开始
        //添加可配送区域和运费点击事件
        $(document).off('click', '.add_new').on('click', '.add_new', function () {
            //1.遍历所有 class 为 allArea 的元素，将设置还原
            $('.right').empty();
            $('.allArea').each(function () {
                $(this).parent().show();
            })
            /*diy设置开始*/
            $("input[name='app_id']:eq(0)").prop("checked", true);//还原类型默认选中第一个
            form.render();//还原后需要重置表单
            /*diy设置结束*/
            choiceArea = [];//清空已选择的省

            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: $('#area_form'),
                shade: 0,
                offset: '100px',
                area: ['50vw', '60vh'],
                cancel: function () {
                    $('#area_form').hide();
                }
            })
        })

        //区域 选择 按钮 点击事件
        $(document).off('click', '.areaChoice').on('click', '.areaChoice', function () {
            var name = $(this).prev()[0].innerText;//获取当前点击的上一个兄弟元素的页面显示值
            choiceArea.push(name);
            $('.right').append('<div class="deleteArea"><span>' + name + '</span><span class="areaDelete">删除></span>></div>');
            $(this).parent().hide();
        })

        //区域 删除 按钮 点击事件
        $(document).on('click', '.areaDelete', function () {
            var name = $(this).prev()[0].innerText;
            $(".allArea").each(function (index, element) {
                if (element.innerText == name) {
                    $(element).parent().show();
                }
            });
            var index = choiceArea.indexOf(name);//获取需要删除的指定元素下标
            if (index > -1) {
                choiceArea.splice(index, 1);
            }
            $(this).parent().remove();//删除该元素的父元素
        })

        //执行区域选择
        var expressEdit = '';//选择编辑对应的标签，方便修改配送区域文字
        form.on('submit(areaSub)', function () {
            var name = '';//显示到运费模板页面的 区域 文字
            if (choiceArea === false) {
                choiceArea.push('全国统一运费');
            }
            for (var i = 0; i < choiceArea.length; i++) {
                name += choiceArea[i] + '、';
            }
            name = name.substr(0, name.length - 1);//获取去除最后符号的最终显示文字
            if (Trim(name) == '') {
                layer.msg('当前未选择区域');
                return;
            }
            layer.close(openIndex)
            //操作完成后添加新模板，如果为编辑则修改配送区域文字
            if (!isAreaEdit) {
                $('#add_new').before(
                    '<tr>\n' +
                    '    <td><span>' + name + '</span><input name="names" value="' + name + '" style="display: none"><span class="edit">编辑</span><span class="delete">删除</span></td>\n' +
                    '    <td><input name="first_num" class="layui-input"></td>\n' +
                    '    <td><input name="first_price" class="layui-input"></td>\n' +
                    '    <td><input name="expand_num" class="layui-input"></td>\n' +
                    '    <td><input name="expand_price" class="layui-input"></td>\n' +
                    '</tr>'
                );
            } else {
                var childNodes = expressEdit.parent()[0].childNodes;
                childNodes[0].innerText = name;
                childNodes[1].defaultValue = name;
            }
            $('#area_form').hide();
            form.render();
        })
//可配送区域结束

        //运费模板 编辑 点击事件
        $(document).off('click', '.edit').on('click', '.edit', function () {
            expressEdit = $(this);
            isAreaEdit = 1;
            var names = $(this).prev()[0].defaultValue;//获取当前点击的上一个兄弟元素的input框隐藏值
            var namesArr = names.split('、');
            //1.遍历所有 class 为 allArea 的元素，将设置还原
            $('.right').empty();
            $('.allArea').each(function () {
                $(this).parent().show();
            })
            //2.遍历所有 class 为 allArea 的元素，设置默认值，不设置会造成右侧重复可删除值
            $('.allArea').each(function () {
                //获取该元素的页面值
                var name = this.innerText;
                //判断该页面值是否存在 namesArr 数组中，如果存在，则执行选择事件
                if ($.inArray(name, namesArr) !== -1) {
                    $('.right').append('<div><span>' + name + '</span><span class="areaDelete">删除></span>></div>');
                    $(this).parent().hide();
                }
            })
            form.render();
            openIndex = layer.open({
                type: 1,
                title: '新增',
                content: $('#area_form'),
                shade: 0,
                offset: '100px',
                area: ['50vw', '60vh'],
                cancel: function () {
                    $('#area_form').hide();
                }
            })
        })

        //运费模板 删除 点击事件
        $(document).on('click', '.delete', function () {
            $(this).parent().parent().remove();
        })

//编辑开始
        if (expressId) {
            //获取运费模板信息
            $.ajax({
                url: url + '/' + expressId + key,
                type: "get",
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
                        layer.msg(res.message)
                        return false;
                    }

                    /*diy设置开始*/
                    $("input[name=name]").val(res.data.name);
                    $("input[name='type'][value='" + res.data.type + "']").prop("checked", true);

                    //设置配送区域
                    $('.expressTitle').parent().remove();//删除原有全国统一运费，方便循环
                    var list = res.data.details.app;//获取所有配送区域对应的模板
                    var length = list.length;

                    for (var i = 0; i < length; i++) {
                        if (list[i].names === '全国统一运费') {
                            $('#add_new').before(
                                '<tr>\n' +
                                '    <td><span>' + list[i].names + '</span><input name="names" value="' + list[i].names + '" style="display: none;"></td>\n' +
                                '    <td><input name="first_num" value="' + list[i].first_num + '" class="layui-input"></td>\n' +
                                '    <td><input name="first_price" value="' + parseFloat(list[i].first_price) + '" class="layui-input"></td>\n' +
                                '    <td><input name="expand_num" value="' + list[i].expand_num + '" class="layui-input"></td>\n' +
                                '    <td><input name="expand_price" value="' + parseFloat(list[i].expand_price) + '" class="layui-input"></td>\n' +
                                '</tr>'
                            );
                        } else {
                            $('#add_new').before(
                                '<tr>\n' +
                                '    <td><span class="one">' + list[i].names + '</span><input name="names" value="' + list[i].names + '" style="display: none;"><span class="edit">编辑</span><span class="delete">删除</span></td>\n' +
                                '    <td><input name="first_num" value="' + list[i].first_num + '" class="layui-input"></td>\n' +
                                '    <td><input name="first_price" value="' + list[i].first_price + '" class="layui-input"></td>\n' +
                                '    <td><input name="expand_num" value="' + list[i].expand_num + '" class="layui-input"></td>\n' +
                                '    <td><input name="expand_price" value="' + list[i].expand_price + '" class="layui-input"></td>\n' +
                                '</tr>'
                            );
                        }
                    }
                    /*diy设置结束*/
                    form.render();//设置完值需要刷新表单
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }
//编辑结束

        //执行最终添加或编辑
        form.on('submit(sub)', function () {
            var names = [];
            var first_num = [];
            var first_price = [];
            var expand_num = [];
            var expand_price = [];
            var subData;
            var ajaxUrl = url;
            if (!expressId) {
                ajaxType = 'post';
                successMsg = sucMsg.post;
            } else {
                ajaxType = 'put'
                ajaxUrl = url + '/' + expressId;
                successMsg = sucMsg.put;
            }
            $("input[name='names']").each(function (j, item) {
                names.push(item.value);
            });
            $("input[name='first_num']").each(function (j, item) {
                first_num.push(item.value);
            });
            $("input[name='first_price']").each(function (j, item) {
                first_price.push(item.value);
            });
            $("input[name='expand_num']").each(function (j, item) {
                expand_num.push(item.value);
            });
            $("input[name='expand_price']").each(function (j, item) {
                expand_price.push(item.value);
            });

            /*diy设置开始*/
            subData = {
                name: $('input[name=name]').val(),
                type: $("input[name='type']:checked").val(),
                names: names,
                first_num: first_num,
                first_price: first_price,
                expand_num: expand_num,
                expand_price: expand_price,
                key: saa_key
            }
            /*diy设置结束*/

            $.ajax({
                url: ajaxUrl,
                data: subData,
                type: ajaxType,
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
                        layer.msg(res.message);
                        return false;
                    }
                    layer.msg(successMsg);
                    layer.close(openIndex);
                    //执行添加完成跳转列表页面
                    return location.hash = "/logistics/express";
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        })

        /*动态添加单选框 应用分组*/
        function getGroups() {
            //每次执行该方法清空左右
            $('.left').empty();
            $('.right').empty();
            $.ajax({
                url: baseUrl + '/goodAddress',
                type: "get",
                headers: headers,
                beforeSend: function () {
                    loading = layer.load(loadType, loadShade);//显示加载图标
                },
                success: function (res) {
                    layer.close(loading);
                    if (typeof res == 'string') {
                        res = eval('(' + res + ')');
                    }
                    res = res.data;
                    if (res.status !== "1") {
                        layer.msg(res.info);
                        return false;
                    }
                    var data = res.districts[0].districts;
                    var length = data.length;
                    //将省级元素添加到 div left
                    for (var i = 0; i < length; i++) {
                        $('.left').append('<div class="areaAll"><span class="allArea">' + data[i]["name"] + '</span>><span class="areaChoice">选择></span></div>');
                    }
                    form.render();
                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        }

    })
    exports('logistics/add', {})
});
