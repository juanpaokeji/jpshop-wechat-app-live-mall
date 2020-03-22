/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 创建于 2019/4/20
 * js 店铺装修
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
    layui.use(['jquery', 'setter', 'admin', 'form', 'element'], function () {
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
		var baseUrl = setter.baseUrl;
        var element = layui.element;
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var saa_key = sessionStorage.getItem('saa_key');
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
		var loading;//定义加载效果
		var loadType = 1;//layer.open 类型
		var loadShade = {shade: 0.3};//layer.open shade属性
        /*diy设置开始*/

        //页面不同属性
        var ajax_method = '/shopDecoration';//新ajax需要的参数 method
        //监听tab切换
        element.on('tab(tab)', function (e) {
            var index = e.index;
            if (index === 0) {
                //我的模板
            } else if (index === 1) {
                //系统模板库
				systemTemplate();
            }
        });
		
		//系统模板库请求
		function systemTemplate(data){
			$.ajax({
				url:baseUrl + '/systemDecoration',
				type:'get',
				async:false,
				data:data?data:{key:saa_key},
				headers:{'Access-Token': layui.data(setter.tableName).access_token},
				beforeSend: function () {
				    loading = layer.load(loadType, loadShade);//显示加载图标
				},
				success:function(res){
					var liString = '';
					res.data && res.data.forEach(function(e){
						liString += '<li class="templates sys">'+
									'<p class="title">'+e.name+'</p>'+
										'<div class="img-border" style="background-image:url('+e.pic_url+');">' +
											// '<img src="'+e.pic_url+'" />'+
										'</div>'+
										'<div class="enable-text">选用</div>' +
										'<a class="mask">'+
											'<div class="btnGroup">'+
												'<p>'+e.name+'</p>'+
												'<button class="layui-btn layui-btn-normal selects a" data-id="'+e.id+'">选用模板</button>'+
											'</div>'+
										'</a>'+
									'</li>';
					})
					layer.close(loading);//关闭加载图标
					$(".sys").remove();
					$(".sysTemplates").prepend(liString);
				},
				error:function(res){
					console.log(res)
				},
			})
		}
		
		//查询  系统模板库
		$(".sysSearch").click(function(){
			systemTemplate({searchName:$(".sysName").val(),key:saa_key});
		})
		
		
		//选用模板到我的模板库
		$(document).off('click','.selects').on('click','.selects',function(){
			var id = $(this).data("id");
			layer.confirm('确定要选用这个模板么?', function (index) {
			    layer.close(index);
				$.ajax({
					url:baseUrl + '/systemDecoration',
					type:'post',
					async:false,
					data:{key:saa_key,template_id:id},
					headers:{'Access-Token': layui.data(setter.tableName).access_token},
					beforeSend: function () {
					    loading = layer.load(loadType, loadShade);//显示加载图标
					},
					success:function(res){
						if(res.status == 200){
							layer.msg('已选用', {
								icon: 1,
								time: 2000 //2秒关闭（如果不配置，默认是3秒）
							}, function () {
								window.history.go(0);
							});
						}
					},
					error:function(res){
						console.log(res)
					},
				})
			})
		})
		
		sendAjax();
		//发送请求
		function sendAjax(data){
			$.ajax({
				url:baseUrl + ajax_method,
				type:'get',
				async:false,
				data:data?data:{key:saa_key},
				headers:{'Access-Token': layui.data(setter.tableName).access_token},
				beforeSend: function () {
				    loading = layer.load(loadType, loadShade);//显示加载图标
				},
				success:function(res){
					var liString = '';
					res.data && res.data.forEach(function(e){
						var border = '',text = '',isEnable = '',del = '';
						if(e.is_enable == 1){
							border = '<div class="img-border able-border" style="background-image:url('+e.pic_url+');">';
							text = '<div class="enable-text" style="visibility:visible;">已启用</div>';
							isEnable = '<button class="layui-btn layui-btn-warm enable Active a" data-id="'+e.id+'">启用模板</button>';
							del = '<button class="layui-btn layui-btn-danger del Active" data-id="'+e.id+'">删除模板</button>';
						}else{
							border = '<div class="img-border" style="background-image:url('+e.pic_url+');">';
							text = '<div class="enable-text">已启用</div>';
							isEnable = '<button class="layui-btn layui-btn-warm enable" data-id="'+e.id+'">启用模板</button>';
							del = '<button class="layui-btn layui-btn-danger del" data-id="'+e.id+'">删除模板</button>';
						}
						liString += '<li class="templates my">'+
										'<p class="title">'+e.name+'</p>'+
										border +
											//'<img src="'+e.pic_url+'" />'+
										'</div>'+
										text +
										'<a class="mask">'+
											'<div class="btnGroup">'+
												isEnable +
												'<button class="layui-btn layui-btn-normal edit decoration " data-id="'+e.id+'">编辑模板</button>'+
												del +
											'</div>'+
										'</a>'+
									'</li>';
					})
					layer.close(loading);//关闭加载图标
					$(".my").remove();
					$(".myTempltes").prepend(liString);
				},
				error:function(res){
					console.log(res)
				},
			})
		}
		
		//查询  我的模板库
		$(".mySearch").click(function(){
			sendAjax({searchName:$(".myName").val(),key:saa_key});
		})
		
		//点击编辑按钮
		$(document).off('click','.edit').on('click','.edit',function(){
			sessionStorage.setItem('myTemplateId', $(this).data("id"));
			location.hash = '/decorationEdit';
		})
		
		//删除
		$(document).off('click','.del').on('click','.del',function(){
			var _this = $(this)
			layer.confirm('确定要删除这个模板么?', function (index) {
			    layer.close(index);
			    var arr = {
			    	method:'shopDecoration/' + _this.data("id"),
			    	type:'delete'
			    }
			    if (getAjaxReturnKey(arr)) {
					sendAjax();
			        layer.msg(sucMsg.delete);
			    }
			})
		})
		
		//展示图片上的按钮
		$(document).off("mouseover",".mask").on("mouseover",".mask",function(){
			$(this).children().css('display','block')
		})
		$(document).off("mouseout",".mask").on("mouseout",".mask",function(){
			$(this).children().css('display','none')
		})
		//点击启用按钮，隐藏删除按钮，并显示取消使用按钮
		$(document).off('click','.enable').on('click','.enable',function(){
			//发送请求切换是否启用
			var arr = {
				method:'shopDecorationIsEnable/' + $(this).data("id"),
				type:'put'
			}
			var res = getAjaxReturnKey(arr);
			if (res.status == 200) {
				$(".enable").removeClass('Active').next().next().removeClass('Active');
				$(this).addClass('Active').next().next().addClass('Active');
				$(".img-border").removeClass('able-border');
				$(".enable-text").css('visibility','hidden');
				$(this).parent().parent().prev().css('visibility','visible').prev().addClass('able-border');
			}
		})
    });
    exports('decoration', {})
});
