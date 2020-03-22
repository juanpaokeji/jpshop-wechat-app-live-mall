/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 创建于 2019/5/6
 * js 佣金流水
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
            {field: 'avatar', title: '头像', templet: '#imgTpl'},
            {field: 'merchant_id', title: 'ID'},
            {field: 'realname', title: '佣金获得者/姓名'},
            {field: 'type', title: '佣金类型', templet: '#typeTpl'},
            {
                field: 'remain_money', title: '所得佣金', templet: function (d) {
                    return parseFloat(d.remain_money)
                }
            },
            {field: 'format_create_time', title: '生成时间'},
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'confirm_time', title: '结算时间'},
            {field: 'order_status', title: '当前订单状态', templet: '#order_statusTpl'},
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
            method: ajax_method + '?type=1&key=' + saa_key,//请求的 api 接口方法和可能携带的参数 key
            cols: [cols],//加载的表格字段
        };
        var render = getTableRender(arr),headModel = '';//变量名对应 arr 中的 name
        
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
        form.on('submit(choice_group)', function () {
			dataList = getListHead();
			dataMosaic(dataList.data);
			getPage();
			headModel = layer.open({
			    type: 1,
				title:'选择团长',
			    content: $(".selectHead"),
			    shade: 0.5,
			    offset: '100px',
			    area: ['800px', 'auto'],
			    cancel: function () {
			        $(".selectHead").hide();
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
			layer.close(headModel);
			$(".selectHead").hide();
			$(".head-input").val('');
		})

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
            if ($('#balance_status').val() === 0) {
                data.field.balance_status = 0;
            }
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

            var str = '行号,ID,佣金获得者/姓名,佣金类型,所得佣金,生成时间,状态,结算时间,当前订单状态\n';

            for (var i = 0; i < data.length; i++) {
                var type = '类型错误';
                var status = '类型错误';
                var order_status = '类型错误';
                if (data[i].type === '0') {
                    type = '默认';
                } else if (data[i].type === '1') {
                    type = '团长佣金';
                } else if (data[i].type === '2') {
                    type = '推荐团长佣金';
                } else if (data[i].type === '3') {
                    type = '自提点佣金';
                } else if (data[i].type === '4') {
                    type = '推荐佣金';
                } else {
                    type = '类型错误';
                }
                if (data[i].status === '0') {
                    status = '结算中';
                } else if (data[i].status === '1') {
                    status = '已结算';
                } else if (data[i].status === '2') {
                    status = '已拒绝';
                } else {
                    status = '类型错误';
                }
                if (data[i].order_status === '0') {
                    order_status = '未设置';
                } else if (data[i].order_status === '1') {
                    order_status = '未设置';
                } else if (data[i].order_status === '2') {
                    order_status = '未设置';
                } else if (data[i].order_status === '3') {
                    order_status = '未设置';
                } else if (data[i].order_status === '4') {
                    order_status = '未设置';
                } else {
                    order_status = '类型错误';
                }
                var data_arr = [
                    data[i].merchant_id,
                    data[i].realname,
                    type,
                    parseFloat(data[i].remain_money),
                    data[i].format_create_time,
                    status,
                    data[i].confirm_time,
                    order_status,
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

    });
    exports('finance/record', {})
});
