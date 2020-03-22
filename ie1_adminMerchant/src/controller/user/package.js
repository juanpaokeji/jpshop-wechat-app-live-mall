/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2019/4/12
 * js 代理申请
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
        var baseUrl = setter.baseUrl;
        var openIndex;//定义弹出层，方便关闭
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义
		var arr = [{name:'短信条数',type:1,data:[],remaining:''},{name:'订单次数',type:2,data:[],remaining:''}];//如果增加新的类型需要更改此变量，增加一个对象 ,{name:'组合套餐',type:5,data:[],remaining:''}
		var res = getAjaxReturn({method:'MerchantCombo',type:'get'});
		if(res.status == 200){
			sessionStorage.setItem('merchant_id',res.merchant_id)
			arr.forEach(function(e){
				res.data.forEach(function(a){
					if(e.type == a.type){
						if(e.type == 1){//如需要其他类型的数据在此添加判断
							e.remaining = res.sms_count;
						}else if(e.type == 2){
							e.remaining = res.order_count;
						}
						a.number = a.sms_number;
						e.data.push(a)
					}
				})
			})
		}
		var packageString = '';
		arr.forEach(function(e){
			var packageListOneString = '';
			e.data && e.data.forEach(function(a){
				packageListOneString += '<li class="package-list-one" data-id="'+a.id+'">'+
											'<img class="list-one-img" src="'+a.pic_url+'"/>'+
										'</li>';
			})
			packageString +='<li class="package-type">'+
								'<div class="type-title">'+e.name+'<span class="span-color"  data-types="'+e.type+'">（剩余数量：'+e.remaining+'条）</span></div>'+
								'<ul class="package-lists">'+
								packageListOneString +	
								'</ul>'+
							'</li>';
		})
		$(".package-data").append(packageString);
		$(".span-color").each(function(e){
			if($(this).data('types') == 5){
				$(this).hide()
			}
		})
		
		//点击购买
		$(document).off('click','.package-list-one').on('click','.package-list-one',function(){
			sessionStorage.setItem('payId',$(this).data('id'))
			location.hash = '/app/add/packagePay';
		})
		
		//返回按钮
		$('.go-back').on('click',function(){
			history.go(-1)
		})
    });
    exports('user/package', {})
});
