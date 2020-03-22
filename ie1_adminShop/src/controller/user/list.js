/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/9/19 10:00  一直在更新，时间随时修改
 * js model
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter','laydate','laypage'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var laydate = layui.laydate
		var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var openIndex;//定义弹出层，方便关闭
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var limit = 10;//列表中每页显示数量
        var limits = [10, 20, 30];//自定义列表每页显示数量
        var saa_key = sessionStorage.getItem('saa_key');
        var operationId;
        var ajaxType,tabPage = 1,pageLimit = 10,laypage = layui.laypage,page = ''
        form.render();
        //以下基本不动
        //加载列表
		var dataList = getList()
		function getList(){
			var res = getAjaxReturnKey({method:'merchantShopUsers',type:'get',params:'limit=' + pageLimit + '&page=' + tabPage})
			page = res.count
			return res
		}
		dataMosaic(dataList.data)
		//列表数据拼接
		function dataMosaic(data){
			var dataListStr = ''
			data && data.forEach(function(e,index){
				var text = ''
				e.status === '1' ? text = '拉黑' : text = '恢复'
				dataListStr += '<tr>' +
									'<td><input type="checkbox" lay-filter="this" name="this" lay-skin="primary"/></td>'+
									'<td><div>'+ e.id +'</div></td>'+
									'<td><div class="avatar"><img src="'+ e.avatar +'"/></div></td>'+
									'<td><div class="margin"><div class="nickName" title="'+ e.nickname +'">昵称：'+ e.nickname +'</div><div>团长：'+ (e.realname === null?"无团长":e.realname) +'</div></div></td>'+
									'<td><div>'+ e.sex +'</div></td>'+
									'<td><span class="layui-badge">会员</span></td>'+
									'<td><div class="recharge"><div>余额：' + e.recharge_balance + '</div><div>积分：' + e.score + '</div></div></td>'+
									'<td><div class="margin"><div>手机号：'+ (e.phone !== null?e.phone:"暂无信息	") +'</div><div>地区：'+ (e.pca === null?"暂无信息":e.pca) +'</div><div>地址：'+ (e.address === null?"暂无信息":e.address) +'</div></div></td>'+
									'<td><div class="margin"><div>总金额：'+ Math.floor(e.money*100)/100 +'</div><div>总订单：'+ e.pay_num +'</div><div>购物车：'+ e.cart_num +'</div></div></td>'+
									'<td><div>'+ e.create_time +'</div></td>'+
									'<td><div><button type="button" class="layui-btn layui-btn-danger black" data-name="'+ e.nickname +'" data-id="'+ e.id +'" data-status="'+ e.status +'">'+ text +'</button><button type="button" class="layui-btn layui-btn-normal order" data-id="'+ e.id +'">订单</button></div></td>'+
								'</tr>'
			})
			$('.tbodys').children().remove()
			$('.tbodys').append(dataListStr)
			form.render()
		}
		getPage();
		//重写分页
		function getPage() {
		    laypage.render({
		        elem: 'page' //注意，这里的 test1 是 ID，不用加 # 号
		        , count: page //数据总数，从服务端得到
		        , prev: '<'
		        , next: '>'
		        , limit: limit
		        , limits: [limit, limit * 2, limit * 3]
		        , layout: ['prev', 'page', 'next', 'refresh', 'skip', 'limit']
		        , jump: function (obj, first) {
					pageLimit = obj.limit
		            is_page = 1
		            tabPage = obj.curr
		            //首次不执行
		            if (!first) {
		                dataMosaic(getList().data)
		            }
		        }
		    });
		}
		//搜索
		$('.search').on('click',function(){
			var searchDataStr = '',saveData = $('form[name=search]').serializeArray()
			saveData && saveData.forEach(function(e){
				searchDataStr += '&' + e.name + '=' + e.value
			})
			var searchData = getAjaxReturnKey({method:'merchantShopUsers',type:'get',params:'limit=' + pageLimit + '&page=' + tabPage + searchDataStr})
			page = searchData.count
			dataMosaic(searchData.data)
			getPage()
		})
		
		//加载时间插件
		laydate.render({
			elem: '.times'
			,type: 'datetime'
			,range: true
		})
		
		// 订单点击事件
		$(document).off('click','.order').on('click','.order',function(){
			sessionStorage.setItem('orderId',$(this).data('id'))
			location.hash = '/order/list'
		})
		//拉黑事件
		$(document).off('click','.black').on('click','.black',function(){
			var _this = $(this),
				id = _this.data('id'),
				status = _this.data('status') === 1 ? status = 0 :status = 1,
				text = _this.html(),
				name = _this.data('name'),
				title = '确定要' + text + name + '用户？'
			layer.confirm(title, function (index) {
			    var res = getAjaxReturnKey({method:'merchantShopUsers/' + id,type:'put',data:{status:status}})
			    if(res.status === 200){
					_this.data('status',status)
					_this.data('status') === 1 ? _this.text('拉黑') : _this.text('恢复')
				}
				layer.close(index);
			})
		})
    })
    exports('user/list', {})
});
