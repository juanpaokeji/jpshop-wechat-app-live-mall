/**
 * Created by 卷泡
 * author: wangjian
 * Created DateTime: 2019/6/11
 * js 添加模板
 */

layui.define(function (exports) {
    layui.use(['jquery', 'setter', 'admin', 'form'], function () {
        var $ = layui.$,
			form = layui.form,
			setter = layui.setter, //配置
			sucMsg = setter.successMsg, //成功提示 数组
			baseUrl = setter.baseUrl, //访问地址
			open_index, //定义弹出层，方便关闭
			appInfo = [], //应用ID数组
			classList = [], //分类数组
			optionStr = '',
			classStr = '',
			flag = 0,
			parentSelectedClassList = [], // 右侧选中的父类集合，也是最终选中的数组，即需要保存的数据
			childSelectedClassList = [], // 右侧选中的子类集合
			selectList = [], //添加分类时选中的checkbox
			data = {}, //最终保存请求的数据对象
			parentarr = [],
			printTempId = sessionStorage.getItem('printTemp'),// 模板id
			dismantlingData = [],
		    tableTitle = []
		//初始化页面
		var print_status = sessionStorage.getItem('print_status') ? sessionStorage.getItem('print_status') : 0;
		if (print_status === '1') {
			sessionStorage.setItem('print_status', '0');
			location.reload();
		} else {
			sessionStorage.setItem('print_status', '1');
		}
		var res = getAjaxReturn({method:'adminPrintingpulldown',type:'get'})
		if(res.status === 200){
			appInfo = res.app_info
			classList = res.data
		}
		//遍历appInfo
		appInfo && appInfo.forEach(function(e){
			optionStr += '<option value="'+ e.id +'">'+ e.name +'</option>'
		})
		$('.displayOption').after(optionStr)
		//编辑时判断并请求赋值
		if(printTempId){
			var res = getAjaxReturn({method:'adminPrintingtemp/'+ printTempId,type:'get'})
			if(res.status === 200){
				$('input[name=name]').val(res.data.name)
				$('input[name=english_name]').val(res.data.english_name)
				$('.appId').val(res.data.appid)
				$('select[name=status]').val(res.data.status)
				$('input[name=width]').val(res.data.width)
				$('input[name=height]').val(res.data.height)
				$('.edit-wrap div:last-child').remove()
				$('.edit-wrap').append(res.data.info)
				parentSelectedClassList = JSON.parse(res.data.keywrod_info)
				parentarr = JSON.parse(res.data.keywords_ids)
				parentSelectedClassList && parentSelectedClassList.forEach(function(e){
					dismantlingData.push(e)
					e.child && e.child.forEach(function(a){
						dismantlingData.push(a)
						childSelectedClassList.push(a)
					})
					if (e.type == 1){
						e.child && e.child.forEach(function(a){
							tableTitle.push({text:a.name,english_name:a.english_name})
						})
					}
				})
				selectList = dismantlingData
				addRightData(parentarr)//遍历右侧的数据
			}
		}
		
		function addRightData(parentarr){
			var classSelectList = ''
			parentarr && parentarr.forEach(function(e){
				if(e.type === '1'){
					var childStr = ''
					e.child && e.child.forEach(function(a){
						childStr += '<li style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;" title="'+ a.name +'" class="checkbox-table name-table" data-name="name-table" data-id="'+ a.id +'" data-parent="'+ e.id +'" data-english="'+ a.english_name +'">'+ a.name +'</li>'
					})
					classSelectList += '<div class="group removes">'+
											'<div class="subTitle" data-id="'+ e.id +'" data-table="'+ e.type +'">'+ e.name +'</div>'+
											'<ul class="list">'+
												childStr +
											'</ul>'+
										'</div>'
				}else if(e.type === '0'){
					var childStr = ''
					e.child && e.child.forEach(function(a){
						childStr += '<li style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;" title="'+ a.name +'" class="checkbox '+ a.id +'" data-name="'+ a.id +'" data-id="'+ a.id +'" data-parent="'+ e.id +'" data-english="'+ a.english_name +'">'+ a.name +'</li>'
					})
					classSelectList += '<div class="group removes">'+
											'<div class="subTitle" data-id="'+ e.id +'" data-table="'+ e.type +'">'+ e.name +'</div>'+
											'<ul class="list">'+
												childStr +
											'</ul>'+
										'</div>'
				}else if(e.type === '2'){
					var childStr = ''
					e.child && e.child.forEach(function(a){
						childStr += '<li style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;" title="'+ a.name +'" class="checkbox add-img '+ a.id +'"  data-name="'+ a.id +'" data-id="'+ a.id +'" data-url="'+ a.pic_url +'" data-parent="'+ e.id +'" data-english="'+ a.english_name +'">'+ a.name +'</li>'
					})
					classSelectList += '<div class="group removes">'+
											'<div class="subTitle" data-id="'+ e.id +'" data-table="'+ e.type +'">'+ e.name +'</div>'+
											'<ul class="list">'+
												childStr +
											'</ul>'+
										'</div>'
				}
			})
			$('.panel .removes').slice(0).remove()
			$('.afterInsert').after(classSelectList)
			
			// 给之前已经勾选的checkbox加类'on'
			if(parentSelectedClassList.length !== 0){
				$('.removes .list li').each(function(){
					var _this = $(this)
					parentSelectedClassList.forEach(function(e){
						e.child.forEach(function(a){
							if(a.id == _this.data('id') && a.name == _this.text()){
								_this.addClass('on')
							}
						})
					})
				})
			}
		}
		
		//添加分类
		$('.addClass').on('click',function(){
			open_index = layer.open({
			    type: 1,
			    title: '添加分类',
			    content: $('#forms'),
			    shade: 0,
			    offset: '100px',
			    area: ['400px', '500px'],
			    cancel: function () {
			        $('#forms').hide()
					$('.tab input').each(function(){
						$(this).prop('checked',false)
					})
					flag++
			    }
			})
			//flag阻止多次遍历
			if(flag === 0){
				//遍历classList
				classList && classList.forEach(function(e){
					var subClassStr = ''
					e.subclass && e.subclass.forEach(function(a){
						subClassStr += '<div class="diy-width">'+
											'<input type="checkbox" class="diy-width childernCheckbox" data-english="'+ a.english_name +'" title="'+ a.name +'" value="'+ a.id +'" data-url="'+ (a.pic_url?a.pic_url:'') +'"  data-parent="'+ a.category_id +'" lay-skin="primary" />'+
										'</div>'
					})
					classStr += '<div class="centent">'+
									'<div class="parent">'+
										'<input type="checkbox" class="parentCheckbox" title="'+ e.name +'" value="'+ e.id +'" data-type="'+ e.type +'" lay-skin="primary"/>'+
									'</div>'+
									'<div class="childern">'+
										subClassStr +
									'</div>'+
								'</div>'
				})
				$('.tab').children().remove()
				$('.tab').append(classStr)
			}
			//添加过分类时，再次点击时，为勾选过的checkbox设置checked为true
			if(selectList.length !== 0){
				selectList && selectList.forEach(function(e){
					$('.tab input').each(function(){
						if($(this).attr('value') == e.id && $(this).attr('title') == e.name){
							$(this).prop('checked',true)
						}
					})
				})
			}
			form.render()
		})
		//判断是否勾选父级分类，未勾选父类子类禁止使用
		var childernCheck = false
		$(document).off('click','.childernCheckbox+div').on('click','.childernCheckbox+div',function(){
			var _this = $(this)
			if (_this.prev().is(':checked')){
				$(_this).parents('.childern').prev().children('input').prop('checked',true)
			}else {
				$(_this).parents('.diy-width').siblings().each(function(){
					if ($(this).children('.childernCheckbox').is(':checked')) {
						childernCheck = true
						return false
					} else {
						childernCheck = false
					}
				});
				if (!childernCheck){
					$(_this).parents('.childern').prev().children('input').prop('checked',false)
				}
			}
			form.render()
		})
		//勾选父类全选、取消
		$(document).off('click','.parentCheckbox+div').on('click','.parentCheckbox+div',function(){
			var _this = $(this)
			if(_this.prev().is(':checked')){
				_this.parent().next().find('.diy-width').each(function(e){
					$(this).children('input').prop("checked",true)
				})
			}else{
				_this.parent().next().find('.diy-width').each(function(){
					$(this).children('input').prop("checked",false)
				})
			}
			form.render()
		})
		//添加按钮点击事件
		$('.addToPage').on('click',function(){
			selectList = []
			parentarr = []
			//遍历选中的checkbox
			$('.tab input:checked').each(function(){
				var _this = $(this)
				//根据不同的类型放到不同的数组中
				_this.attr('data-type') ? selectList && selectList.push({name:_this.attr('title'),id:_this.attr('value'),type:_this.attr('data-type')}) : selectList.push({name:_this.attr('title'),id:_this.attr('value'),parentId:_this.attr('data-parent'),pic_url:_this.attr('data-url'),english_name:_this.attr('data-english')})
			})
			layer.close(open_index)
			$('#forms').hide()
			//展示右侧选项
			var childArr = [],
				//深拷贝数据，原数据用于再次点击添加时使用
				copyList = $.map(selectList,function(e){
					return $.extend(true,{},e)
				})
			//遍历数据，父对象放在parentarr中，子对象放在childArr中
			copyList && copyList.forEach(function(e){
				if(!e.parentId){
					parentarr.push(e)
				}else{
					childArr.push(e)
				}
			})
			//向parentarr中添加child属性，属性值为childArr中parentId = id的对象
			parentarr && parentarr.forEach(function(e){
				e.child = []
				childArr && childArr.forEach(function(a){
					e.id === a.parentId && (e.child.push(a))
				})
			})
			//遍历parentarr以type的值区分不同的种类，展示右侧的数据
			addRightData(parentarr)
			flag = 1
			$('.tab input').slice(0).prop('checked',false)
			form.render()
		})
		
		//保存模板
		$('.saved').on('click',function(){
			var headSave = $('.headSave').serializeArray() // 表单序列化数据
			headSave && headSave.forEach(function(e){
				data[e.name] = e.value
			})
			var listPaper = $('.listPaper').serializeArray() // 表单序列化数据
			listPaper && listPaper.forEach(function(e){
				data[e.name] = e.value
			})
			data.keywords_ids = JSON.stringify(parentarr)
			data.keywrod_info = JSON.stringify(parentSelectedClassList)
			data.info = $('.edit-box').prop('outerHTML')
			var type = '',method = '',newOrEdit = ''
			if(printTempId){
				type = 'put'
				method = 'adminPrintingtemp/' + printTempId
				newOrEdit = sucMsg.put
			}else{
				type = 'post'
				method = 'adminPrintingtemp'
				newOrEdit = sucMsg.post
			}
			var res = getAjaxReturn({method:method,type:type,data:data})
			if(res.status === 200){
				layer.msg(newOrEdit)
				setTimeout(function() {
					location.hash = '/set/printing/printing_temp'
				}, 1500)
			}
		})



		//操作页面的js
		// 通用：弹框关闭
		$(document).on('click', '.alert .btn-close,.alert .over-close,.alert .js_btn-cancle', function (event) {
			event.preventDefault()
			$(this).parents(".alert").fadeOut('300', function () {
				$(this).removeClass('show')
			})
		}) // 删除选中


		$("body").on('click', '.btn-delete', function (event) {
			event.preventDefault()

			if (!$(".item-active").hasClass('item-line') && !$(".item-active").hasClass('item-table')) {
				var cls = '.' + $(".item-active").attr("data-name")
				$(".group " + cls).removeClass('on')
				var name = $(".item-active").attr("data-name")
				var text = $(".item-active").text()
				text = text.slice(0,text.indexOf(':'))
				del(name,text)
			}

			if ($(".item-active").hasClass('item-table')) {
				$(".group .name-table").removeClass('on')
				tableTitle = []
				parentSelectedClassList && parentSelectedClassList.forEach(function(e,index){
					if (e.type === 1  && e.name === '表格信息'){
						parentSelectedClassList.splice(index,1)
					}
				})
			}
			$(".item-active").remove()
		})

		$('.edit-box .item').l_zoom('auto').l_drag($('.edit-box .item'),$('.edit-box'))
		$("body").on('mousedown', '.edit-box .item', function (event) {
			var _this = $(this)

			_this.addClass('item-active').siblings().removeClass('item-active')

			if ($(this).hasClass('item-line')) {
				$('.setFont').css("display", "none")
				$('.setLine').css("display", "block")
			} else {
				$('.setFont').css("display", "block")
				$('.setLine').css("display", "none")
			}
			resetOperate()
		})

		$("body").on('click', ".edit-over", function (event) {
			$(".item-active").removeClass('item-active')
			$('.setFont').css("display", "none")
			$('.setLine').css("display", "none")
		})

		function resetOperate() {
			var active = $(".item-active")
			if (!active.hasClass('item-line') && !active.hasClass('item-img')) {
				$(".setFont .font").eq(0).val(active.css("fontFamily"))
				$(".setFont .font")[1][parseInt(active.css("fontSize")) - 12].selected = true
				$(".setFont .font")[2][parseInt(active.css("letterSpacing"))].selected = true
			} else {
				$(".setFont .font").val("")
				$(".setLine .line").val("")
			}
		} // 添加元素

		function addElement(name, text, isImg,url,english_name) {
			var str = ''
			if (isImg) {
				str = '<div class="item item-img '+ name +' item-active" id="item-active" data-name="'+ name +'" data-englishName="'+ english_name +'"><img style="width:90px;height:90px;" data-englishName="'+ english_name +'" src="'+ url +'" alt=""><div class="img-over"></div></div>'
			} else {
				str = '<div class="item '+ name +' item-active" id="item-active" data-name="'+ name +'" data-englishName="'+ english_name +'"><span>'+ text +':$'+ english_name +'</span></div>'
			}
			$(".item-active").removeClass('item-active')
			editBox.append(str)
			$('.item-active').l_zoom('auto').l_drag($('.item-active'),$('.edit-box'))
			$('.setFont').css("display", "block")
			$('.setLine').css("display", "none")

			resetOperate()
		}

		function addElementLine(direction, type, isImg) {
			var width = '4px'
			var height = '4px'
			if (direction == 'w') {
				width = "80%"
				var str = '<div class="item item-line ' + name + ' item-active" data-direction="w" style="width:' + width + ';height:1px;border-width: 8px 0px 0px;border-style: ' + type + ';"><span></span></div>'
			}
			if (direction == 'h') {
				height = "80%"
				var str = '<div class="item item-line ' + name + ' item-active" data-direction="h" style="width:1px;height:' + height + ';border-width: 0px 0px 0px 8px;border-style: ' + type + ';"><span></span></div>'
			}
			$(".item-active").removeClass('item-active')
			editBox.append(str)
			$('.item-active').l_drag($('.item-active'),$('.edit-box'))
			$('.setFont').css("display", "none")
			$('.setLine').css("display", "block")
			resetOperate()
		}


		function addElementTable() {
			var x = $(".item-table").css("left") || 0
			var y = $(".item-table").css("top") || 0
			$(".item-table").remove()
			if (tableTitle.length > 0) {
				var ths = ''
				for (var i = 0; i < tableTitle.length; i++) {
					ths += '<th data-englishName="'+ tableTitle[i].english_name +'" style="border:1px solid black;padding:10px;">' + tableTitle[i].text + '</th>'
				}
				var str = '<div class="item item-table item-active" data-name="name-table" style="left:' + x + ';top:' + y + ';"><table style="white-space:normal;border:1px solid black;" border="1" cellspacing="0" cellpadding="0"><tr>' + ths + '</tr></table></div>'
				$(".item-active").removeClass('item-active')
				editBox.append(str)
				$('.item-active').l_zoom('auto').l_drag($('.item-active'),$('.edit-box'))
				$('.setFont').css("display", "none")
				$('.setLine').css("display", "none")
			}
		}

		var editBox = $(".edit-box")
		$("body").on('change', '.editBoxWidth', function (event) {
			event.preventDefault()
			var val = $(this).val()
			editBox.width(val)
		})

		$("body").on('change', '.editBoxHeight', function (event) {
			event.preventDefault()
			var val = $(this).val()
			editBox.height(val)
		})

		$("body").on('change', '.font', function (event) {
			event.preventDefault()
			var val = $(this).val()
			var type = $(this).attr("data-type")
			if (type == "fontFamily") {
				$(".item-active").css("fontFamily", val)
			} else if (type == "fontSize") {
				$(".item-active").css("fontSize", val + 'px')
			} else if (type == "letterSpacing") {
				$(".item-active").css("letterSpacing", val + 'px')
			}
		})

		$("body").on('change', '.setLine .line', function (event) {
			event.preventDefault()
			var val = $(this).val()
			var type = $(this).attr("data-type")
			var direction = $(".item-active").attr("data-direction")

			if (type == "line-type") {
				$(".item-active").css("borderStyle", val)
			} else if (type == "line-width") {
				if (direction == 'w') {
					$(".item-active").css("width", val + 'px')
				} else {
					$(".item-active").css("borderWidth", '0px 0px 0px ' + val + 'px')
				}
			} else if (type == "line-height") {
				if (direction == 'w') {
					$(".item-active").css("borderWidth", val + 'px 0px 0px')
				} else {
					$(".item-active").css("height", val + 'px')
				}
			}
		})

		$("body").on('click', '.checkbox', function (event) {
			event.preventDefault()
			var name = $(this).attr("data-name")
			var text = $(this).text()
			var isImg = $(this).hasClass('add-img')
			var url = $(this).attr('data-url')
			var parentId = $(this).attr('data-parent')
			var english_name = $(this).attr('data-english')
			var _this = $(this)
			if ($(this).hasClass('on')) {
				$(".edit-box ." + name).remove()
				del(name,text) //移除数据
			} else {
				addElement(name, text, isImg,url,english_name)
				add(_this,name,text,url,parentId,english_name) //添加数据
			}
			$(this).toggleClass('on')
		})

		function add(_this,name,text,url,parentId,english_name){
			var hash = {}
			parentSelectedClassList.push({id:_this.parent().prev().data('id'), name:_this.parent().prev().text(),type:_this.parent().prev().data('table')}) //父类数组
			childSelectedClassList.push({id:name, name:text, pic_url:url?url:'', parentId:parentId,english_name:english_name}) //子类数组
			//数组对象去重
			parentSelectedClassList = parentSelectedClassList.reduce(function (item, next) {
				hash[next.id] ? '' : hash[next.id] = true && item.push(next)
				return item
			}, [])
			//整合为一个二维数组
			parentSelectedClassList && parentSelectedClassList.forEach(function(e){
				e.child = []
				childSelectedClassList && childSelectedClassList.forEach(function(a){
					e.id == a.parentId && (e.child.push(a))
				})
			})
		}
		
		function del(name,text){
			parentSelectedClassList && parentSelectedClassList.forEach(function(e,index){
				e.child.forEach(function(a,Index){
					if (a.id === name && a.name === text){
						e.child.splice(Index,1)
					}
				})
				e.child.length === 0 && parentSelectedClassList.splice(index,1)
			})
			childSelectedClassList && childSelectedClassList.forEach(function(a,index){
				if (a.id === name && a.name === text){
					childSelectedClassList.splice(index,1)
				}
			})
		}


		$("body").on('click', '.checkbox-table', function (event) {
			event.preventDefault()
			var name = $(this).attr("data-id")
			var text = $(this).text()
			var url = $(this).parent().prev().attr('data-url')
			var parentId = $(this).attr('data-parent')
			var english_name = $(this).attr('data-english')
			var _this = $(this)
			if ($(this).hasClass('on')) {
				tableTitle.splice(tableTitle.findIndex(function (item) { return item.text === text}), 1)
				del(name,text) //移除数据
			} else {
				tableTitle.push({text:text,english_name:english_name})
				add(_this,name,text,url,parentId,english_name) //添加数据
			}
			addElementTable()
			$(this).toggleClass('on')
		})

		$("body").on('click', '.group .add', function (event) {
		  event.preventDefault()
		  var direction = $(this).attr("data-direction")
		  var type = $(this).attr("data-type")
		  var text = $(this).text()
		  addElementLine(direction, type)
		})
    });
    exports('set/printingAdd', {})
});
