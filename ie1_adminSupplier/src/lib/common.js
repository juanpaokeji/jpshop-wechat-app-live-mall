/*
 * 通用方法开始
 */
/**
 * 获取格式化后的日期时间 传入的时间应该为‘毫秒数’，时间戳 * 1000
 * 用法 new Date().format("yyyy-MM-dd hh:mm:ss");//今天日期
 * @param format
 * @returns {*}
 */
Date.prototype.format = function (format) {
    var args = {
        "M+": this.getMonth() + 1,
        "d+": this.getDate(),
        "h+": this.getHours(),
        "m+": this.getMinutes(),
        "s+": this.getSeconds(),
        "q+": Math.floor((this.getMonth() + 3) / 3),  //quarter
        "S": this.getMilliseconds()
    };
    if (/(y+)/.test(format))
        format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var i in args) {
        var n = args[i];
        if (new RegExp("(" + i + ")").test(format))
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? n : ("00" + n).substr(("" + n).length));
    }
    return format;
};

/**
 * js去除字符串头尾空格
 * @param str
 * @returns {*}
 * @constructor
 */
function Trim(str) {
    return str.replace(/(^\s*)|(\s*$)/g, "");
}

/**
 * 数组数字排序函数，sort 方法添加参数函数  用法 arr.sort(sortNumber);
 * @param a
 * @param b
 * @returns {number}
 */
function sortNumber(a, b) {
    return a - b;
}

/**
 * 数组去重
 * @param array
 * @returns {Array}
 */
function duplicateRemoval(array) {
    var temp = []; //一个新的临时数组
    for (var i = 0; i < array.length; i++) {
        if (temp.indexOf(array[i]) == -1) {
            temp.push(array[i]);
        }
    }
    return temp;
}

/**
 * 数组删除指定值 arr：数组 val：需要删除的值
 * @param arr
 * @param val
 * @returns {boolean}
 */
function deleteSpecifiedElement(arr, val) {
    var i = arr.length;
    while (i--) {
        if (arr[i] === val) {
            arr.splice(i, 1);
        }
    }
    return false;
}

/**
 * 将传入的内容复制到剪贴板
 * @param text
 */
function getCopy(text) {
    var oInput = document.createElement('input');
    oInput.value = text;
    document.body.appendChild(oInput);
    oInput.select(); // 选择对象
    document.execCommand("Copy"); // 执行浏览器复制命令
    oInput.className = 'oInput';
    oInput.style.display = 'none';
}

/**
 * 点击图片实现在浏览器中预览 layui 打开方式
 * @param img
 */
function imgClickEvent(img) {
    var imgHtml = "<img style='width: 500px;' src='" + img.src + "' />";
    layer.open({
        type: 1,
        shade: 0.3,
        shadeClose: true,
        title: false,
        area: '500px',
        content: imgHtml,
        cancel: function () {
            // console.log('取消显示')
        }
    })
}

/**
 * 营销菜单获取已购买的插件菜单
 * 仅商城营销菜单使用
 */
function getVoucherMenu() {
    var titles = sessionStorage.getItem('titles');
    var routes = sessionStorage.getItem('routes');
    if (routes && titles) {
        var titleArr = titles.split(',');
        var routeArr = routes.split(',');
        for (var i = 0; i < titleArr.length; i++) {
            if (titleArr[i] != '' && routeArr[i] != '') {
                var href = 'voucher/' + routeArr[i];
                var flag = 0;//判断是否存在
                //循环查找当前页面路由对应的子菜单路由
                $('.id_menu').find('a').each(function (index, j) {
                    var lay_href = $(j).attr('lay-href');
                    //当当前页面路由等于这个子菜单路由时，表示存在，不追加标签
                    if (href === lay_href) {
                        flag = 1;
                    }
                })
                if (!flag) {
                    $('.units_ul').append('<li class="default_delete"><a lay-href="voucher/' + routeArr[i] + '">' + titleArr[i] + '</a></li>');
                }
            }
        }
    }
}

/**
 * 判断是否已加载指定js
 * @param name
 * @returns {boolean}
 */
function isIncludeJS(name) {
    var js = /js$/i.test(name);
    var es = document.getElementsByTagName(js ? 'script' : 'link');
    for (var i = 0; i < es.length; i++)
        if (es[i][js ? 'src' : 'href'].indexOf(name) != -1) return true;
    return false;
}
/*
 * 通用方法结束
 */


/*
 * ajax 通用方法开始
 * 以下为 ajax 通用方法配置，如有字段重复，请修改
 * 其他通用方法，为避免不必要的麻烦尽量写在上面的通用方法里
 */

var loading;//定义加载效果
var result = '';//返回ajax请求的结果给调用者

/**
 * 通用layer自带获取列表方法
 * 验证必传数据 name elem method cols
 * @param arr
 * @returns {*}
 */
function getTableRender(arr) {
    result = false;//重置返回
    //验证 table.render 请求必传参
    if (!checkRequiredData(arr, ['name', 'elem', 'method', 'cols'])) {
        //只要有一个未传，则返回false
        return false;
    }
    var limit = 10;//列表中每页显示数量
    var limits = [10, 20, 30];//自定义列表每页显示数量
    var page = true;//是否开启分页，默认开启
    if (arr.page === false) {
        page = false;
    }
    arr.name = layui.table.render({ //name 为可操作的 render 对象名称
        elem: arr.elem,//需要加载的 table 表格对应的 id
        url: layui.setter.baseUrl + '/' + arr.method, //数据接口 请求的 api 接口方法和可能携带的参数 key
        page: page, //是否开启分页，默认开启
        skin: 'nob',//数据列表去除横竖线
        even: true,//隔行背景
        limit: arr.limit ? arr.limit : limit,//列表中每页显示数量
        limits: arr.limits ? arr.limits : limits,//自定义列表每页显示数量
        loading: true,//是否显示加载条（默认：true）。如果设置 false，则在切换分页时，不会出现加载条。该参数只适用于 url 参数开启的方式
        headers: {'Access-Token': layui.data(layui.setter.tableName).access_token},//请求头部，一般是登录后存的用户信息
        cols: arr.cols,//加载的表格字段
        response: {
            statusName: 'status', //数据状态的字段名称，默认：code
            statusCode: "200", //成功的状态码，默认：0
            dataName: 'data' //数据列表的字段名称，默认：data
        },
        done: function (res) {
            layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
            if (res.status === layui.setter.timeOutCode) {//token错误代码
                layer.msg(layui.setter.timeOutMsg, {icon: 1, time: 2000});//token错误提示
                layui.admin.exit();
                return false;
            }
            if (res.status !== 200 && res.status !== 204) {
                layer.msg(res.message, {icon: 1, time: 2000});
                return false;
            }
            result = res;
        }
    });
    return arr.name;
}

/**
 * 对 ajax 请求做通用方法 带 key
 * 一般列表可以用 layui 自带 table.render
 * 如果需要自定义列表，则需要自己拼写 参数 params，追加 page 和 limit
 * @param arr  //ajax请求传参 一般包含 method、type，可能包含 params(get)、data(post、delete、put)
 * @returns {boolean}
 */
function getAjaxReturnKey(arr) {
    //验证 ajax 请求必传参
    if (!checkRequiredData(arr, ['method', 'type'])) {
        //只要有一个未传，则返回false
        return false;
    }
    //验证通过后拼接url
    var saa_key = sessionStorage.getItem('supplier_saa_key');
    if (arr.type !== 'get') {
        if (arr.data) {
            arr.data.key = saa_key;
        } else {
            arr.data = {'key': saa_key};
        }
    }
    var key = '?key=' + saa_key;//key为必需项，在参数最开始的地方
    var params = arr.params ? '&' + arr.params : '';//params为非必需
    var url = layui.setter.baseUrl + '/' + arr.method + key + params;
    return ajaxBase(url, arr);
}

/**
 * 对 ajax 请求做通用方法 不带key
 * 列表：一般列表可以用 layui 自带 table.render
 *       自定义列表，则需要自己拼写 参数 params，追加 page 和 limit，若拼接的参数已加在 method 中，则 params 不需要传
 * @param arr  //ajax请求传参 一般包含 method、type，可能包含 params
 * @returns {boolean}
 */
function getAjaxReturn(arr) {
    if (!checkRequiredData(arr, ['method', 'type'])) {
        return false;
    }
    var params = arr.params ? '&' + arr.params : '';//params为非必需
    var url = layui.setter.baseUrl + '/' + arr.method + params;
    return ajaxBase(url, arr);
}

/**
 * ajax请求，最终返回请求结果
 * 大部分的ajax请求设置只需要更改该方法
 * @param url
 * @param arr
 * @returns {*}
 */
function ajaxBase(url, arr) {
    result = false;//重置返回
    var async = false;//默认为同步请求，只有该请求执行完才会执行其他请求，暂时只支持同步请求
    // //判断同步异步，如果 async 存在并等于 true，则调用者设置了该方法为异步，否则默认为同步
    // //由于暂时未处理异步数据，所以只支持同步，异步请求可单独写
    // if (arr.async && arr.async === true) {
    //     async = true;
    // }
    $.ajax({
        url: url,
        type: arr.type,
        data: arr.data ? arr.data : {},//判断是否有非get传参
        async: async,
        headers: {'Access-Token': layui.data(layui.setter.tableName).access_token},//请求头部，一般是登录后存的用户信息
        success: function (res_data) {
            layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
            var res = res_data;
            //判断接口返回类型是否为规范的对象，如果不是，转为对象，判断状态是否为token错误，如果是退出返回到首页，如果不是，显示错误信息
            if (typeof res !== 'object') {
                res_data = eval('(' + res_data + ')');
                if (res_data.status) {
                    if (res_data.status !== layui.setter.timeOutCode) {
                        layer.msg(res, {icon: 1, time: 2000});
                    } else {
                        layer.msg(layui.setter.timeOutMsg, {icon: 1, time: 2000});//token错误提示
                        layui.admin.exit();
                    }
                } else {
                    layer.msg(res, {icon: 1, time: 2000});
                }
                return false;
            }
            if (res.status === layui.setter.timeOutCode) {//token错误代码
                layer.msg(layui.setter.timeOutMsg, {icon: 1, time: 2000});//token错误提示
                layui.admin.exit();
                return false;
            }
            if (res.status === 500) {
                layer.msg(res.message, {icon: 1, time: 2000});
                return false;
            }
            result = res;
        },
        error: function () {
            result = false;
            layer.msg(layui.setter.errorMsg, {icon: 1, time: 2000});//接口错误提示
            layer.close(loading);//关闭加载图标，对应 beforeSend 中的加载
        },
        beforeSend: function () {
            loading = layer.load(1, {shade: 0.3});//layer.open 类型和 shade 属性 加载
        }
    });
    return result;
}

/*
 * ajax 通用方法结束
 */

/**
 * 判断是否缺少必传参方法通用方法
 * @param arr   需要判断的参数
 * @param check 需要验证的字段
 * @returns {boolean}
 */
function checkRequiredData(arr, check) {
    for (var i = 0; i < check.length; i++) {
        if (!arr[check[i]]) {
            layer.msg('缺少请求参数 ' + check[i], {icon: 1, time: 2000});
            return false;
        }
    }
    return true;
}
