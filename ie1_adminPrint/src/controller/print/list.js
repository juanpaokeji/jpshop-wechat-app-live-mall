/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 创建于 2019/4/16
 * js 批量打印列表
 */

layui.define(function (exports) {
    /**
     * use 首参简单解释
     *
     * jquery 必须 很多地方那个用到，必须定义
     * setter 必须 获取config 配置，但不必定义
     * admin 必须 若未用到则不必定义
     * table 不必须 若表格渲染，若无表格操作点击事件，可不必定义
     * form 不必须 表单操作，一般用于页面有新增和编辑
     * laydate 不必须 日期选择器
     */
    layui.use(['jquery', 'setter', 'admin', 'laydate', 'laypage', 'element','table'], function () {
        var $ = layui.$;
        var setter = layui.setter;//配置
        var layDate = layui.laydate;
        var laypage = layui.laypage;
		var element = layui.element
		var table = layui.table
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var arr = {};//全局ajax请求参数
        var order_sn_list = [];//批量执行打印需要的订单编号数组
        var is_tuan_list = [];//批量执行打印需要的订单编号数组
        var stayorder_list = {};//待发货订单数组
        var express_type = {};//待发货订单数组
        var total_count = 0;//订单总数，分页用
        var pageLimit = 1000;//查询使用到的每页显示数量，只需要初始化与 limit 相同即可
        var limit = 1000;//列表中每页显示数量
        var tabPage = 1;//获取当前分页的页数
        var user_id = 0;//买家ID
        var all_orders = [];//查询到的所有信息，方便通过订单编号查找订单信息
		var open_index,arr= {}
        // var LODOP=getLodop();
        /*diy设置开始*/
        if (layui.router().search.key && layui.router().search.key !== '') {
            sessionStorage.setItem('saa_key', layui.router().search.key);//将key存入session
        } else {
            layer.msg('错误请求', {icon: 1, time: 2000}, function () {
                window.close();
                layer.msg('请关闭当前页面', {icon: 1, time: 2000});
            });
        }


        //====判断是否需要 Web打印服务CLodop:===
        //===(不支持插件的浏览器版本需要用它)===
        function needCLodop() {
            try {
                var ua = navigator.userAgent;
                if (ua.match(/Windows\sPhone/i)){
                    return true;
                }

                if (ua.match(/iPhone|iPod|iPad/i)){
                    return true;
                }
                if (ua.match(/Android/i)){
                    return true;
                }
                if (ua.match(/Edge\D?\d+/i)){
                    return true;
                }
                var verTrident = ua.match(/Trident\D?\d+/i);
                var verIE = ua.match(/MSIE\D?\d+/i);
                var verOPR = ua.match(/OPR\D?\d+/i);
                var verFF = ua.match(/Firefox\D?\d+/i);
                var x64 = ua.match(/x64/i);
                if ((!verTrident) && (!verIE) && (x64)){
                    return true;
                }else if (verFF) {
                    verFF = verFF[0].match(/\d+/);
                    if ((verFF[0] >= 41) || (x64)){
                        return true;
                    }
                } else if (verOPR) {
                    verOPR = verOPR[0].match(/\d+/);
                    if (verOPR[0] >= 32){
                        return true;
                    }
                } else if ((!verTrident) && (!verIE)) {
                    var verChrome = ua.match(/Chrome\D?\d+/i);
                    if (verChrome) {
                        verChrome = verChrome[0].match(/\d+/);
                        if (verChrome[0] >= 41){
                            return true;
                        }
                    }
                }
                return false;
            } catch (err) {
                return true;
            }
        }

        //====页面引用CLodop云打印必须的JS文件,用双端口(8000和18000）避免其中某个被占用：====
        if (needCLodop()) {
            var src1 = "http://localhost:8000/CLodopfuncs.js?priority=1";
            var src2 = "http://localhost:18000/CLodopfuncs.js?priority=0";

            var head = document.head || document.getElementsByTagName("head")[0] || document.documentElement;
            var oscript = document.createElement("script");
            oscript.src = src1;
            head.insertBefore(oscript, head.firstChild);
            oscript = document.createElement("script");
            oscript.src = src2;
            head.insertBefore(oscript, head.firstChild);
            CLodopIsLocal = !!((src1 + src2).match(/\/\/localho|\/\/127.0.0./i));
        }

        //获取当前系统位数
        var agent = navigator.userAgent.toLowerCase();
        var systemBit
        if(agent.indexOf("win64")>=0||agent.indexOf("wow64")>=0) {
            systemBit = 64;
        } else {
            systemBit = 32;
        }

        //====获取LODOP对象的主过程：====
        function getLodop() {
            var LODOP;
            try {
                try {
                    LODOP = getCLodop();
                } catch (err) {}
                if (!LODOP && document.readyState !== "complete") {
                    alert("网页还没下载完毕，请稍等一下再操作.");
                    return;
                }
                if (!LODOP) {
                    layer.open({
                        title:'下载打印控件',
                        content: '未安装LODOP控件，请下载安装后刷新页面',
                        btn: ['确认', '取消'],
                        yes: function (index) {
                            window.location.href = 'http://img.xiguaje.com/CLodop.exe';
                            // if (systemBit == 64){
                            //     window.location.href = 'http://www.lodop.net/download/CLodop_Setup_for_Win64NT_3.083Extend.zip';
                            // } else {
                            //     window.location.href = 'http://www.lodop.net/download/CLodop_Setup_for_Win32NT_https_3.083Extend.zip';
                            // }
                            layer.close(index);
                        },
                        btn2: function () {

                        },
                        cancel: function () {
                            //右上角关闭回调
                        }
                    })
                }
                // LODOP.SET_LICENSES("","13528A153BAEE3A0254B9507DCDE2839","","");
                LODOP.SET_LICENSES("","7664EF00752AB8C6FF9E1F6906FB9256","C94CEE276DB2187AE6B65D56B3FC2848","");
                return LODOP;
            } catch (err) {}
        }


		var myTemps = getAjaxReturnKey({method:'merchantPrintingtemp',type:'get'})
		var sysTemps = getAjaxReturn({method:'adminPrinttemp',type:'get'})
		var allArray = []
		if(sysTemps.status === 200){
			myTemps.data && myTemps.data.forEach(function(e){
				sysTemps.data && sysTemps.data.forEach(function(a,index){
					e.english_name === a.english_name && sysTemps.data.splice(index,1)
				})
			})
			if(myTemps.status != 200){
				allArray = sysTemps.data
			}else{
				sysTemps.data.forEach(function(e){
					myTemps.data.push(e)
				})
				allArray = myTemps.data
			}
			allArray && allArray.forEach(function(e){
                tempId = e.system_express_template_id?e.system_express_template_id:e.id
                switch (e.english_name) {
                    case 'leader_order':
                        $('.footer_div').find("[data-engshilname='leader_order']").attr('data-tempId',tempId).attr('data-width',e.width).attr('data-height',e.height)
                        break
                    case 'Invoice':
                        $('.footer_div').find("[data-engshilname='Invoice']").attr('data-tempId',tempId).attr('data-width',e.width).attr('data-height',e.height)
                        break
                    case 'purchasing_order':
                        $('.footer_div').find("[data-engshilname='purchasing_order']").attr('data-tempId',tempId).attr('data-width',e.width).attr('data-height',e.height)
                        break
                    case 'distribution_bill':
                        $('.footer_div').find("[data-engshilname='distribution_bill']").attr('data-tempId',tempId).attr('data-width',e.width).attr('data-height',e.height)
                        break
                    case 'route_sheet':
                        $('.footer_div').find("[data-engshilname='route_sheet']").attr('data-tempId',tempId).attr('data-width',e.width).attr('data-height',e.height)
                        break
                    default:

                }
			})
		}

		$(document).off('click','input[type=checkbox]').on('click','input[type=checkbox]',function(){
			if($(this).is(':checked')){
				$('.btns').each(function(){
					$(this).removeClass('layui-btn-disabled')
				})
			}
		})
        //订单列表导出
        $('.exportcsv').on('click',function () {
            if (order_sn_list.length <= 0) {
                layer.msg('未选择订单', {icon: 1, time: 2000});
                return;
            }
            arr = {
                method: 'merchantPrintsOrders',
                type: 'get',
                data: {
                    page:tabPage,
                    limit:pageLimit,
                    status:$('#status').val(),
                }
            };
            var res = getAjaxReturnKey(arr);
            var orderlist = []

            if (res.status != 200) {
                layer.msg(res.message, {icon: 2, time: 2000});
                return;
            } else {
                if ($('#status').val() == 1){
                    for (var i = 0; i < res.count; i++) {
                        for (var k = 0; k < res.data[i].length; k++) {
                            for (var j = 0; j < order_sn_list.length; j++) {
                                if (res.data[i][k]['order_sn'] == order_sn_list[j]){
                                    orderlist[j] = res.data[i][k];
                                }
                            }
                        }
                    }
                } else {
                    for (var i = 0; i < res.count; i++) {
                        for (var j = 0; j < order_sn_list.length; j++) {
                            if (res.data[i]['order_sn'] == order_sn_list[j]){
                                orderlist[j] = res.data[i];
                            }
                        }
                    }
                }
                toLargerCSV(orderlist);
            }
        })

        function toLargerCSV(data) {
            //CSV格式可以自己设定，适用MySQL导入或者excel打开。
            //由于Excel单元格对于数字只支持15位，且首位为0会舍弃 建议用 =“数值”

            var str = '行号,用户编码,姓名,手机号,收件地址,订单状态,快递单号,订单图片,订单规格,宝贝数量,留言备注\n';

            for (var i = 0; i < data.length; i++) {
                var status = '类型错误';
                if (data[i].status === '0') {
                    status = '待付款';
                } else if (data[i].status === '1') {
                    status = '待发货';
                } else if (data[i].status === '2') {
                    status = '已取消';
                } else if (data[i].status === '3') {
                    status = '已发货';
                } else if (data[i].status === '4') {
                    status = '已退款';
                } else if (data[i].status === '5') {
                    status = '退款中';
                } else if (data[i].status === '6') {
                    status = '待评价';
                } else if (data[i].status === '7') {
                    status = '已完成';
                } else if (data[i].status === '8') {
                    status = '已删除';
                } else if (data[i].status === '9') {
                    status = '一键退款';
                } else {
                    status = '类型错误';
                }

                var order_pic_url = '';
                var order_number = '';
                var order_property1_name = '';
                for (var j = 0; j < data[i].order.length; j++) {
                    order_pic_url += data[i].order[j].pic_url + ';'
                    order_number += data[i].order[j].number
                    order_property1_name += data[i].order[j].property1_name + ';'
                }

                var data_arr = [
                    data[i].user_id,
                    data[i].name,
                    data[i].phone,
                    data[i].address,
                    status,
                    data[i].order[0].express_number,
                    order_pic_url,
                    order_property1_name,
                    order_number,
                    data[i].remark
                ];

                str += (i + 1).toString() + ',' + data_arr.join(',') + ',\n'
            }
            var blob = new Blob([str], {type: "text/plain;charset=utf-8"});
            //解决中文乱码问题
            blob = new Blob([String.fromCharCode(0xFEFF), blob], {type: blob.type});
            var object_url = window.URL.createObjectURL(blob);
            var link = document.createElement("a");
            link.href = object_url;
            link.download = "导出.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
		//配置模板开始
		$('.configeTemp').on('click',function(){
			open_index = layer.open({
			    type: 1,
			    title: '配制模板',
			    content: $('.tempModel'),
			    shade: 0,
			    offset: '100px',
			    area: ['1000px', '500px'],
			    cancel: function () {
			        $('.tempModel').hide()
			    }
			})
			myTemp()
		})

		function myTemp(){
            var saa_key = sessionStorage.getItem('saa_key');
			var cols = [
			    {field: 'id', title: 'ID'},
			    {field: 'name', title: '模板名称'},
			    {field: 'english_name', title: '英文名称'},
			    {field: 'format_create_time', title: '创建时间'},
			    {field: 'operations', title: '操作', toolbar: '#Operations'}
			]
			arr = {
			    name: 'render',//可操作的 render 对象名称
			    elem: '#myPageTable',//需要加载的 table 表格对应的 id
			    method: 'merchantPrintingtemp?key='+saa_key,//请求的 api 接口方法
			    cols: [cols],//加载的表格字段
			}
			var render = getTableRender(arr)
		}

		//监听tab切换
		element.on('tab(tab)', function (e) {
		    var index = e.index;
		    if (index === 0) {
		        //我的模板
				myTemp()
		    } else if (index === 1) {
		        //系统模板
				sysTemp()
		    }
		})
		
		function sysTemp(){
			var cols = [
			    {field: 'id', title: 'ID'},
			    {field: 'name', title: '模板名称'},
			    {field: 'english_name', title: '英文名称'},
			    {field: 'format_create_time', title: '创建时间'},
			    {field: 'operations', title: '操作', toolbar: '#operations'}
			]
			arr = {
			    name: 'render',//可操作的 render 对象名称
			    elem: '#sysPageTable',//需要加载的 table 表格对应的 id
			    method: 'adminPrinttemp',//请求的 api 接口方法
			    cols: [cols],//加载的表格字段
			}
			var render = getTableRender(arr)
		}
		//选用
		table.on('tool(sysPageTable)',function(obj){
			var id = obj.data.id
			var layEvent = obj.event
			layer.confirm('确定要选用这个模板么?', function (index) {
			    layer.close(index)
			    if (getAjaxReturnKey({ method: 'merchantPrintingtemp', type: 'post',data:{id:id}})) {
			        layer.msg('选用成功')
			    }
			})
		})
		
		//我的模板
		table.on('tool(myPageTable)',function(obj){
			var id = obj.data.id
			var layEvent = obj.event
			if (layEvent === 'edit'){
				sessionStorage.setItem('printTempId',id)
			}else if (layEvent === 'del') {
			    layer.confirm('确定要删除这条数据么?', function (index) {
			        layer.close(index)
			        if (getAjaxReturn({ method: 'merchantPrintingtemp/' + id, type: 'delete'})) {
			            layer.msg('删除成功')
			            obj.del()
			        }
			    })
			}
		})
		
		//配制模板结束

        //选择日期
        layDate.render({
            elem: '#time_type_value',
            type: 'datetime',
            range: true,
        });

        //获取电子面单列表
        arr = {
            method: 'merchantElectronics',
            type: 'get',
            params: 'status=1'
        };
        var res = getAjaxReturnKey(arr);
        if (res && res.data) {
            var ele_list = res.data;
            var ele_list_len = ele_list.length;
            for (var ele = 0; ele < ele_list_len; ele++) {
                var ele_id = ele_list[ele].id;
                var ele_name = ele_list[ele].express_name;
                $('.express_list').append('<input type="radio" id="express_' + ele_id + '" name="express"><label>' + ele_name + '</label>');
            }
        }

        //搜索按钮点击事件
        $(document).off('click', '.search_btn').on('click', '.search_btn', function () {
            //每次重新搜索情清空之前数据
            order_sn_list = [];
            is_tuan_list = [];
            stayorder_list = {};
            express_type = {};
            getList();
            if (total_count > pageLimit) {
                getPage();
            }
        });

        //获取订单列表
        function getList() {
            var time_type = $('#time_type').val();
            var time_type_value = $('input[name=time_type_value]').val()
            if (time_type === '' && time_type_value !== '') {
                layer.msg('未选择搜索时间类型', {icon: 1, time: 2000});
                return;
            }
            var type = $('#type').val();
            var type_value = $('input[name=type_value]').val();
            if (type === '' && type_value !== '') {
                layer.msg('未选择查询条件', {icon: 1, time: 2000});
                return;
            }
            var print_type = $('#print_type').val();
            var is_print = $('#is_print').val();
            if (print_type === '' && is_print !== '') {
                layer.msg('未选择打印类型', {icon: 1, time: 2000});
                return;
            }
            total_count = 0;
            all_orders = [];
            $('.order_list').empty();
            var params = '';
            params += 'limit=' + pageLimit;
            params += '&page=' + tabPage;
            params += '&time_type=' + time_type;
            params += '&time_type_value=' + time_type_value;
            params += '&status=' + $('#status').val();
            params += '&type=' + type;
            params += '&type_value=' + type_value;
            params += '&print_type=' + print_type;
            params += '&is_print=' + is_print;
            arr = {
                method: 'merchantPrintsOrders',
                type: 'get',
                // params: 'limit=2',
                params: params,
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var order_list = all_orders = res.data;
                var order_list_len = order_list.length;
                total_count = res.count;
                for (var i = 0; i < order_list_len; i++) {
                    //查询待发货状态时，同用户订单合并在一起,另做处理
                    if ($('#status').val() == 1){
                        $('.order_list').append(getStayOrderDiv(order_list[i]));
                    } else {
                        $('.order_list').append(getOrderDiv(order_list[i]));
                    }
                    if(i%2==0){
                        $('.order_list').children('li')[i].style.background='#e4e5e8';
                    }
                }
            }
            $('.user_num').html(res.user ? res.user : 0);
            $('.order_num').html(res.count);
        }

        //默认列表分页
        function getPage() {
            laypage.render({
                elem: 'page' //注意，这里的 page 是 ID，不用加 # 号
                , count: total_count //数据总数，从服务端得到
                , prev: '<'
                , next: '>'
                , limit: pageLimit
                , limits: [limit, limit * 2, limit * 3]
                , layout: ['prev', 'page', 'next', 'refresh', 'skip', 'limit']
                , jump: function (obj, first) {
                    pageLimit = obj.limit;
                    tabPage = obj.curr;
                    //首次不执行
                    if (!first) {
                        getList();
                    }
                }
            });
        }

        //打印模板单按钮点击事件
        $(document).off('click', '.print_temp').on('click', '.print_temp', function () {
            if (order_sn_list.length <= 0) {
                layer.msg('未选择订单', {icon: 1, time: 2000});
                return;
            }
            var id = $(this).attr('data-tempId'),str = ''
            // var tempType = $(this).html().substring($(this).html().length-3) //订单类型
            var height = Math.ceil($(this).attr('data-height')) + 2  //此处+2是设置模板宽高时，边框会增加2px，不+2，会导致超出高度，多一页空白
            var width = Math.ceil($(this).attr('data-height')) + 2
            // var height = Math.ceil($(this).attr('data-height')/37.8)  //px转为cm
            // var width = Math.ceil($(this).attr('data-width')/37.8)  //px转为cm
            order_sn_list.forEach(function(e){
                str += e + ','
            })
            var res = getAjaxReturnKey({method:'merchantTuanordertemp',type:'get',data:{id:id,order_ids:str.substring(0,str.length-1)}})
            if(res.status === 200){
                getLodop()
                // LODOP.SET_LICENSES("","7664EF00752AB8C6FF9E1F6906FB9256","C94CEE276DB2187AE6B65D56B3FC2848","");
                // LODOP.SET_PRINT_PAGESIZE(1,width+'cm',height+'cm',"") //设置纸张大小
                LODOP.SELECT_PRINTER();
                LODOP.On_Return=function(TaskID,Value){
                    if(Value>=0) {
                        LODOP.SET_PRINTER_INDEX(Value)
                        for (var i = 0; i < res.data.length; i++) {
                            // LODOP.ADD_PRINT_TEXT(10, "40%", "100%", "100%", tempType) //设置抬头订单类型
                            // LODOP.SET_PRINT_STYLEA(1,"FontSize",21);  //抬头字体
                            LODOP.ADD_PRINT_HTM(0, 0, width, height, res.data[i]);
                            // LODOP.PREVIEW();//打印预览
                            LODOP.PRINT();//直接打印
                        }
                    }
                }
            }
        });

        //打印快递单按钮点击事件
        $(document).off('click', '.print_express_order').on('click', '.print_express_order', function () {
            if ($('input[name=express]:checked').length === 0) {
                layer.msg('未选择快递模板', {icon: 1, time: 2000});
                return;
            }
            if (order_sn_list.length <= 0) {
                layer.msg('未选择订单', {icon: 1, time: 2000});
                return;
            }
            var express_id = $($('input[name=express]:checked')[0]).attr('id').split('_')[1];

            if ($('#status').val() == 1){
                var stayorder_arr = []
                var express_type_arr = []
                for(let i in stayorder_list){
                    stayorder_arr.push(stayorder_list[i])
                }
                for(let i in express_type){
                    express_type_arr.push(express_type[i])
                }
                var subData = {
                    order_sn: stayorder_arr,
                    electronics_id: express_id,
                    type: express_type_arr
                };
            } else {
                var stayorder_arr = []
                var express_type_arr = []
                var k = 0
                for(let i in stayorder_list){
                    for (var j = 0; j < stayorder_list[i].length; j++){
                        stayorder_arr[k] = []
                        stayorder_arr[k].push(stayorder_list[i][j])
                        k++
                    }
                }
                k = 0
                for(let i in express_type){
                    for (var j = 0; j < express_type[i].length; j++){
                        express_type_arr[k] = []
                        express_type_arr[k].push(express_type[i][j])
                        k++
                    }
                }
                var subData = {
                    order_sn: stayorder_arr,
                    electronics_id: express_id,
                    type: express_type_arr
                };
            }
            arr = {
                method: 'merchantPrintsOrders',
                type: 'post',
                data: subData,
            };
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {
                var order_list = res.data;
                var order_list_len = order_list.length;
                var str = '';
                for (var i = 0; i < order_list_len; i++) {
                    str += order_list[i].PrintTemplate;
                    $($('#' + order_list[i].order_sn).parent().parent().find('.express_number')[0]).val(order_list[i].express_number);
                }
                if (str !== '') {
                    getLodop()
                    // LODOP.SET_LICENSES("","7664EF00752AB8C6FF9E1F6906FB9256","C94CEE276DB2187AE6B65D56B3FC2848","");
                    LODOP.ADD_PRINT_HTM(0, 0, "100%", "100%", str);
                    // LODOP.PREVIEW();//打印预览
                    LODOP.PRINT();//直接打印
                }
            }
        });

        //发货按钮点击事件
        $(document).off('click', '.deliver_goods').on('click', '.deliver_goods', function () {
            if ($('input[name=express]:checked').length === 0) {
                layer.msg('未选择快递模板', {icon: 1, time: 2000});
                return;
            }
            if (order_sn_list.length <= 0) {
                layer.msg('未选择订单', {icon: 1, time: 2000});
                return;
            }
            var express_numbers = [];//对应的快递单号
            var express_id = $($('input[name=express]:checked')[0]).attr('id').split('_')[1];//快递模板

            if ($('#status').val() == 1){
                //待发货订单处理
                $('input[name=user_checkbox]:checked').each(function () {
                    if (express_id == 0){
                        $($(this).parent().parent().find('.express_number')[0]).val('本地配送');
                    }
                    var express_number = $($(this).parent().parent().find('.express_number')[0]).val();
                    if (express_number == '') {
                        layer.msg($(this).parent().next().next().html() + ' 的订单未填写快递单号', {icon: 1, time: 2000});
                        return false;
                    }
                    express_numbers.push(express_number);
                })
            } else {
                for (var o = 0; o < order_sn_list.length; o++) {
                    if (express_id == 0){
                        $($('#' + order_sn_list[o]).parent().parent().find('.express_number')[0]).val('本地配送');
                    }
                    var express_number = $($('#' + order_sn_list[o]).parent().parent().find('.express_number')[0]).val();
                    if (express_number == '') {
                        layer.msg('订单号为 ' + order_sn_list[o] + ' 的订单未填写快递单号', {icon: 1, time: 2000});
                        return;
                    }
                    express_numbers.push(express_number);
                }
            }
            var a_o_len = all_orders.length;
            //循环判断选中的订单状态是否都是待发货
            for (var i = 0; i < a_o_len; i++) {
                if (order_sn_list.indexOf(all_orders[i].order_sn) !== -1) {
                    if (all_orders[i].status !== '1') {
                        layer.msg('订单号为 ' + all_orders[i].order_sn + ' 的订单不能发货', {icon: 1, time: 2000});
                        return;
                    }
                }
            }
            var subData = {
                order_sn: order_sn_list,
                express_number: express_numbers,
                electronics_id: express_id,
                is_tuan: is_tuan_list
            };

            arr = {
                method: 'merchantPrintsOrdersSend',
                type: 'put',
                data: subData,
            };
            var res = getAjaxReturnKey(arr);
            if (res) {
                //更新好物圈订单信息
                for (var i = 0; i < order_sn_list.length; i++) {
                    getAjaxReturnKey({method: 'shopCircleOrder/' + order_sn_list[i], type: 'put'});
                }
                layer.msg(res.message, {icon: 1, time: 2000}, function () {
                    getList();
                    if (total_count > pageLimit) {
                        getPage();
                    }
                    order_sn_list = [];
                    is_tuan_list = [];
                });
            }
        });

        //点击展开或收起事件
        $(document).off('click', '.table_ul_top p span').on('click', '.table_ul_top p span', function () {
            //原背景颜色
            var bgcolor = $(this).parent('p').parent('.table_ul_top').parent('li').next().next().css('background-color')
            if (!bgcolor){
                bgcolor = $(this).parent('p').parent('.table_ul_top').parent('li').prev().prev().css('background-color')
            }
            if ($(this).parent('p').parent('.table_ul_top').siblings('.table_ul_bottom').css('display') == 'none') {
                $(this).parent('p').parent('.table_ul_top').parent('li').css({
                    'background': '#00cea7',
                    'padding-bottom': '20px'
                });
                $(this).html('收起');
                $(this).parent('p').parent('.table_ul_top').parent('li').siblings('li').children('.table_ul_top').children('p').children('span').html('展开');
                $('.table_ul_bottom').slideUp(200);
                $(this).parent('p').parent('.table_ul_top').siblings('.table_ul_bottom').slideDown(200);
            } else {
                $(this).html('展开');
                $(this).parent('p').parent('.table_ul_top').parent('li').css({
                    'background': bgcolor,
                    'padding-bottom': '0'
                });
                $(this).parent('p').parent('.table_ul_top').siblings('.table_ul_bottom').slideUp(200);
            }
        });

        //点击展开中的复制地址事件
        $(document).off('click', '.copy_address').on('click', '.copy_address', function () {
            getCopy($(this).prev().val());
            layer.msg('复制成功', {icon: 1, time: 2000});
        });

        //全选按钮点击事件 默认 先功能 后样式
        $(document).off('click', '.all_checkbox').on('click', '.all_checkbox', function () {
            var bool = $(this)[0].checked
            if (bool) {
                $('.checkbox').each(function () {
                    order_sn_list.push($(this).attr('id'));
                    is_tuan_list.push($(this).attr('data-is_tuan'));
                    if ($('#status').val() != 1){
                        user_id = $(this).parent().next().html()
                        if (!stayorder_list.hasOwnProperty(user_id)){
                            stayorder_list[user_id] = [];
                            express_type[user_id] = [];
                        }
                        stayorder_list[user_id].push($(this).attr('id'));
                        if ($(this).attr('data-express_type') == 1 || $(this).attr('data-express_type') == 2){
                            express_type[user_id].push(1);
                        } else {
                            express_type[user_id].push(0);
                        }
                    }
                })
                if ($('#status').val() == 1){
                    $('.user_checkbox').each(function () {
                        user_id = $(this).parent().next().html()
                        stayorder_list[user_id] = [];
                        express_type[user_id] = [];
                        $(this).parents('.table_ul_top').next().find('.checkbox').each(function () {
                            stayorder_list[user_id].push($(this).attr('id'));
                            if ($(this).attr('data-express_type') == 1 || $(this).attr('data-express_type') == 2){
                                express_type[user_id].push(1);
                            } else {
                                express_type[user_id].push(0);
                            }
                        })
                    })
                }
            } else {
                order_sn_list = [];
                is_tuan_list = [];
                stayorder_list = {};
                express_type = {};
            }
            $('.checkbox').prop('checked', bool)
            $('.user_checkbox').prop('checked', bool)
			if($('input[type=checkbox]:checked').length === 0){
				$('.btns').each(function(){
					$(this).addClass('layui-btn-disabled')
				})
			}
        })

        //待发货列表单选按钮点击事件
        $(document).off('click', '.user_checkbox').on('click', '.user_checkbox', function () {
            user_id = $(this).parent().next().html()
            var bool = $(this)[0].checked
            if (bool) {
                stayorder_list[user_id] = [];
                express_type[user_id] = [];
                $(this).parents('.table_ul_top').next().find('.checkbox').each(function () {
                    order_sn_list.push($(this).attr('id'));
                    is_tuan_list.push($(this).attr('data-is_tuan'));
                    stayorder_list[user_id].push($(this).attr('id'));
                    if ($(this).attr('data-express_type') == 1 || $(this).attr('data-express_type') == 2){
                        express_type[user_id].push(1);
                    } else {
                        express_type[user_id].push(0);
                    }
                })
            } else {
                $(this).parents('.table_ul_top').next().find('.checkbox').each(function () {
                    var _this_id = $(this).attr('id')
                    var _this_is_tuan = $(this).attr('data-is_tuan')
                    order_sn_list.forEach(function (e,index) {
                        if (e == _this_id){
                            order_sn_list.splice(index,1)
                            is_tuan_list.splice(index,1)
                        }
                    })
                    for(let i in stayorder_list){
                        stayorder_list[i].forEach(function (v) {
                            if (v == _this_id){
                                delete stayorder_list[i]
                                delete express_type[i]
                            }
                        })
                    }
                })
            }
            $(this).parents('.table_ul_top').next().find('.checkbox').prop('checked', bool)
            if($('input[type=checkbox]:checked').length === 0){
                $('.btns').each(function(){
                    $(this).addClass('layui-btn-disabled')
                })
            }

        })

        //单选按钮点击事件
        $(document).off('click', '.checkbox').on('click', '.checkbox', function () {
            var bool = $(this)[0].checked
            if ($('#status').val() == 1){
                user_id = $(this).parents('.table_ul_bottom').prev().find('.user_checkbox').parent().next().html()
            } else {
                user_id = $(this).parent().next().html()
            }
			if (bool) {
                order_sn_list.push($(this).attr('id'));
                is_tuan_list.push($(this).attr('data-is_tuan'));
                //判断是否所有都选中，如果所有选中，则全选按钮选中，否则，取消全选按钮
                if (order_sn_list.length === $('.checkbox').length) {
                    $('.all_checkbox').prop('checked', true)
                }

                //待发货状态内部订单单选处理
                if (!stayorder_list.hasOwnProperty(user_id)){
                    stayorder_list[user_id] = [];
                    express_type[user_id] = [];
                }
                stayorder_list[user_id].push($(this).attr('id'));
                if ($(this).attr('data-express_type') == 1 || $(this).attr('data-express_type') == 2){
                    express_type[user_id].push(1);
                } else {
                    express_type[user_id].push(0);
                }
                if (stayorder_list[user_id].length == $(this).parents('.table_ul_bottom').find('.checkbox').length){
                    $(this).parents('.table_ul_bottom').prev().find('.user_checkbox').prop('checked', true)
                }
            } else {
                deleteSpecifiedElement(order_sn_list, $(this).attr('id'));
                deleteSpecifiedElement(is_tuan_list, $(this).attr('data-is_tuan'));
                $('.all_checkbox').prop('checked', false)

                //待发货状态内部订单单选处理
                var _this_id = $(this).attr('id')
                stayorder_list[user_id].forEach(function (e,index) {
                    if (e == _this_id){
                        stayorder_list[user_id].splice(index,1)
                        express_type[user_id].splice(index,1)
                    }
                })
                $(this).parents('.table_ul_bottom').prev().find('.user_checkbox').prop('checked', false)
            }
			if($('input[type=checkbox]:checked').length === 0){
				$('.btns').each(function(){
					$(this).addClass('layui-btn-disabled')
				})
			}
        })
        //点击图片打开预览
        $(document).off('click', '.imgClickEvent').on('click', '.imgClickEvent', function () {
            imgClickEvent(this)
        })
    });
    exports('print/list', {})
})

//获取订单div
function getOrderDiv(order) {
    var s_show = '';
    if (order.status === 0) {
        s_show = '';
    }
    if (order.status === '0') {
        s_show = '待付款';
    } else if (order.status === '1') {
        s_show = '待发货';
    } else if (order.status === '2') {
        s_show = '已取消';
    } else if (order.status === '3') {
        s_show = '已发货';
    } else if (order.status === '4') {
        s_show = '已退款';
    } else if (order.status === '5') {
        s_show = '退款中';
    } else if (order.status === '6') {
        s_show = '待评价';
    } else if (order.status === '7') {
        s_show = '已完成';
    } else if (order.status === '8') {
        s_show = '已删除';
    } else if (order.status === '9') {
        s_show = '一键退款';
    } else {
        s_show = '类型错误';
    }
    //判断是否有子订单并获取第一个子订单的标题和图片
    var property1_name = '';
    var images = '';
    var number = 0;
    var c_order_div = '';
    var express_number = '';
    if (order.order && order.order[0]) {
        express_number = order.order[0].express_number ? order.order[0].express_number : '';
        //循环子订单获取宝贝数量
        var c_order = order.order;
        var c_o_len = order.order.length;
        for (var i = 0; i < c_o_len; i++) {
            number += parseInt(c_order[i].number);
            c_order_div += '<div class="li_div clearfix">\n' +
                '               <img class="imgClickEvent" src="' + c_order[i].pic_url + '" alt="">\n' +
                '               <div class="li_div_right">\n' +
                '                   <h4>' + c_order[i].name + '</h4>\n' +
                '                   <p>' + c_order[i].property1_name + '&nbsp;' + c_order[i].property2_name + '</p>\n' +
                '                   <p><span>定价：' + c_order[i].price + '</span><span>实付：' + c_order[i].payment_money + '</span></p>\n' +
                '               </div>\n' +
                '           </div>';
            if (i !== 0) {
                images += '<br/>';
                property1_name += '<br/>';
            }
            images += '<img class="imgClickEvent" style="height: 20px;" src="' + c_order[i].pic_url + '" alt="">';
            property1_name += c_order[i].property1_name;
        }
    }
    //拼接需要复制的地址
    var address = order.address
    address =   address.substr(0,address.lastIndexOf('-'))
    address = address.split('-').join('');
    address = address == '' ? '无地址信息':address
    return '             <li>\n' +
        '                    <div class="table_ul_top clearfix">\n' +
        '                        <p><input id="' + order.order_sn + '" data-express_type="' + order.express_type + '" data-is_tuan="' + order.is_tuan + '" type="checkbox" class="checkbox"></p>\n' +
        '                        <p>' + order.user_id + '</p>\n' +
        '                        <p>' + order.name + '</p>\n' +
        '                        <p>' + order.phone + '</p>\n' +
        '                        <p>' + address + '</p>\n' +
        '                        <p>' + s_show + '</p>\n' +
        '                        <p><input class="number express_number" type="text" value="' + express_number + '"></p>\n' +
        '                        <p>' + images + '</p>\n' +
        '                        <p>' + property1_name + '</p>\n' +
        '                        <p>' + number + '</p>\n' +
        '                        <p>' + order.remark + '__' + order.admin_remark + '</p>\n' +
        '                        <p><span>展开</span></p>\n' +
        '                    </div>\n' +
        '                    <div class="table_ul_bottom">\n' +
        '                        <ul class="table_ul_bottom_ul">\n' +
        '                            <li>\n' +
        '                                <h3>昵称：</h3>\n' +
        '                                <h4>' + order.nickname + '</h4>\n' +
        '                                <button class="btn2">复制昵称</button>\n' +
        '                            <li>\n' +
        '                                <h3>收&nbsp件&nbsp人&nbsp：</h3>\n' +
        '                                <input type="text" value="' + order.name + '">\n' +
        '                                <h3>手机：</h3>\n' +
        '                                <input type="text" value="' + order.phone + '">\n' +
        '                                <h3>固话：</h3>\n' +
        '                                <input type="text" value="">\n' +
        '                                <h3>邮编：</h3>\n' +
        '                                <input type="text" value="' + order.postcode + '">\n' +
        '                            </li>\n' +
        '                            <li>\n' +
        '                                <h3>收货地址：</h3>\n' +
        '                                <input type="text" value="' + order.province + '">\n' +
        '                                <input class="ipt" type="text" value="' + order.city + '">\n' +
        '                                <input class="ipt" type="text" value="' + order.area + '">\n' +
        '                                <input class="ipt2" type="text" value="' + order.addr + '">\n' +
        '                                <button class="btn2">确定</button>\n' +
        '                                <input style="display: none;" value="' + address + '">\n' +
        '                                <button class="btn2 copy_address">复制地址</button>\n' +
        '                            </li>\n' +
        '                            <li>\n' +
        '                                <p>\n' +
        '                                    <img src="./print/images/4.png" alt="">\n' +
        '                                    <span>' + order.order_sn + '</span>\n' +
        '                                    <span>' + order.create_time + '</span>\n' +
        '                                    <span>共付' + order.total_price + '元（含运费' + order.express_price + '元）</span>\n' +
        '                                </p>\n' +
        '                            </li>\n' +
        '                        </ul>\n' +
        '                        <ul class="table_ul_bottom_ul_b">\n' +
        '                            <li>\n' +
        '                                <h3>留言：</h3>\n' +
        '                                <input type="text">\n' +
        '                            </li>\n' +
        '                            <li>\n' +
        '                                <h3>备注：</h3>\n' +
        '                                <input type="text" value="' + order.remark + '">\n' +
        '                            </li>\n' +
        '                            <li>\n' +
        '                                <h3>发票：</h3>\n' +
        '                                <input type="text">\n' +
        '                            </li>\n' +
        '                            <li>\n' +
        '                                <h3>宝贝：</h3>\n' + c_order_div +
        '                            </li>\n' +
        '                        </ul>\n' +
        '                    </div>\n' +
        '                </li>';
}


//待发货订单div
function getStayOrderDiv(order) {
    var s_show = '';
    if (order[0].status === 0) {
        s_show = '';
    }
    if (order[0].status === '0') {
        s_show = '待付款';
    } else if (order[0].status === '1') {
        s_show = '待发货';
    } else if (order[0].status === '2') {
        s_show = '已取消';
    } else if (order[0].status === '3') {
        s_show = '已发货';
    } else if (order[0].status === '4') {
        s_show = '已退款';
    } else if (order[0].status === '5') {
        s_show = '退款中';
    } else if (order[0].status === '6') {
        s_show = '待评价';
    } else if (order[0].status === '7') {
        s_show = '已完成';
    } else if (order[0].status === '8') {
        s_show = '已删除';
    } else if (order[0].status === '9') {
        s_show = '一键退款';
    } else {
        s_show = '类型错误';
    }

    //外层商品拼装
    var order_goods = [];
    var remark = '';
    var order_sn_info = '';
    var is_tuan_info = '';
    order.forEach(function (a) {
        remark += a.remark + '__' + a.admin_remark + '&nbsp;&nbsp;';
        order_sn_info += a.order_sn + ',';
        is_tuan_info += a.is_tuan + ',';
        a.order && a.order.forEach(function (e) {
            if (!order_goods.hasOwnProperty(e.goods_id)) {
                order_goods[e.goods_id] = [];
                order_goods[e.goods_id]['number'] = parseInt(e.number)
            } else {
                order_goods[e.goods_id]['number'] += parseInt(e.number)
            }
            order_goods[e.goods_id]['pic_url'] = e.pic_url
            order_goods[e.goods_id]['property1_name'] = e.property1_name
            order_goods[e.goods_id]['property2_name'] = e.property2_name
        })
    });

    //判断是否有子订单并获取第一个子订单的标题和图片
    var order_len = order.length;
    var property1_name = '';
    var images = '';
    var number = '';
    var express_number = '';

    //外层商品展示
    order_goods.forEach(function (e) {
        number += e.number + '<br/>';
        images += '<img class="imgClickEvent" style="height: 20px;" src="' + e.pic_url + '" alt=""><br/>';
        property1_name += e.property1_name + '&nbsp;' + e.property2_name + '<br/>';
    })

    //拼接需要复制的地址
    var address = order[0].address
    address =   address.substr(0,address.lastIndexOf('-'))
    address = address.split('-').join('');
    address = address == '' ? '无地址信息':address
    var res_list = '';

    res_list += '             <li>\n' +
        '                    <div class="table_ul_top clearfix">\n' +
        '                        <p><input type="checkbox" name="user_checkbox" class="user_checkbox"></p>\n' +
        '                        <p>' + order[0].user_id + '</p>\n' +
        '                        <p>' + order[0].name + '</p>\n' +
        '                        <p>' + order[0].phone + '</p>\n' +
        '                        <p>' + address + '</p>\n' +
        '                        <p>' + s_show + '</p>\n' +
        '                        <p><input class="number express_number" type="text" value="' + express_number + '"></p>\n' +
        '                        <p>' + images + '</p>\n' +
        '                        <p>' + property1_name + '</p>\n' +
        '                        <p>' + number + '</p>\n' +
        '                        <p>' + remark + '</p>\n' +
        '                        <p><span>展开</span></p>\n' +
        '                    </div>\n' +
        '                    <div class="table_ul_bottom">\n' +
        '                        <ul class="table_ul_bottom_ul">\n' +
        '                            <li>\n' +
        '                                <h3>昵称：</h3>\n' +
        '                                <h4>' + order[0].nickname + '</h4>\n' +
        '                                <button class="btn2">复制昵称</button>\n' +
        '                            <li>\n' +
        '                                <h3>收&nbsp件&nbsp人&nbsp：</h3>\n' +
        '                                <input type="text" value="' + order[0].name + '">\n' +
        '                                <h3>手机：</h3>\n' +
        '                                <input type="text" value="' + order[0].phone + '">\n' +
        '                                <h3>固话：</h3>\n' +
        '                                <input type="text" value="">\n' +
        '                                <h3>邮编：</h3>\n' +
        '                                <input type="text" value="' + order[0].postcode + '">\n' +
        '                            </li>\n' +
        '                            <li>\n' +
        '                                <h3>收货地址：</h3>\n' +
        '                                <input type="text" value="' + order[0].province + '">\n' +
        '                                <input class="ipt" type="text" value="' + order[0].city + '">\n' +
        '                                <input class="ipt" type="text" value="' + order[0].area + '">\n' +
        '                                <input class="ipt2" type="text" value="' + order[0].addr + '">\n' +
        '                                <button class="btn2">确定</button>\n' +
        '                                <input style="display: none;" value="' + address + '">\n' +
        '                                <button class="btn2 copy_address">复制地址</button>\n' +
        '                            </li>\n';

    for(var i = 0; i < order_len; i++){
        var goods_list = order[i].order;
        var goods_list_len = order[i].order.length;

        res_list += '                        </ul>\n' +
            '                        <ul class="table_ul_bottom_ul_b">\n' +
            '                            <li>\n' +
            '                                <p>\n' +
            '                                    <input id="' + order[i].order_sn + '" data-express_type="' + order[i].express_type + '" data-is_tuan="' + order[i].is_tuan + '" type="checkbox" class="checkbox">\n' +
            '                                    <img src="./print/images/4.png" alt="">\n' +
            '                                    <span>' + order[i].order_sn + '</span>\n' +
            '                                    <span>' + order[i].create_time + '</span>\n' +
            '                                    <span>共付' + order[i].total_price + '元（含运费' + order[i].express_price + '元）</span>\n' +
            '                                </p>\n' +
            '                            </li>\n' +
            '                            <li>\n' +
            '                                <h3>留言：</h3>\n' +
            '                                <input type="text">\n' +
            '                            </li>\n' +
            '                            <li>\n' +
            '                                <h3>备注：</h3>\n' +
            '                                <input type="text" value="' + order[i].remark + '">\n' +
            '                            </li>\n' +
            '                            <li>\n' +
            '                                <h3>发票：</h3>\n' +
            '                                <input type="text">\n' +
            '                            </li>\n' +
            '                            <li>\n' +
            '                                <h3>宝贝：</h3>\n';
        for(var j = 0; j < goods_list_len; j++){
            res_list += '<div class="li_div clearfix">\n' +
                '               <img class="imgClickEvent" src="' + goods_list[j].pic_url + '" alt="">\n' +
                '               <div class="li_div_right">\n' +
                '                   <h4>' + goods_list[j].name + '</h4>\n' +
                '                   <p>' + goods_list[j].property1_name + '&nbsp;' + goods_list[j].property2_name + '</p>\n' +
                '                   <p><span>定价：' + goods_list[j].price + '</span><span>实付：' + goods_list[j].payment_money + '</span></p>\n' +
                '               </div>\n' +
                '           </div>';
        }
        res_list += '                            </li>\n' +
            '                        </ul>\n';
    }
    res_list += '                    </div>\n' +
        '                </li>';


    return res_list;
}


