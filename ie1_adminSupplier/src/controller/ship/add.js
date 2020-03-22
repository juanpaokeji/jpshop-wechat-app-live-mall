/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/10/29
 * js 门店商品添加
 */

var is_open_assemble = 0;//是否开启拼团，1 开启，0 未开启
var is_bargain = 0;//是否开启砍价，1 开启，0 未开启
layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter', 'upload', 'laydate'], function () {
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        // var upload = layui.upload;//配置
        var layDate = layui.laydate;
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
        var goods_id = sessionStorage.getItem('goods_id');//当前操作 id ,编辑时可用到
        var arr, res;//全局ajax请求参数
        var videoData = {};
        //如果类型为新增，则
        form.render();//还原后需要重置表单  pictureGroup  picture

        //加载图片库及判断图片库js是否已加载
        $('.introduce_images').load('src/views/images.html');
        if (!isIncludeJS("images.js")) {
            $.getScript("src/lib/images.js");
        }
        layDate.render({
            elem: '#diyStartTime',
            type: 'datetime',
            trigger: 'click'//解决闪现问题
        });
        layDate.render({
            elem: '#endTime',
            type: 'datetime',
            trigger: 'click'
        });
        layDate.render({
            elem: '#take_goods_time',
            type: 'date',
            trigger: 'click'
        });

        //上传视频
        $('.videos').on('change', function () {
            var videoFile = this.files[0],
                formData = new FormData(),
                reader = new FileReader();
            formData.append('file', videoFile);
            reader.readAsDataURL(videoFile);
            reader.onload = function (e) {
                $('.videoed').attr("src", e.target.result).css('display', '')
            };
            $.ajax({
                url: baseUrl + '/merchantGoodsVideo',
                data: formData,
                async: false,
                type: 'post',
                headers: headers,
                mimeType: "multipart/form-data",
                processData: false,
                contentType: false,
                beforeSend: function () {
                    loading = layer.load(1, {shade: 0.3});//layer.open 类型和 shade 属性 加载
                },
                success: function (res) {
                    layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
                    layer.msg('视频上传成功', {icon: 1, time: 2000});//接口错误提示
                    res = JSON.parse(res);
                    if (res.status === 200) {
                        videoData = res.data
                    }
                },
                error: function () {
                    layer.msg(layui.setter.errorMsg, {icon: 1, time: 2000});//接口错误提示
                    layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
                }
            })
        });

        // 限量 input显示隐藏
        form.on('checkbox(is_limit)', function (data) {
            if (data.elem.checked) {
                $("#limit_number").css('display', 'block');
            } else {
                $("#limit_number").css('display', 'none');
            }
        });


        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/supplierGoods";//当前页面主要使用 url
        /*diy设置结束*/

        $('#type1').attr('class', 'layui-col-md2 detail type');
        $('.type1').css('color', '#318AF7');

        //实例化百度编辑器
        UE.delEditor('editor');//先删除之前实例的对象
        var ue = UE.getEditor('editor');//添加编辑器 //参数 id 可随意更改为当前期望的值
        //为百度编辑器新增的上传图片增加点击事件
        ue.commands['uploadimage'] = {
            execCommand: function () {
                sessionStorage.setItem('images_common_type_uEditor', '1');//设置类型为百度编辑器
                sessionStorage.setItem('images_common_div_info', '<img style="width: 100%; margin-top: -5px">');
                images_open_index_fun();
            }
        };

        var merchantShopCategory;//获取的商品类目，排版后数据
        //加载商品分组
        $.ajax({
            url: baseUrl + '/supplierCategoryTypeMini',
            type: "get",
            headers: headers,
            async: false,
            beforeSend: function () {
                loading = layer.load(loadType, loadShade);//显示加载图标
            },
            success: function (res) {
                if (res.status == timeOutCode) {
                    layer.msg(timeOutMsg);
                    admin.exit();
                    return false;
                }
                layer.close(loading);
                if (res.status !== 200) {
                    layer.msg(res.message);
                    if (res.status == 204) {
                        layer.confirm('当前没有商品分组，是否去设置？', function (index) {
                            layer.close(index);
                            location.hash = '/goods/group';
                        })
                    }
                    return false;
                }
                merchantShopCategory = res;
                //这里修改，获取一级和二级目录的三维数组
                //循环获取商品类目一级
                for (var a = 0; a < res.data.length; a++) {
                    $('select[name=m_parent_id]').append("<option value=" + res.data[a].id + ">" + res.data[a].name + "</option>");
                    if (a == 0 && res.data[0].sub && res.data[0].sub.length > 0) {
                        //获取类目一级默认第一个下面对应的二级类目
                        for (var b = 0; b < res.data[0].sub.length; b++) {
                            $('select[name=m_category_id]').append("<option value=" + res.data[0].sub[b].id + ">" + res.data[0].sub[b].name + "</option>");
                        }
                    }
                }
                form.render();
            },
            error: function () {
                layer.msg(errorMsg);
                layer.close(loading);
            }
        });

        //加载区域分组
        arr = {
            method: 'supplierGoodsCityGroup?status=1',//获取所有的省市区
            type: 'get',
            async: true
        };
        res = getAjaxReturn(arr);
        if (res && res.data) {
            var name;
            var id;
            for (var a = 0; a < res.data.length; a++) {
                name = res.data[a].name;
                id = res.data[a].id;
                $('select[name=city_group_id]').append("<option value=" + id + ">" + name + "</option>");
            }
            form.render();
        }

        //一级分组切换事件
        form.on('select(m_parent_id)', function (data) {
            //当前点击的option
            var thisId = data.value;
            var res = merchantShopCategory;
            for (var a = 0; a < res.data.length; a++) {
                if (res.data[a].id == thisId) {
                    //清除二级菜单
                    $("select[name=m_category_id]").empty();
                    if (res.data[a].hasOwnProperty('sub')) {
                        //获取当前选择分组一级下面对应的二级分组
                        for (var b = 0; b < res.data[a].sub.length; b++) {
                            $('select[name=m_category_id]').append("<option value=" + res.data[a].sub[b].id + ">" + res.data[a].sub[b].name + "</option>");
                        }
                    } else {
                        layer.msg('当前选择的分组没有数据');
                    }
                }
            }
            form.render();
        });

        var pic_urls = '';//最终存入数据库的 图片地址字符串
        var urlsArr = {};
        var random;

        //删除图片按钮点击事件
        $(document).on("click", '.deleteIcon', function () {
            var parentNode = this.parentNode;
            var ran = $(this).prev('img').attr('alt');
            layer.open({
                title: '删除',
                content: '确认删除这张图片吗',
                yes: function (index) {
                    //获取需要删除的 img
                    ran = ran.substr(2, ran.length);//去除开头定义的两个字母
                    delete (urlsArr[ran]);//删除最终保存数组中对应的数据
                    parentNode.remove();//删除页面显示图片的元素
                    layer.close(index);//关闭弹出窗
                }
            });
        });

        var type = 1;
        //商品类型选择
        $(document).on('click', '.type', function () {
            $('.type').attr('class', 'layui-col-md2 undetail type');
            $(this).attr('class', 'layui-col-md2 detail type');
            //设置类型值
            if ($(this).attr('id') === 'type1') {
                type = 1;
                $('.type2').css('color', '');
                $('.type1').css('color', '#318AF7');
                $('.service_goods_is_ship').hide();
            } else if ($(this).attr('id') === 'type2') {
                type = 3;
                $('.type1').css('color', '');
                $('.type2').css('color', '#318AF7');
                $('.service_goods_is_ship').show();
            }
        });

//拼团设置和规格设置开始
        /**
         * 1、开启拼团后显示单独购买到拼团类型这5个按钮
         * 2、开启拼团后在商品规格明细中显示拼团配置按钮
         * 3、开启是否团长优惠在拼团配置中显示团长优惠价
         * 4、选择单双规格需要清空现有规格明细
         * 5、添加规格名称样式为复选框，当勾中复选框按钮时生成规格明细
         * 6、生成规格时判断是否开启拼团，如果开启，则每种规格后需要显示‘拼团配置’按钮
         * 7、拼团配置通过拼团类型来区别展现形式，如果是多人团，则只需要显示拼团价格和成团人数，如果是阶梯团，只需要显示添加和删除按钮
         */

        //开启拼团按钮点击事件
        form.on('switch(is_open_assemble)', function (data) {
            //判断开启拼团按钮是否开启，来显示隐藏其他拼团信息，该方法等做到规格的时候，需要在规格中展示或隐藏拼团价格信息
            if (data.elem.checked) {
                $('.tuan_config').show();
                is_open_assemble = 1;
                $('.assemble_price').show();
                $('.assemble_price_div').show();
            } else {
                $('.tuan_config').hide();
                is_open_assemble = 0;
                $('.assemble_price').hide();
                $('.assemble_price_div').hide();
            }
        });

        //是否团长优惠切换事件
        form.on('switch(is_leader_discount)', function (data) {
            if (data.elem.checked) {
                $('.is_leader_discount').show();
            } else {
                $('.is_leader_discount').hide();
            }
        });

        //拼团类型切换事件
        form.on('radio(tuan_type)', function () {
            if (this.value === '1') {
                //当切换回多人团时需要删除后添加的 成团人数
                $('.group_price_discount').hide();
                $('.assemble_add_button').hide();
                $('.assemble_ladder').empty();
            } else if (this.value === '2') {
                $('.group_price_discount').show();
                $('.assemble_add_button').show();
            }
            //获取是否团长优惠的值，如果是打开则团长优惠比例显示，否则隐藏
        });

        //添加阶梯团成员人数和团长优惠比例
        $(document).off('click', '.assemble_add_button').on('click', '.assemble_add_button', function () {
            var is_leader_discount_style = 'none';//是否显示团长优惠比例
            //获取团长优惠
            if ($('input[name=is_leader_discount]:checked').val()) {
                is_leader_discount_style = 'block';
            }
            $('.assemble_ladder').append('<div class="layui-form-item ">\n' +
                '                                <label class="layui-form-label">成团人数</label>\n' +
                '                                <div class="layui-input-inline">\n' +
                '                                    <input name="assemble_number" class="layui-input" lay-verify="number" value="0">\n' +
                '                                </div>\n' +
                '                                <div class="is_leader_discount" style="display: ' + is_leader_discount_style + ';">' +
                '                                    <label class="layui-form-label">团长优惠比例(%)</label>\n' +
                '                                    <div class="layui-input-inline">\n' +
                '                                        <input name="assemble_group_discount" class="layui-input" lay-verify="number" value="0">\n' +
                '                                    </div>' +
                '                                </div>\n' +
                '                                <div class="group_price_discount">' +
                '                                    <label class="layui-form-label">拼团价比例(%)</label>\n' +
                '                                    <div class="layui-input-inline">\n' +
                '                                        <input name="group_price_discount" class="layui-input" lay-verify="number" value="0">\n' +
                '                                    </div>' +
                '                                </div>\n' +
                '                                <a href="javascript: void(0)" class="assemble_del_button" style="color: red;">删除</a>\n' +
                '                            </div>');
        });

        //删除阶梯团成员人数和团长优惠比例
        $(document).off('click', '.assemble_del_button').on('click', '.assemble_del_button', function () {
            $(this).parent().remove();
        });

        //获取当前规格类型
        var now_stock_type = $("select[name=stock_type]").val();
        $('.property2').hide();
        form.on('select(stock_type)', function (data) {
            if (now_stock_type == $("select[name=stock_type]").val()) {
                //相同，不做操作
                return;
            }
            layer.confirm('切换后将删除已添加的规格，确认切换吗？', {
                btn: ['确定', '取消'] //可以无限个按钮
                , btn2: function (index) {
                    layer.close(index);
                    if (data.value == '1') {
                        //需要显示规格2
                        $("select[name=stock_type]").val(2);
                        now_stock_type = 2;
                    } else if (data.value == '2') {
                        //需要显示规格1
                        $("select[name=stock_type]").val(1);
                        now_stock_type = 1;
                    }
                    form.render();
                }
            }, function (index) {
                layer.close(index);
                $('.specification_name').val('');
                $('.first_s').empty();
                $('.second_s').empty();
                $('.save_specifications_list').empty();
                if (data.value == '2') {
                    //双规格，显示规格二
                    $('.property2').show();
                    now_stock_type = 2;
                } else if (data.value == '1') {
                    //单规格，隐藏规格2
                    $('.property2').hide();
                    now_stock_type = 1;
                }
            });
        });

        //规格1添加操作，添加已添加的规格进行判断
        $(document).off('click', '.first_s_add').on("click", '.first_s_add', function () {
            var first_s_name = $('.first_s_name').val();
            if (first_s_name != '' && Trim(first_s_name) != '') {
                var property1_name = [];
                //获取已添加的规格1
                $('.specifications_first').each(function (j, item) {
                    property1_name.push(item.title);
                });
                //判断新增加的规格是否已存在
                if (property1_name.indexOf(first_s_name) !== -1) {
                    layer.msg('您选择的规格1已重复，请重新填写规格1的值');
                    return;
                }
                // var property1_value_random = Math.round(Math.random() * 1e10);
                $('.first_s').append('<input class="specifications_first" type="checkbox" lay-skin="primary" title="' + first_s_name + '" lay-filter="choice_property">');
                $('.first_s_name').val('').focus();
                form.render();
            } else {
                layer.msg('请填写规格1再添加');
                $('.first_s_name').val('').focus();
            }
        });

        //规格2添加操作，添加已添加的规格进行判断
        $(document).off('click', '.second_s_add').on("click", '.second_s_add', function () {
            var second_s_name = $('.second_s_name').val();
            if (second_s_name != '' && Trim(second_s_name) != '') {
                var property2_name = [];
                //获取已添加的规格2
                $('.specifications_second').each(function (j, item) {
                    property2_name.push(item.title);
                });
                //判断新增加的规格是否已存在
                if (property2_name.indexOf(second_s_name) !== -1) {
                    layer.msg('您选择的规格2已重复，请重新填写规格2的值');
                    return;
                }
                $('.second_s').append('<input class="specifications_second" type="checkbox" lay-skin="primary" title="' + second_s_name + '" lay-filter="choice_property">');
                $('.second_s_name').val('').focus();
                form.render();
            } else {
                layer.msg('请填写规格2再添加');
                $('.second_s_name').val('').focus();
            }
        });

        //规格复选框点击事件
        form.on('checkbox(choice_property)', function () {
            var p_c_className = this.className; //选择规格值对应的规格1还是规格2
            var p_c_title = this.title; //选择规格值对应的值
            var p_c_checked = this.checked; //选择规格值对应的富文本是否选中
            // 如果富文本选中
            // 判断是规格1还是规格2
            // 如果是1，则生成规格框
            // 如果是2，判断是否已选择规格1，如果未选提示选择，已选，循环规格框添加规格2
            // 生成后需要设置库存（可能添加价格）不可编辑
            var stocks_is_checked = $('#stocks').attr('readonly');
            if (!stocks_is_checked) { //判断库存文本框是否不可编辑
                $('#stocks').attr('readonly', 'readonly');
            }

            var stock_type = $('#stock_type').val();
            if (stock_type === '1') {
                //当单规格并且选中的时候，添加一条规格明细，如果是取消选中，则将该规格值对应的规格明细删除
                if (p_c_checked) {
                    $('.save_specifications_list').append(new_specifications(p_c_title));
                } else {
                    $('.property1_name').each(function (i, j) {
                        if (p_c_title === $(j).attr('data')) {
                            $(this).parent().parent().remove();
                        }
                    })
                }
            } else if (stock_type === '2') {
                //判断复选框操作是规格1还是规格2
                if (p_c_className === 'specifications_first') {
                    //如果是规格1选中，则需要判断选中的规格2数量，如果没有，不添加，如果有，则需要循环选中的规格2进行添加
                    if (p_c_checked) {
                        //获取已选择的规格2，循环添加数据，如果没有规格2，则不添加
                        var second_checked_len = $("input[class=specifications_second]:checked").length;
                        if (second_checked_len > 0) {
                            $("input[class=specifications_second]:checked").each(function (i, j) {
                                $('.save_specifications_list').append(new_specifications(p_c_title, $(j).attr('title')));
                            })
                        }
                    } else {
                        //规格1取消选中，直接循环删除规格1对应的规格明细，不用管规格2
                        $('.property1_name').each(function (i, j) {
                            if (p_c_title === $(j).attr('data')) {
                                $(this).parent().parent().remove();
                            }
                        })
                    }
                } else if (p_c_className === 'specifications_second') {
                    if (p_c_checked) {
                        //规格2选中，需要判断时候有规格1，如果有，循环规格1添加规格明细，如果没有，不添加
                        var first_checked_len = $("input[class=specifications_first]:checked").length;
                        if (first_checked_len > 0) {
                            $("input[class=specifications_first]:checked").each(function (i, j) {
                                $('.save_specifications_list').append(new_specifications($(j).attr('title'), p_c_title));
                            })
                        }
                    } else {
                        //规格2取消选中，直接循环删除规格2对应的规格明细，不用管规格1
                        $('.property2_name').each(function (i, j) {
                            if (p_c_title === $(j).attr('data')) {
                                $(this).parent().parent().remove();
                            }
                        })
                    }
                } else {
                    layer.msg('操作错误', {icon: 1, time: 2000});
                }
            }

        });

        //单图片上传事件
        $(document).off('change', '.addImgPut').on('change', '.addImgPut', function () {
            var this_img = this;
            var file = this.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    $(this_img).next().attr('src', e.target.result);
                    $(this_img).parent().find('.main_figure').css('display', 'none');
                };
            }
            file = null;
        });

        //删除规格明细事件
        $(document).on('click', '.specifications_delete', function () {
            $(this).parent().remove();
            //判断删除后的规格明细数量，如果为零，设置库存（可能会添加价格）为可编辑
            var s_d_len = $('.specification_details').length;
            if (s_d_len === 0) {
                $('#stocks').removeAttr('readonly');
            }
        });

//规格设置结束

        //获取最低价格
        $(document).on('change', '.stock_price', function () {
            //循环获取价格框，取最低存入下方最低价格
            var priceArr = [];
            $('input[name=stock_price]').each(function () {
                if (this.value != '') {
                    priceArr.push(this.value);
                }
            });
            priceArr.sort(sortNumber);//数组排序，取第一个值（最低值）
            $('#price').val(priceArr[0]);
        });

        //获取最低拼团价格
        $(document).on('change', '.stock_assemble_price', function () {
            //循环获取价格框，取最低存入下方最低价格
            var priceArr = [];
            $('input[name=stock_assemble_price]').each(function () {
                if (this.value != '') {
                    priceArr.push(this.value);
                }
            });
            priceArr.sort(sortNumber);//数组排序，取第一个值（最低值）
            $('#assemble_price').val(priceArr[0]);
        });

        //获取总库存
        $(document).on('change', '.stock_number', function () {
            //现在只获取一个框中的数字，会拓展到循环获取并赋值
            var num = 0;
            $('input[name=stock_number]').each(function () {
                if (this.value != '') {
                    num += this.value * 1;
                }
            })
            $('#stocks').val(num);
        });

        //开启砍价按钮点击事件
        form.on('switch(is_bargain)', function (data) {
            if (data.elem.checked) {
                //判断是否开启拼团，如果开启拼团则提示
                if (is_open_assemble === 1) {
                    layer.msg('该商品已设置开启拼团，如果需要开启砍价，请关闭拼团', {icon: 1, time: 2000});
                    $('input[name=is_bargain]').prop('checked', false);
                    form.render();
                    return;
                }
                is_bargain = 1;
                $('.bargain_info').append(
                    '<div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"><span class="asterisk">*</span>活动时间</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="bargain_start_time"\n' +
                    '                               id="bargain_start_time">\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" lay-verify="required" name="bargain_end_time"\n' +
                    '                               id="bargain_end_time">\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '                <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">支持单独购买</label>\n' +
                    '                    <div class="layui-input-inline" style="width: 100px">\n' +
                    '                        <input type="checkbox" name="is_buy_alone" lay-skin="switch" lay-text="是|否">\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '                <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"><span class="asterisk">*</span>虚拟发起砍价数</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" name="fictitious_initiate_bargain" lay-verify="required|number">\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '                <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"><span class="asterisk">*</span>虚拟帮砍人数</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" name="fictitious_help_bargain" lay-verify="required|number">\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '                <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"><span class="asterisk">*</span>砍价最终底价</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" name="bargain_price" lay-verify="required|number">\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-input-inline" style="width: 300px">\n' +
                    '                        <span>必须大于0</span>\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '                <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"><span class="asterisk">*</span>好友帮砍次数</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" name="help_number" lay-verify="required|number">\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-input-inline" style="width: 300px">\n' +
                    '                        <span>限制该用户对当前商品最多可以帮砍次数</span>\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '                <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"><span class="asterisk">*</span>砍价时间限制</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" name="bargain_limit_time" lay-verify="required|number">\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-input-inline" style="width: 300px">\n' +
                    '                        <span>发起砍价后，最多砍价小时数，只能填写整数</span>\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '                <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label">砍价规则</label>\n' +
                    '                </div>\n' +
                    '                <div class="layui-form-item">\n' +
                    '                    <label class="layui-form-label"><span class="asterisk">*</span>金额大于</label>\n' +
                    '                    <div class="layui-input-inline">\n' +
                    '                        <input type="text" class="layui-input" name="bargain_list_price" lay-verify="required|number">\n' +
                    '                    </div>\n' +
                    '                    <label class="layui-form-label"><span class="asterisk">*</span>每次砍价</label>\n' +
                    '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                    '                        <input type="text" class="layui-input" name="bargain_min" lay-verify="required|number">\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-input-inline" style="width: 20px;">\n' +
                    '                        到\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                    '                        <input type="text" class="layui-input" name="bargain_max" lay-verify="required|number">\n' +
                    '                    </div>\n' +
                    '                    <a href="javascript: void(0)" class="bargain_add" style="color: limegreen;">添加</a>\n' +
                    '                </div>\n' +
                    '                <div class="bargain_rule_list"></div>'
                );
                layDate.render({
                    elem: '#bargain_start_time',
                    type: 'datetime',
                    trigger: 'click'
                });
                layDate.render({
                    elem: '#bargain_end_time',
                    type: 'datetime',
                    trigger: 'click'
                });
            } else {
                is_bargain = 0;
                $('.bargain_info').empty();
            }
        });

        //添加砍价规则
        $(document).off('click', '.bargain_add').on('click', '.bargain_add', function () {
            $('.bargain_rule_list').append(
                '               <div class="layui-form-item">\n' +
                '                    <label class="layui-form-label">金额大于</label>\n' +
                '                    <div class="layui-input-inline">\n' +
                '                        <input type="text" class="layui-input" name="bargain_list_price" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <label class="layui-form-label">每次砍价</label>\n' +
                '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                '                        <input type="text" class="layui-input" name="bargain_min" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 20px;">\n' +
                '                        到\n' +
                '                    </div>\n' +
                '                    <div class="layui-input-inline" style="width: 100px;">\n' +
                '                        <input type="text" class="layui-input" name="bargain_max" lay-verify="required|number">\n' +
                '                    </div>\n' +
                '                    <a href="javascript: void(0)" class="bargain_del" style="color: red;">删除</a>\n' +
                '                </div>'
            );
        });

        //删除砍价规则
        $(document).off('click', '.bargain_del').on('click', '.bargain_del', function () {
            $(this).parent().remove();
        });

        // 编辑设置
        if (goods_id) {
            //获取该商品id对应的数据
            $.ajax({
                    url: url + '/' + goods_id,
                    type: "get",
                    headers: headers,
                    async: false,
                    beforeSend: function () {
                        loading = layer.load(loadType, loadShade);//显示加载图标
                    },
                    success: function (res) {
                        if (res.status == timeOutCode) {
                            layer.msg(timeOutMsg);
                            admin.exit();
                            return false;
                        }
                        layer.close(loading);
                        if (res.status !== 200) {
                            if (res.status != 204) {
                                layer.msg(res.message);
                            }
                            return false;
                        }
                        //设置页面值
                        var data = res.data;
                        videoData.video_url = data.video_url;
                        videoData.video_pic_url = data.video_pic_url;
                        videoData.video_id = data.video_id;
                        data.video_url && $('.videoed').attr("src", data.video_url).css('display', '');
                        // type = data.type;
                        // $('#type' + type).attr('class', 'layui-col-md2 detail type');
                        // $('.type' + type).css('color', '#318AF7');
                        type = data.type;

                        //设置类型值
                        if ($(this).attr('id') === 'type1') {
                            type = 1;
                            $('.type2').css('color', '');
                            $('.type1').css('color', '#318AF7');
                            $('.service_goods_is_ship').hide();
                        } else if ($(this).attr('id') === 'type2') {
                            type = 3;
                            $('.type1').css('color', '');
                            $('.type2').css('color', '#318AF7');
                            $('.service_goods_is_ship').show();
                        }
                        $('.type').attr('class', 'layui-col-md2 undetail type');
                        if (type === '1') {
                            $('#type1').attr('class', 'layui-col-md2 detail type');
                            $('.type2').css('color', '');
                            $('.type1').css('color', '#318AF7');
                            $('.service_goods_is_ship').hide();
                        } else if (type === '3') {
                            $('#type2').attr('class', 'layui-col-md2 detail type');
                            $('.type1').css('color', '');
                            $('.type2').css('color', '#318AF7');
                            if (data.service_goods_is_ship === '1') {
                                $("input[name=service_goods_is_ship]").prop('checked', true);
                            } else {
                                $("input[name=service_goods_is_ship]").removeAttr('checked');
                            }
                            $('.service_goods_is_ship').show();
                        }
                        $("input[name=name]").val(data.name);
                        $("input[name=short_name]").val(data.short_name);
                        $("input[name=code]").val(data.code);
                        if (data.is_limit == 1) {
                            $("input[name=is_limit]").attr("checked", "checked");
                            $("#limit_number").css('display', 'block');
                        } else {
                            $("#limit_number").css('display', 'none');
                        }
                        $('input[name=limit_number]').val(data.limit_number);
                        //商品图展示
                        urlsArr = {};
                        var p_urls = data.pic_urls.substr(0, data.pic_urls.length - 1).split(',');
                        for (var p in p_urls) {
                            random = Math.round(Math.random() * 1e9);
                            urlsArr[random] = p_urls[p];
                            $('#images').append('<div class="images"><img src="' + p_urls[p] + '" alt="gu' + random + '" class="layui-upload-img goodsImg" style="width: 80px; height: 80px"><i class="deleteIcon layui-icon-close layui-icon"></i></div>')
                        }

                        //分组单独处理 类目已删除
                        var m_parent_id;
                        //循环 merchantShopCategory 查找数据中 sub 里包含 data.m_category_id 的那一组，获取 对应的一级和二级列表
                        for (var a = 0; a < merchantShopCategory.data.length; a++) {
                            //清除二级菜单
                            $("select[name=m_category_id]").empty();
                            if (merchantShopCategory.data[a].hasOwnProperty('sub')) {
                                //循环获取二级类目判断是否匹配
                                var flag = false;
                                for (var b = 0; b < merchantShopCategory.data[a].sub.length; b++) {
                                    if (merchantShopCategory.data[a].sub[b].id == data.m_category_id) {
                                        flag = true;
                                        m_parent_id = merchantShopCategory.data[a].id;
                                        break;
                                    }
                                }
                                if (flag) {
                                    for (var b = 0; b < merchantShopCategory.data[a].sub.length; b++) {
                                        $('select[name=m_category_id]').append("<option value=" + merchantShopCategory.data[a].sub[b].id + ">" + merchantShopCategory.data[a].sub[b].name + "</option>");
                                    }
                                    break;
                                }
                            }
                        }
                        $('select[name=m_parent_id]').val(m_parent_id);
                        $('select[name=m_category_id]').val(data.m_category_id);
                        $('select[name=city_group_id]').val(data.city_group_id);
                        // $('select[name=band_self_leader_id]').val(data.band_self_leader_id);
                        // $("select[name=shop_express_template_id]").val(data.shop_express_template_id);

                        //获取标签并循环存入文本框中
                        if (data.label) {
                            var label = data.label;
                            label = label.split(',');
                            var label_len = label.length;
                            for (var l = 0; l < label_len; l++) {
                                if (Trim(label[l]) != '') {
                                    $($('input[name=label]')[l]).val(label[l]);
                                }
                            }
                        }
                        $("input[name=unit]").val(data.unit);//商品单位
                        //商品属性
                        if (data.attribute) {
                            var attribute = data.attribute;
                            var attribute_len = attribute.length;
                            for (var att = 0; att < attribute_len; att++) {
                                var att_value = attribute[att];
                                if (att === 0) {
                                    $("input[name=attribute]").val(att_value)
                                } else {
                                    $('.attribute_list').append('<div class="layui-form-item ">\n' +
                                        '                                <label class="layui-form-label"></label>\n' +
                                        '                                <div class="layui-input-inline">\n' +
                                        '                                    <input name="attribute" value="' + att_value + '" class="layui-input">\n' +
                                        '                                </div>\n' +
                                        '                                <a href="javascript: void(0)" class="attribute_del_button" style="color: red;">删除</a>\n' +
                                        '                            </div>');
                                }
                            }
                        }

                        $("textarea[name=simple_info]").val(data.simple_info);
                        ue.ready(function () {
                            //设置编辑器的内容
                            setTimeout(function () {
                                ue.setContent(data.detail_info, false);
                            }, 600);
                        });
                        $("select[name=stock_type]").val(data.stock_type);

                        //判断是否开启拼团
                        var is_open_assemble_edit = data.is_open_assemble;
                        is_open_assemble = parseInt(is_open_assemble_edit);
                        if (is_open_assemble_edit === '1') {
                            var group_info = data.group_info;
                            //显示拼团设置和拼团价
                            if (is_open_assemble_edit === '1') {
                                $("input[name=is_open_assemble]").prop('checked', true);
                            } else {
                                $("input[name=is_open_assemble]").removeAttr('checked');
                            }
                            if (group_info.is_self === '1') {
                                $("input[name=is_self]").prop('checked', true);
                            } else {
                                $("input[name=is_self]").removeAttr('checked');
                            }
                            if (group_info.is_automatic === '1') {
                                $("input[name=is_automatic]").prop('checked', true);
                            } else {
                                $("input[name=is_automatic]").removeAttr('checked');
                            }
                            if (group_info.older_with_newer === '1') {
                                $("input[name=older_with_newer]").prop('checked', true);
                            } else {
                                $("input[name=older_with_newer]").removeAttr('checked');
                            }
                            if (group_info.is_show === '1') {
                                $("input[name=is_show]").prop('checked', true);
                            } else {
                                $("input[name=is_show]").removeAttr('checked');
                            }
                            var tuan_type = group_info.type;
                            $("input[name='tuan_type'][value='" + tuan_type + "']").prop("checked", true);
                            //循环添加成团人数和团长优惠比例
                            var assemble_number = group_info.assemble_number;
                            var group_discount = [];
                            if (group_info.group_discount) {
                                group_discount = group_info.group_discount;
                            }
                            var group_price_discount = [];
                            if (group_info.group_price_discount) {
                                group_price_discount = group_info.group_price_discount;
                            }
                            for (var as = 0; as < assemble_number.length; as++) {
                                //如果是第一条，只需要将值填入文本框，其余的需要添加标签
                                if (as === 0) {
                                    $("input[name=assemble_number]").val(assemble_number[as]);
                                    $("input[name=assemble_group_discount]").val(group_discount[as] ? group_discount[as] : '0');
                                    $("input[name=group_price_discount]").val(group_price_discount[as] ? group_price_discount[as] : '0');
                                } else {
                                    var is_leader_discount_style = 'none';//是否显示团长优惠比例
                                    //获取团长优惠
                                    if (group_info.is_leader_discount === '1') {
                                        is_leader_discount_style = 'block';
                                    }
                                    $('.assemble_ladder').append('' +
                                        '                           <div class="layui-form-item ">\n' +
                                        '                                <label class="layui-form-label">成团人数</label>\n' +
                                        '                                <div class="layui-input-inline">\n' +
                                        '                                    <input name="assemble_number" class="layui-input" lay-verify="number" value="' + assemble_number[as] + '">\n' +
                                        '                                </div>\n' +
                                        '                                <div class="is_leader_discount" style="display: ' + is_leader_discount_style + ';">' +
                                        '                                    <label class="layui-form-label">团长优惠比例(%)</label>\n' +
                                        '                                    <div class="layui-input-inline">\n' +
                                        '                                        <input name="assemble_group_discount" class="layui-input" lay-verify="number" value="' + (group_discount[as] ? group_discount[as] : '0') + '">\n' +
                                        '                                    </div>' +
                                        '                                </div>\n' +
                                        '                                <div class="group_price_discount" style="display: ' + is_leader_discount_style + ';">' +
                                        '                                    <label class="layui-form-label">拼团价比例(%)</label>\n' +
                                        '                                    <div class="layui-input-inline">\n' +
                                        '                                        <input name="group_price_discount" class="layui-input" lay-verify="number" value="' + (group_price_discount[as] ? group_price_discount[as] : '0') + '">\n' +
                                        '                                    </div>' +
                                        '                                </div>\n' +
                                        '                                <a href="javascript: void(0)" class="assemble_del_button" style="color: red;">删除</a>\n' +
                                        '                            </div>');
                                }
                            }
                            //当团长优惠比例打开时，需要显示团长优惠比例填写框，否则隐藏
                            if (group_info.is_leader_discount === '1') {
                                $('.is_leader_discount').show();
                                $("input[name=is_leader_discount]").prop('checked', true);
                            } else {
                                $('.is_leader_discount').hide();
                                $("input[name=is_leader_discount]").removeAttr('checked');
                            }
                            if (tuan_type === '1') {
                                //当多人团时需要隐藏后添加的 成团人数
                                $('.group_price_discount').hide();
                                $('.assemble_add_button').hide();
                                $('.assemble_ladder').empty();
                            } else if (tuan_type === '2') {
                                $('.group_price_discount').show();
                                $('.assemble_add_button').show();
                            }
                            $('.tuan_config').show();
                            $("input[name=assemble_price]").val(parseFloat(data.assemble_price));
                            $('.assemble_price_div').show();
                        }

                        //规格明细 需要获取商品对应的规格列表 将获取到的规格1和2分别显示在页面上
                        var stock = data.stock;
                        if (data.have_stock_type == '1') {
                            for (var s = 0; s < stock.length; s++) {
                                var stock_assemble_price = 0;
                                //如果开启拼团，则获取当前规格对应的拼团价
                                if (is_open_assemble_edit === '1') {
                                    stock_assemble_price = data.group_info.assemble_price[s];
                                }
                                var p1 = stock[s]['property1_name'];
                                var p2 = stock[s]['property2_name'];
                                $('.save_specifications_list').append(new_specifications(
                                    p1,
                                    p2,
                                    parseFloat(stock[s]['price']),
                                    stock[s]['number'],
                                    stock[s]['code'],
                                    parseFloat(stock[s]['cost_price']),
                                    stock[s]['pic_url'],
                                    stock_assemble_price
                                ));
                            }
                            $('.main_figure').css('display', 'none');
                            //将规格1规格2添加到页面 由于数据库已去除，所以可直接循环添加
                            var property1 = data.property1;
                            if (Trim(property1) != '') {
                                var k_v1 = property1.split(':');
                                if (Trim(k_v1[0]) != '') {
                                    $('input[name=property1_key]').val(k_v1[0]);
                                }
                                var arr = k_v1[1].split(',');
                                for (var i = 0; i < arr.length; i++) {
                                    $('.first_s').append('<input class="specifications_first" type="checkbox" lay-skin="primary" title="' + arr[i] + '" lay-filter="choice_property" checked>');
                                }
                            }
                            if (data.stock_type == '2') {
                                $('.property2').show();
                                var property2 = data.property2;
                                if (Trim(property2) != '') {
                                    var k_v2 = property2.split(':');
                                    if (Trim(k_v2[0]) != '') {
                                        $('input[name=property2_key]').val(k_v2[0]);
                                    }
                                    var arr2 = k_v2[1].split(',');
                                    for (var j = 0; j < arr2.length; j++) {
                                        $('.second_s').append('<input class="specifications_second" type="checkbox" lay-skin="primary" title="' + arr2[j] + '" lay-filter="choice_property" checked>');
                                    }
                                }
                            }
                        }

                        //如果开启拼团，则显示拼团价
                        if (is_open_assemble_edit === '1') {
                            $('.assemble_price').show();
                        }

                        $("input[name=price]").val(parseFloat(data.price));
                        $("input[name=line_price]").val(parseFloat(data.line_price));
                        $("input[name=stocks]").val(parseFloat(data.stocks));
                        $("input[name=sales_number]").val(parseFloat(data.sales_number));
                        $("input[name=commission_leader_ratio]").val(parseFloat(data.commission_leader_ratio));
                        // $("input[name=commission_selfleader_ratio]").val(parseFloat(data.commission_selfleader_ratio));
                        //上架类型及时间设置 商家类型再次修改，添加开始时间类型 start_type，按照类型显示页面选择
                        if (data.start_type === '2') {
                            $("input[name=diyStartTime]").val(data.format_start_time);
                        }
                        $("input[name='start_time'][value='" + data.start_type + "']").prop("checked", true);
                        $("input[name=end_time]").val(data.end_time != '0' ? data.format_end_time : '');
                        $("input[name=take_goods_time]").val(data.take_goods_time != '0' ? data.format_take_goods_time : '');
                        if (data.regimental_only === '1') {
                            $("input[name=regimental_only]").prop('checked', true);
                        } else {
                            $("input[name=regimental_only]").removeAttr('checked');
                        }
                        $("input[name=sort]").val(data.sort);

                        form.render();//设置完值需要刷新表单
                    },
                    error: function () {
                        layer.msg(errorMsg);
                        layer.close(loading);
                    }
                }
            )
        }

//执行添加或编辑
        form.on('submit(sub_goods)', function () {
            pic_urls = '';
            $('.goodsImg').each(function () {
                pic_urls += $(this).attr('src') + ',';
            });

            if (pic_urls === '') {
                layer.msg('请添加商品图');
                return;
            }

            if ($('select[name=m_category_id]').val() == null) {
                layer.msg('请选择商品分组');
                return;
            }

            if (ue.getContent() === '') {
                layer.msg('请填写详细说明', {icon: 1, time: 2000});
                return;
            }

            //获取商品标签
            var label = '';
            $('input[name=label]').each(function () {
                if (Trim(this.value) != '') {
                    label += this.value + ',';
                }
            });

            var subData;
            var ajaxType;
            var ajaxUrl = url;
            //goods_id 存在则为编辑，否则为新增
            if (goods_id) {
                ajaxUrl = url + '/' + goods_id;
                ajaxType = 'put';
                successMsg = sucMsg.put;
            } else {
                ajaxUrl = url;
                ajaxType = 'post';
                successMsg = sucMsg.post;
            }
            //获取规格明细
            var pic_url = [];//规格图片
            var property1_name = [];//规格1名称
            var property1 = '';
            var property2 = '';
            var property2_name = [];//规格2名称
            var stock_price = [];//价格
            var stock_number = [];//库存
            var stock_code = [];//规格编码
            var stock_cost_price = [];//成本价
            var stock_assemble_price = [];//拼团价
            $("img[name='pic_url']").each(function () {
                if ($(this).attr('src') !== '') {
                    pic_url.push($(this).attr('src'));
                }
            });
            if ($("img[name='pic_url']").length !== pic_url.length) {
                layer.msg('请选择规格图片', {icon: 1, time: 2000});
                return;
            }

            $('.property1_name').each(function (j, item) {
                property1_name.push(item.innerText)
            });

            //规格1去重
            if (property1_name.length > 0) {
                property1 = duplicateRemoval(property1_name);
                property1 = property1.join(',');
                //追加 key
                property1 = $('input[name=property1_key]').val() + ':' + property1;
            } else {
                property1 = '';
            }

            $('.property2_name').each(function (j, item) {
                property2_name.push(item.innerText);
            });
            //规格2去重
            if (property2_name.length > 0) {
                property2 = duplicateRemoval(property2_name);
                property2 = property2.join(',');
                //追加 key
                property2 = $('input[name=property2_key]').val() + ':' + property2;
            } else {
                property2 = '';
            }
            var stock_arr;
            var have_stock_type;//判断是否有规格，0没有，1有
            //判断是否有规格
            if (property1 == '' && property2 == '') {
                have_stock_type = 0;
            } else {
                have_stock_type = 1;
                $("input[name='stock_price']").each(function (j, item) {
                    if (item.value !== '0') {
                        stock_price.push(item.value);
                    }
                });
                if ($("input[name='stock_price']").length !== stock_price.length) {
                    layer.msg('价格不能为零', {icon: 1, time: 2000});
                    return;
                }
                $("input[name='stock_number']").each(function (j, item) {
                    stock_number.push(item.value);
                });
                $("input[name='stock_code']").each(function (j, item) {
                    stock_code.push(item.value);
                });
                $("input[name='stock_cost_price']").each(function (j, item) {
                    stock_cost_price.push(item.value);
                });
                $("input[name='stock_assemble_price']").each(function (j, item) {
                    if (is_open_assemble) {
                        if (item.value !== '0') {
                            stock_assemble_price.push(item.value);
                        }
                    }
                });
                if (is_open_assemble) {
                    if ($("input[name='stock_assemble_price']").length !== stock_assemble_price.length) {
                        layer.msg('拼团价不能为零', {icon: 1, time: 2000});
                        return;
                    }
                }
                stock_arr = {
                    'pic_url': pic_url,
                    'property1_name': property1_name,
                    'property2_name': property2_name,
                    'price': stock_price,
                    'number': stock_number,
                    'code': stock_code,
                    'cost_price': stock_cost_price,
                    'assemble_price': stock_assemble_price
                };
            }

            //获取上架时间，并判断有效无效
            var start_time = null;
            var status = 1;
            var start_type = $('input[name=start_time]:checked').val();
            var is_limit = $('input[name=is_limit]:checked').val();
            if (is_limit == undefined) {
                is_limit = 0;
            }
            var limit_number = $('input[name=limit_number]').val();
            if (is_limit == 0 || limit_number == '') {
                limit_number = 0;
            }
            if (start_type == 1) {
                start_time = '';
            } else if (start_type == 2) {
                start_time = $('input[name=diyStartTime]').val();
            } else if (start_type == 3) {
                start_time = '';
                status = 0;
            }
            var end_time = null;
            if ($('input[name=end_time]').val()) {
                end_time = $('input[name=end_time]').val();
            }
            var take_goods_time = null;
            if ($('input[name=take_goods_time]').val()) {
                take_goods_time = $('input[name=take_goods_time]').val();
            }
            var regimental_only = 0;
            if ($('input[name=regimental_only]:checked').val()) {
                regimental_only = 1;
            }
            var assemble_price = $('input[name=assemble_price]').val();
            if (is_open_assemble) {
                if (assemble_price === '0') {
                    layer.msg('拼团价不能为零', {icon: 1, time: 2000});
                    return;
                }
            }
            var attribute = [];
            $('input[name=attribute]').each(function (i, j) {
                //判断是否为空，如果为空则不需要存
                if (Trim(j.value) !== '') {
                    attribute.push(j.value);
                }
            });

            /*diy设置开始*/
            subData = {
                type: type,
                name: $('input[name=name]').val(),
                short_name: $('input[name=short_name]').val(),
                code: $('input[name=code]').val(),
                m_category_id: $('select[name=m_category_id]').val(),
                city_group_id: $('select[name=city_group_id]').val(),
                // band_self_leader_id: $('select[name=band_self_leader_id]').val(),
                label: label,
                pic_urls: pic_urls,
                simple_info: $('textarea[name=simple_info]').val(),
                detail_info: ue.getContent(),
                stock: stock_arr,
                property1: property1,
                property2: property2,
                assemble_price: assemble_price,
                price: $('input[name=price]').val(),
                line_price: $('input[name=line_price]').val(),
                stocks: $('input[name=stocks]').val(),
                sales_number: $('input[name=sales_number]').val(),
                commission_leader_ratio: $('input[name=commission_leader_ratio]').val(),
                // commission_selfleader_ratio: $('input[name=commission_selfleader_ratio]').val(),
                start_type: start_type,
                start_time: start_time,
                end_time: end_time,
                take_goods_time: take_goods_time,
                sort: $('input[name=sort]').val(),
                status: status,
                have_stock_type: have_stock_type,
                stock_type: $('#stock_type').val(),//判断单规格双规格
                video_id: videoData.video_id,
                video_pic_url: videoData.video_pic_url,
                video_url: videoData.video_url,
                is_limit: is_limit,
                regimental_only: regimental_only,//是否团长专属
                unit: $('input[name=unit]').val(),//商品单位
                attribute: attribute,//商品属性
                limit_number: limit_number,
                is_open_assemble: is_open_assemble, //是否拼团商品
                is_check: 0
            };

            if (type == '3') {
                //如果是服务商品，则需要添加是否自动发货
                subData.service_goods_is_ship = $('input[name=service_goods_is_ship]:checked').val() ? 1 : 0;
            }

            //判断是否为拼团商品，如果是，获取拼团信息
            if (is_open_assemble) {
                var is_self = 0;
                if ($('input[name=is_self]:checked').val()) {
                    is_self = 1;
                }
                var is_automatic = 0;
                if ($('input[name=is_automatic]:checked').val()) {
                    is_automatic = 1;
                }
                var older_with_newer = 0;
                if ($('input[name=older_with_newer]:checked').val()) {
                    older_with_newer = 1;
                }
                var is_leader_discount = 0;
                if ($('input[name=is_leader_discount]:checked').val()) {
                    is_leader_discount = 1;
                }
                var is_show = 0;
                if ($('input[name=is_show]:checked').val()) {
                    is_show = 1;
                }
                subData.is_self = is_self;
                subData.is_automatic = is_automatic;
                subData.older_with_newer = older_with_newer;
                subData.is_leader_discount = is_leader_discount;
                subData.is_show = is_show;
                var tuan_type_value = $('input[name=tuan_type]:checked').val();
                subData.tuan_type = tuan_type_value;
                //通过拼团类型判断 多人团 还是 阶梯团，多人团只有一个成团人数，阶梯团为数组
                if (tuan_type_value === '1') {
                    subData.assemble_number = [$('input[name=assemble_number]').val()];//成团人数
                    if (is_leader_discount) {
                        subData.assemble_group_discount = [$('input[name=assemble_group_discount]').val()];//团长优惠比例
                    }
                } else if (tuan_type_value === '2') {
                    var an_arr = [];
                    $('input[name=assemble_number]').each(function (i, j) {
                        an_arr.push(j.value);
                    });
                    //成团人数去重，通过去重前后的数量来判断是否有相同成团人数，如果有重复的，提示重新填写
                    var d_an_arr = duplicateRemoval(an_arr);
                    if (d_an_arr.length !== an_arr.length) {
                        layer.msg('成团人数不能重复', {icon: 1, time: 2000});
                        return;
                    }
                    subData.assemble_number = an_arr;
                    //阶梯团中的拼团价比例
                    var gpd_arr = [];
                    $('input[name=group_price_discount]').each(function (i, j) {
                        gpd_arr.push(j.value);
                    });
                    for (var i = 0; i < gpd_arr.length; i++) {
                        if (gpd_arr[i] <= 0 || gpd_arr[i] > 100) {
                            layer.msg('拼团价比例错误', {icon: 1, time: 2000});
                            return;
                        }
                    }
                    subData.group_price_discount = gpd_arr;
                    if (is_leader_discount) {
                        var ild_arr = [];
                        $('input[name=assemble_group_discount]').each(function (i, j) {
                            ild_arr.push(j.value);
                        });
                        subData.assemble_group_discount = ild_arr;
                    }
                }
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
                    if (res.status == timeOutCode) {
                        layer.msg(timeOutMsg);
                        admin.exit();
                        return false;
                    }
                    if (res.status != 200) {
                        layer.close(loading);//关闭加载图标
                        if (res.status != 204) {
                            layer.msg(res.message);
                        }
                        return false;
                    }
                    layer.msg(successMsg, {
                        icon: 1,
                        time: 2000 //2秒关闭（如果不配置，默认是3秒）
                    }, function () {
                        //更新好物圈商品信息
                        if (goods_id) {
                            getAjaxReturnKey({method: 'shopCircleGoods', type: 'post', data: {goods_id: goods_id}});
                        }
                        layer.close(openIndex);
                        layer.close(loading);//关闭加载图标
                        location.hash = '/ship/goods';
                    });

                },
                error: function () {
                    layer.msg(errorMsg);
                    layer.close(loading);
                }
            })
        });

        //上传图片现方法
        //加载图片库专用js，并将接受img url的div class设置到session，必须
        $(document).off('click', '#putAddBtn').on('click', '#putAddBtn', function () {
            sessionStorage.setItem('images_common_div', '#images');
            sessionStorage.setItem('images_common_div_info', '<div class="images"><img src="" alt="gu' + Math.round(Math.random() * 1e9) + '" class="layui-upload-img goodsImg"><i class="deleteIcon layui-icon-close layui-icon"></i></div>');
            var num = 0;
            $('.goodsImg').each(function () {
                num++;
            });
            if (num < 5) {
                sessionStorage.setItem('images_common_type_uEditor', '0');//设置类型为普通上传
                images_open_index_fun();
            } else {
                layer.msg('最多上传 5 张图片', {icon: 1});
            }
        });

        //添加商品属性
        $(document).off('click', '.attribute_add_button').on('click', '.attribute_add_button', function () {
            //判断已有商品属性数量，不超过6个
            if ($('input[name=attribute]').length > 5) {
                layer.msg('商品属性最多6条', {icon: 1, time: 2000});
                return;
            }
            $('.attribute_list').append('<div class="layui-form-item ">\n' +
                '                                <label class="layui-form-label"></label>\n' +
                '                                <div class="layui-input-inline">\n' +
                '                                    <input name="attribute" class="layui-input">\n' +
                '                                </div>\n' +
                '                                <a href="javascript: void(0)" class="attribute_del_button" style="color: red;">删除</a>\n' +
                '                            </div>');
        });

        //删除商品属性
        $(document).off('click', '.attribute_del_button').on('click', '.attribute_del_button', function () {
            $(this).parent().remove();
        });

    });
    exports('ship/add', {})
});

//新增规格明细方法
function new_specifications(property1_name, property2_name, stock_price, stock_number, stock_code, stock_cost_price, pic_url, stock_assemble_price) {
    if (!property1_name) {
        property1_name = '';
    }
    if (!property2_name) {
        property2_name = '';
    }
    if (!stock_price) {
        stock_price = 0;
    }
    if (!stock_number) {
        stock_number = 0;
    }
    if (!stock_code) {
        stock_code = '';
    }
    if (!stock_cost_price) {
        stock_cost_price = 0;
    }
    if (!pic_url) {
        pic_url = '';
    }
    if (!stock_assemble_price) {
        stock_assemble_price = 0;
    }
    var assemble_price_style = 'none';
    if (is_open_assemble) {
        assemble_price_style = 'block';
    }

    return '<div class="layui-form-item specification_details" style="display: flex;flex-direction: row;justify-content: flex-start;">\n' +
        '                    <div class="layui-input-inline" style="text-align: center;">\n' +
        '                        <span>\n' +
        '                            <i class="layui-icon layui-icon-face-smile main_figure">&#xe61f;</i><input class="addImgPut" type="file"><img class="new_pictures" name="pic_url" src="' + pic_url + '"/>\n' +
        '                        </span>\n' +
        '                    </div>\n' +
        '                    <div class="layui-input-inline" style="width: 100px;">\n' +
        '                        <span class="unified_style property1_name" data="' + property1_name + '">' + property1_name + '</span>\n' +
        '                    </div>\n' +
        '                    <div class="layui-input-inline" style="width: 100px;">\n' +
        '                        <span class="unified_style property2_name" data="' + property2_name + '">' + property2_name + '</span>\n' +
        '                    </div>\n' +
        '                    <div class="layui-input-inline" style="width: 150px;">\n' +
        '                        <span class="unified_style">\n' +
        '                            <span class="asterisk">*</span>价格(元)\n' +
        '                        </span>\n' +
        '                        <input name="stock_price" value="' + stock_price + '" lay-verify="required|number" required\n' +
        '                               class="specification_color1 stock_price">\n' +
        '                    </div>\n' +
        '                    <div class="layui-input-inline" style="width: 150px;">\n' +
        '                        <span class="unified_style">\n' +
        '                            库存：\n' +
        '                        </span>\n' +
        '                        <input name="stock_number" value="' + stock_number + '" lay-verify="number"\n' +
        '                               class="specification_color1 stock_number">\n' +
        '                    </div>\n' +
        '                    <div class="layui-input-inline">\n' +
        '                        <span class="unified_style">规格编码：</span>\n' +
        '                        <input name="stock_code" value="' + stock_code + '" class="specification_color1">\n' +
        '                    </div>\n' +
        '                    <div class="layui-input-inline" style="width: 150px;">\n' +
        '                        <span class="unified_style">成本价：</span>\n' +
        '                        <input name="stock_cost_price" value="' + stock_cost_price + '" lay-verify="number"\n' +
        '                               class=" specification_color1">\n' +
        '                    </div>\n' +
        '                    <div class="assemble_price" style="display: ' + assemble_price_style + ';">' +
        '                        <div class="layui-input-inline" style="width: 150px;">\n' +
        '                            <span class="unified_style">拼团价：</span>\n' +
        '                            <input name="stock_assemble_price" value="' + stock_assemble_price + '" lay-verify="number"\n' +
        '                               class="specification_color1 stock_assemble_price">\n' +
        '                        </div>\n' +
        '                    </div>\n' +
        '                </div>';
}
