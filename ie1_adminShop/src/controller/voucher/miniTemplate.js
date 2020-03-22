/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/3/2
 * 团购
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'element'], function () {
        var element = layui.element;
        var $ = layui.$;
        var form = layui.form;
		var setter = layui.setter;//配置
		var baseUrl = setter.baseUrl;
		var sucMsg = setter.successMsg;//成功提示 数组
        var arr, res;//全局ajax请求参数
		var saa_key = sessionStorage.getItem('saa_key');
        form.render();

		//进入营销菜单必须执行方法，获取该应用的自定义版权状态，如果为1则显示自定义版权，为0则需要隐藏
		//之前写在layout里，太消耗性能，所以写在营销菜单下的所有页面里
		arr = {
			method: 'merchantCopyright',
			type: 'get'
		};
		res = getAjaxReturnKey(arr);
		if (res && res.data && res.data.copyright && res.data.copyright === '1') {
			if ($('.copyright_li').length <= 0) {
				$('.voucher_ul').append('<li class="copyright_li"><a lay-href="voucher/copyright">自定义版权</a></li>');
			}
		} else {
			$('.copyright_li').remove();
		}

        element.on('tab(tab)', function (e) {
            var index = e.index;
            if (index === 0) {
                getMiniSynchronization();
            } else if (index === 1) {
                loadMiniPush();
            }
        });
		
		function getAjax(d){
			 var data = '';
			$.ajax({
				url:baseUrl +'/merchantSystemTemplate',
				type:'get',
				data:d,
				async:false,
				headers:{'Access-Token': layui.data(layui.setter.tableName).access_token},
				success:function(res){
					if(res.status == 200 && res.data){
						data = res.data;
					}
				}
			});
			return data;
		}
		
		//同步模板
        //获取当前同步模板
		getMiniSynchronization();
        function getMiniSynchronization() {
            var data = getAjax({purpose:'order',key:saa_key});
            $('#miniSynchronization').empty();
            for (var i = 0; i < data.length; i++) {
                $('#miniSynchronization').append('<li><span class="temp_name">模板名称：' + data[i].name + '</span>&nbsp;&nbsp;&nbsp;<span>模板ID：' + data[i].template_id + '</span></li>');
            }
        }

		form.on('submit(sub)', function () {
			arr = {
				method: 'merchantSystemTemplate',
				type: 'post'
			};
			res = getAjaxReturnKey(arr);
			if (!res) {
				return false;
			}
			element.tabChange('tab', '0');
		})
		
		//群消息推送
		$(document).off('click','.createPush').on('click','.createPush',function(){
			sessionStorage.removeItem('msgTemplateId');
			location.hash = '/voucher/miniTemplateAdd';
		});
		
        //加载小程序推送信息页面，并获取配置
        function loadMiniPush() {
            var data = getAjax({purpose:'message',key:saa_key});
			if(data){
				var msgTemplateString = '';
				data.forEach(function(e){
					var insideStr = '',send = '';
					e.template_params && e.template_params.forEach(function(d){
						insideStr += '<p class="tem-con-list-tem"><span>'+d.name+'</span> <span>'+d.example+'</span></p>';
					})
					if(e.status == 2){
						send = '<div class="layui-col-md4 layui-hide">'+
									'<button class="layui-btn layui-btn-normal send" data-id="'+e.id+'">发送</button>'+
								'</div>';
					}else{
						send = '<div class="layui-col-md4">'+
									'<button class="layui-btn layui-btn-normal send" data-id="'+e.id+'">发送</button>'+
								'</div>';
					}
					msgTemplateString += '<li class="list-push '+e.id+'">'+
											'<div class="table-content-box">'+
												'<div class="table-push-con">'+
													'<div class="puth-tem-title">'+
														'<span class="tem-logo">'+
															'<img src="'+e.head_img+'">'+
														'</span>'+
														'<span class="title-cons">'+e.miniprogram_name+'</span>'+
														'<i class="layui-icon layui-icon-more tem-more"></i>'+
													'</div>'+
													'<div class="tem-con-list">'+
														'<p class="tem-con-list-title">'+e.name+'</p>'+
														insideStr +
														'</p>'+
													'</div>'+
													'<div class="tem-foot">'+
														'<p class="view-mini">进入小程序查看<i class="layui-icon layui-icon-right"></i></p>'+
														'<p>拒收通知<i class="layui-icon layui-icon-right"></i></p>'+
													'</div>'+
												'</div>'+
											'</div>'+
											'<div class="push-btn-list">'+
												'<div class="layui-col-md12">'+
													send+
													'<div class="layui-col-md4">'+
														'<button class="layui-btn layui-btn-warm editTemplate" data-id="'+e.id+'">编辑</button>'+
													'</div>'+
													'<div class="layui-col-md4">'+
														'<button class="layui-btn layui-btn-danger delTemplate" data-id="'+e.id+'">删除</button>'+
													'</div>'+
												'</div>'+
											'</div>'+
										'</li>';
				})
				$(".list-table-ul .list-push").remove();
				$(".list-table-ul").append(msgTemplateString);
			}
        }
		
		//发送消息
		$(document).off('.click','.send').on('click','.send',function(){
			var _this = $(this);
			var id = _this.data('id');
			layer.confirm('是否发送消息?', function (index) {
			    layer.close(index);
				$.ajax({
					url:baseUrl + '/merchantSystemTemplateMessageSend',
					type:'post',
					data:{key:saa_key,id:id},
					async:false,
					headers:{'Access-Token': layui.data(layui.setter.tableName).access_token},
					success:function(res){
						if(res.status == 200){
							_this.parent().addClass('layui-hide');
							layer.msg('发送成功!');
						}
					}
				})
			})
		});
		
		//编辑
		$(document).off('clikc','.editTemplate').on('click','.editTemplate',function(){
			sessionStorage.setItem('msgTemplateId',$(this).data('id'));
			location.hash = '/voucher/miniTemplateAdd';
		});
		
		//删除
		$(document).off('clikc','.delTemplate').on('click','.delTemplate',function(){
			var id = $(this).data('id');
			layer.confirm('确定删除这个模板么?', function (index) {
			    layer.close(index);
				res = getAjaxReturnKey({method:'merchantSystemTemplate/' + id,type:'delete'});
				if(res.status == 200){
					$('.' + id).remove();
					layer.msg(sucMsg.delete);
				}
			})
		});
		
        //获取小程序推送信息
        function getMiniPush() {
            arr = {
                method: 'merchantSystemTemplate',
                type: 'get'
            };
            var res = getAjaxReturnKey(arr);
            if (!res || !res.data) {
                return false;
            }
        }

    });
    exports('voucher/miniTemplate', {})
});
