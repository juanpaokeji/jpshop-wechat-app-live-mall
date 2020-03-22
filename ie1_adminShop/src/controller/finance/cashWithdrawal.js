/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 创建于 2019/5/8
 * js 佣金提现申请
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'laydate','laypage'], function () {
        var $ = layui.$;
        var setter = layui.setter;
        var table = layui.table;
        var form = layui.form;
        var layDate = layui.laydate;
		var laypage = layui.laypage; //分页
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
		var tabPage = '';
		var pageLimit = 10;
		var limit = 10;//列表中每页显示数量
		var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');
        var arr = {};
        var export_data = [];//最终需要导出的 id 集合
        form.render();
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = 'merchantShopBalance';//新ajax需要的参数 method
        var cols = [//加载的表格
            {type: 'checkbox'},
            {field: 'balance_sn', title: '提现单号', width: '12%'},
            {field: 'avatar', title: '会员信息', templet: '#imgTpl', width: '12%'},
            {field: 'phone', title: '手机号码', width: '8%'},
            {field: 'send_type', title: '提现方式', templet: '#send_typeTpl', width: '6%'},
            {
                field: 'money', title: '提现金额', templet: function (d) {
                    return parseFloat(d.money)
                }, width: '6%'
            },
            {
                field: 'fee', title: '手续费', templet: function (d) {
                    return parseFloat(d.fee)
                }, width: '6%'
            },
            {
                field: 'remain_money', title: '打款金额', templet: function (d) {
                    return parseFloat(d.remain_money)
                }, width: '6%'
            },
            {field: 'format_create_time', title: '申请时间', width: '12%'},
            {field: 'realname', title: '收款人姓名', width: '7%'},
            {field: 'pay_number', title: '账号'},
            {field: 'operations', title: '操作', toolbar: '#operations'}
        ];

        //选择日期
        layDate.render({
            elem: '#datetime',
            type: 'date',
            range: true
        });
        /*diy设置结束*/

        //以下基本不动
        //默认加载列表
        arr = {
            name: 'render',//可操作的 render 对象名称
            elem: '#pageTable',//需要加载的 table 表格对应的 id
            method: ajax_method + '?type=2&key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols],//加载的表格字段
        };
        var render = getTableRender(arr),headModels = '';//变量名对应 arr 中的 name
		
		//选择团长
		var dataList ='';
		function getListHead() {
			var res = getAjaxReturnKey({method: 'merchantTuanUser',type: 'get',params: 'type=1'});
			return res;
		}
		
		//拼接
		function dataMosaic(data){
			var headString = '';
			data && data.forEach(function(e,index){
				var colorString = '';
				if(!index % 2 == 0){
					colorString = '<li class="tbodyCol oddNumber">';
				}else{
					colorString = '<li class="tbodyCol others">';
				}
				headString = colorString +
								'<div>'+e.id+'</div>'+
								'<div>'+e.realname+'</div>'+
								'<div class="shorten" title="'+e.addr+'">'+e.addr+'</div>'+
								'<div class="operated"><a data-name="'+e.realname+'">选择</a></div>'+
							'</li>';
			})
			$(".tbodyCol").remove();
			$(".tableHead").after(headString);
		}
		
		//点击选择团长按钮
		form.on('submit(choice_groups)', function () {
			dataList = getListHead();
			dataMosaic(dataList.data);
			getPage();
			headModels = layer.open({
			    type: 1,
				title:'选择团长',
			    content: $(".selectHeads"),
			    shade: 0.5,
			    offset: '100px',
			    area: ['800px', 'auto'],
			    cancel: function () {
			        $(".selectHeads").hide();
			    }
			})
		});
		
		//弹出层查询
		$(".searchHead").click(function(){
			arr = {
			    method: 'merchantTuanUser?key='+ saa_key + '&limit=' + pageLimit + '&page=' + tabPage,
			    type: 'get',
			    data: {searchName: $(".head-input").val()},
			};
			var searchData = getAjaxReturnKey(arr);
			dataMosaic(searchData.data);
			getPage();
		})
		
		//弹出层分页
		function getPage() {
		    laypage.render({
		        elem: 'page' //注意，这里的 test1 是 ID，不用加 # 号
		        , count: dataList.count //数据总数，从服务端得到
		        , prev: '<'
		        , next: '>'
		        , limit: limit
		        , limits: [limit, limit * 2, limit * 3]
		        , layout: ['prev', 'page', 'next', 'refresh', 'skip', 'limit']
		        , jump: function (obj, first) {
		        	//obj包含了当前分页的所有参数，比如： 
		        	// console.log(obj.curr); 
		        	//得到当前页，以便向服务端请求对应页的数据。 
		        	// console.log(obj.limit); 
		        	//得到每页显示的条数
		            pageLimit = obj.limit;
		            is_page = 1;
		            tabPage = obj.curr;
		            //首次不执行
		            if (!first) {
		                dataMosaic(getListHead().data);
		            }
		        }
		    });
		}
		
		
		//选择按钮
		$(document).off('click','.operated a').on('click','.operated a',function(){
			$("input[name=searchName]").val($(this).data('name'))
			layer.close(headModels);
			$(".selectHeads").hide();
			$(".head-input").val('');
		})
		
        //选择团长
        form.on('submit(choice_group)', function (data) {//查询
            arr = {
                method: 'merchantTuanUser',
                type: 'get',
                params: 'type=1'
            }
            var res = getAjaxReturnKey(arr);
            if (res && res.data) {

            }
        });

        //清除日期范围
        form.on('submit(clear_date)', function () {//查询
            $('input[name=datetime]').val('');
        });

        //清除团长姓名
        form.on('submit(clear_group)', function () {//查询
            $('input[name=searchName]').val('');
        });

        //搜索
        form.on('submit(find)', function (data) {//查询
            console.log(data);
            render.reload({
                where: data.field,
                page: {curr: 1}
            });
        });

        //复选框选中事件
        table.on('checkbox(pageTable)', function (obj) {
            if (!result) {
                layer.msg('数据未载入完成，请稍后再试', {icon: 1, time: 2000});
                return;
            }
            if (obj.type === 'all') {
                if (obj.checked) {
                    //全选选中
                    for (var i = 0; i < result.data.length; i++) {
                        export_data.push(result.data[i].id);
                    }
                } else {
                    //取消全选选中
                    export_data = [];
                }
            } else if (obj.type === 'one') {
                if (obj.checked) {
                    //单条选中
                    export_data.push(obj.data.id);
                } else {
                    //取消单条选中，两种写法，1、先去重，再循环查找删除，删除后break，2、直接循环删除，不使用break 这里使用第二种
                    deleteSpecifiedElement(export_data, obj.data.id);
                }
            }
        });

        //导出
        form.on('submit(export)', function () {//查询
            if (export_data.length <= 0) {
                layer.msg('请选择导出数据', {icon: 1, time: 2000});
                return;
            }
            var data = [];
            for (var i = 0; i < export_data.length; i++) {
                for (var j = 0; j < result.data.length; j++) {
                    if (result.data[j].id === export_data[i]) {
                        data.push(result.data[j]);
                        break;
                    }
                }
            }
            toLargerCSV(data);
        });

        function toLargerCSV(data) {
            //CSV格式可以自己设定，适用MySQL导入或者excel打开。
            //由于Excel单元格对于数字只支持15位，且首位为0会舍弃 建议用 =“数值”
            var str = '行号,提现单号,会员信息,手机号码,提现方式,提现金额,手续费,打款金额,申请时间,收款人姓名,账号\n';
            for (var i = 0; i < data.length; i++) {
                //类型 0=余额 1=微信 2=支付宝 3=银行卡
                var send_type = '类型错误';
                if (data[i].send_type === '0') {
                    send_type = '余额';
                } else if (data[i].send_type === '1') {
                    send_type = '微信';
                } else if (data[i].send_type === '2') {
                    send_type = '支付宝';
                } else if (data[i].send_type === '3') {
                    send_type = '银行卡';
                } else {
                    send_type = '类型错误';
                }
                var data_arr = [
                    data[i].balance_sn + '\t',
                    data[i].nickname,
                    data[i].phone,
                    send_type,
                    parseFloat(data[i].money),
                    parseFloat(data[i].fee),
                    parseFloat(data[i].remain_money),
                    data[i].format_create_time,
                    data[i].realname,
                    data[i].pay_number,
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

        //表格操作点击事件
        table.on('tool(pageTable)', function (obj) {
            var data = obj.data;
            var layEvent = obj.event;
            if (layEvent === 'pay') {
                var that = this;
                var audit_method = 'merchantShopBalance';
                //打开新窗口，显示通过和不通过按钮
                layer.confirm('该提现申请是否通过审核？', {
                    btn: ['通过', '不通过'] //可以无限个按钮
                    , btnAlign: 'c'
                    , btn1: function (index) {
                        //按钮 通过 的回调
                        layer.close(index);
                        arr = {
                            method: audit_method + '/' + data.id,
                            type: 'put',
                            data: {status: 1}
                        };
                        var res = getAjaxReturnKey(arr);
                        if (!res) {
                            return false;
                        }
                        layer.msg(res.message, {icon: 1, time: 2000});
                        $(that).addClass('layui-btn-normal').html('已通过');
                    }
                    , btn2: function () {
                        //按钮 不通过 的回调 需要修改审核状态
                        arr = {
                            method: audit_method + '/' + data.id,
                            type: 'put',
                            data: {status: 2}
                        };
                        var res = getAjaxReturnKey(arr);
                        if (!res) {
                            return false;
                        }
                        layer.msg(res.message, {icon: 1, time: 2000});
                        $(that).addClass('layui-btn-danger').html('已拒绝');
                    }
                });
            } else {
                layer.msg(setter.errorMsg, {icon: 1, time: 2000});
            }
        });

    });
    exports('finance/cashWithdrawal', {})
});
