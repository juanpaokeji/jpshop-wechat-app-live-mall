/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/6/9 10:10  一直在更新，时间随时修改
 * js model
 */

layui.define(function (exports) {
    layui.use(['jquery', 'form', 'admin', 'setter'], function () {
        var $ = layui.$;
        var form = layui.form;
        var admin = layui.admin;
        var setter = layui.setter;//配置
        var upload = layui.upload;//配置
        var baseUrl = setter.baseUrl;
        var sucMsg = setter.successMsg;//成功提示 数组
        var errorMsg = setter.errorMsg;//错误提示
        var timeOutCode = setter.timeOutCode;//token错误代码
        var timeOutMsg = setter.timeOutMsg;//token错误提示
        var headers = {'Access-Token': layui.data(setter.tableName).access_token};
        var loading;//定义加载效果
        var loadType = 1;//layer.open 类型
        var loadShade = {shade: 0.3};//layer.open shade属性
        var successMsg;//成功提示，仅用于判断新增编辑
        var saa_key = sessionStorage.getItem('saa_key');
		var openModel = '',modelTemplateId = '';
        /*diy设置开始*/
        //页面不同属性
        var url = baseUrl + "/merchantGoods";//当前页面主要使用 url
        var key = '?key=' + saa_key;
		var modelList = [];
		var template_id = '1';
        /*diy设置结束*/
		var parameters = '';
		if(sessionStorage.getItem('msgTemplateId')){
			parameters = '/' + sessionStorage.getItem('msgTemplateId');
		}
		//页面初始第一个模板
		var resp = getAjaxReturnKey({method:'merchantSystemTemplateMessageOne' + parameters,type:'get'});
		if(resp.status == 200 && resp.data){
			assignment(resp.data)
		}
		
		// 选择模板
		$(".select-tem").on("click",function(){
			openModel = layer.open({
                type: 1,
				title:false,
				closeBtn :2,
                content: $(".tem-lists"),
                shade: 0.5,
                offset: '100px',
                area: ['970px', '600px'],
                cancel: function () {
                    $(".tem-lists").hide();
                }
            })
			var arr= {
				method:'merchantSystemTemplateMessage',
				type:'get'
			}
			var res = getAjaxReturnKey(arr);
			if(res.status == 200 && res.data){
				modelList = res.data;
				var modelString = '';
				res.data.forEach(function(e){
					var insideStr = '';
					e.keyword_list && e.keyword_list.forEach(function(d){
						insideStr += '<p class="tem-con-list-tem"><span>'+d.name+'</span> <span>例：'+d.example+'</span></p>';
					})
					modelString += '<li class="models list" data-id="'+e.id+'">'+
										'<div class="active-table-content-box">'+
											'<div class="active-table-push-con">'+
												'<div class="active-push-tem-title">'+
													'<span class="tem-logo">'+
														'<img src="'+e.head_img+'">'+
													'</span>'+
													'<span class="title-con">'+e.miniprogram_name+'</span>'+
													'<i class="layui-icon layui-icon-more tem-more"></i>'+
												'</div>'+
												'<div class="tem-con-list">'+
													'<p class="tem-con-list-title">'+e.name+'</p>'+
													insideStr +
												'</div>'+
												'<div class="tem-foot">'+
													'<p class="view-mini">进入小程序查看<i class="layui-icon layui-icon-right"></i></p>'+
													'<p>拒收通知<i class="layui-icon layui-icon-right"></i></p>'+
												'</div>'+
											'</div>'+
										'</div>'+
									'</li>';
				})
				$(".active-moudle-list .list").remove();
				$(".active-moudle-list").append(modelString);
				$(".active-moudle-list li:first-child").addClass('actives');
			}
		})
		
		//单击选中模板
		$(document).off('click','.list').on('click','.list',function(){
			$(".list").removeClass('actives');
			$(this).addClass('actives');
		})
		//双击选择模板并关闭弹出框
		$(document).off('dblclick','.list').on('dblclick','.list',function(){
			closeModel();
			loadModel($(this).data('id'));
		})
		
		//取消事件
		$(".cancels").click(function(){
			closeModel();
		})
		
		//确定事件
		$(".saved").click(function(){
			closeModel();
			loadModel($(".actives").data('id'));
		})
		
		//关闭模态框
		function closeModel(){
			layer.close(openModel);
			$(".tem-lists").hide();
		}
		
		//加载模板
		function loadModel(data){
			modelList && modelList.forEach(function(e){
				if (e.id == data) {
					assignment(e);
				}
			})
		}
		//为模板赋值
		function assignment(data){
			modelTemplateId = data.id;
			if(data.template_id){
				template_id = data.template_id;
				modelTemplateId = data.system_mini_template_id;
			}
			$(".form-list .box").remove();
			$(".tem-con-list .tem-con-list-tem").remove();
			$(".msgName").text(data.name);
			$(".push-tem-title .title-con").text(data.miniprogram_name);
			$(".push-tem-title .tem-logo img").attr('src',data.head_img);
			var presenceInput = '',noInput = '';
			if(data.template_params){
				data.keyword_list = data.template_params;
			}
			data.keyword_list && data.keyword_list.forEach(function(e,index){
				var value = '';
				if(data.template_params){
					value = e.example
				}
				presenceInput += '<div class="mode-box box">'+
									'<div class="mess-title" data-index="'+index+'">'+e.name+'</div>'+
									'<div class="title-input">'+
										'<input type="text" name="'+e.name+'" placeholder="请输入内容" class="input-inner linkage" value="'+value+'">'+
										// '<textarea rows="" cols="" name="'+e.name+'" placeholder="请输入内容" class="textarea-inner"></textarea>'+
									'</div>'+
								'</div>';
				noInput += '<p class="tem-con-list-tem" data-index="'+index+'">'+
								'<span id="'+index+'">'+e.name+'</span>'+
								'<span class="'+index+'">'+e.example+'</span>'+
							'</p>';
			})
			$(".after").after(presenceInput);
			$(".tem-con-list-title").after(noInput);
		}
		
		//模板预览中的change事件
		$(document).off('input','.linkage').on('input','.linkage',function(){
			if($(this).parent().prev().text() == $('#' + $(this).parent().prev().data('index')).text()){
				$('.'+$(this).parent().prev().data('index')).text($(this).val())
			}
		})
		
		//点击跳转页
		var openJumpPage = '';
		$(".jumpPage").click(function(){
			sessionStorage.setItem('choice_url_type', 'mini');
			$('#choicePageUrl').load('./src/views/choicePageUrl.html',function(){
				openJumpPage = layer.open({
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
			})
		})
		
		//点击保存
		$(".saveTemplate").click(function(){
			var arr = [],other = {};
			$($("#add_edit_form").serializeArray()).each(function(index,obj){
				var data = {name:obj.name,example:obj.value};
				arr.push(data)
			})
			$($("#leftForms").serializeArray()).each(function(index,obj){
				other[obj.name] = obj.value;
			})
			var dataSend = $.extend({},{template_id:template_id,id:modelTemplateId,template_params:arr,key:saa_key,page:[{link_name:$(".input-inner").val(),page_url:$(".page_url").val(),page_app_id:$(".page_app_id").val()}]},other);
			if($(this).data('type')){
				dataSend.type = $(this).data('type');
			}
			$.ajax({
				url:baseUrl + '/merchantSystemTemplateMessage',
				data:dataSend,
				async:false,
				type:'post',
				headers:{'Access-Token': layui.data(setter.tableName).access_token},
				success:function(res){
					if (res.status == 200) {
						sessionStorage.removeItem('msgTemplateId')
						layer.msg(sucMsg.post, {
							icon: 1,
							time: 2000 //2秒关闭（如果不配置，默认是3秒）
						}, function () {
							window.history.go(-1);
						});
					}
				}
			})
		})
		
    })
    exports('voucher/miniTemplateAdd', {})
});
