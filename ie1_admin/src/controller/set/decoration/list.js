/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 创建于 2019/4/20
 * js 店铺装修模板列表
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
    layui.use(['jquery', 'setter', 'admin', 'table', 'form', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.$;
        var form = layui.form;
        var setter = layui.setter;//配置
		var baseUrl = setter.baseUrl;
        var layDate = layui.laydate;
        var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
		var ajax_method = '/decoration';//请求接口
		var loading;//定义加载效果
		var loadType = 1;//layer.open 类型
		var loadShade = {shade: 0.3};//layer.open shade属性
        /*diy设置开始*/
        form.render();

        //页面不同属性
        /*diy设置结束*/
		sendAjax();
		//发送请求
		function sendAjax(data){
			$.ajax({
				url:baseUrl + ajax_method,
				type:'get',
				async:false,
				data:data?data:{},
				headers:{'Access-Token': layui.data(setter.tableName).access_token},
				beforeSend: function () {
				    loading = layer.load(loadType, loadShade);//显示加载图标
				},
				success:function(res){
					var liString = '';
					res.data && res.data.forEach(function(e){
						liString += '<li class="templates">'+
										'<p class="title">'+e.name+'</p>'+
										'<div class="img-border" style="background-image:url('+e.pic_url+');">'+
											// '<img src="'+e.pic_url+'" />'+
										'</div>'+
										'<div class="enable-text"></div>'+
										'<a class="mask">'+
											'<div class="btnGroup">'+
												'<button class="layui-btn layui-btn-normal edit decoration a" data-id="'+e.id+'">编辑模板</button>'+
												'<button class="layui-btn layui-btn-danger del" data-id="'+e.id+'">删除模板</button>'+
											'</div>'+
										'</a>'+
									'</li>';
					})
					layer.close(loading);//关闭加载图标
					$(".templates").remove();
					$(".layui-row").prepend(liString);
				},
				error:function(res){
					console.log(res)
				}
			})
		}
		
		//查询
		$(".search-templates").click(function(){
			sendAjax({searchName:$(".layui-input-inline input").val()});
		})
		
		
		//点击编辑按钮和新增店铺模板
        $(document).off('click', '.decoration').on('click', '.decoration', function () {
			sessionStorage.removeItem('decoration_id')
			if($(this).data("id")){
				sessionStorage.setItem('decoration_id', $(this).data("id"));//将当前点击的模板 id 存入缓存方便编辑页面获取
			}
            location.hash = '/set/decoration/add';
        })
		
		//删除按钮
		$(document).off('click','.del').on('click','.del',function(){
			var id = $(this).data("id")
			layer.confirm('确定要删除这个店铺模板么?', function (index) {
			    layer.close(index);
			    var arr = {
			    	method:'decoration' +'/'+ id,
			    	type:'delete'
			    }
			    if (getAjaxReturn(arr)) {
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
    });
    exports('set/decoration/list', {})
});
