/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 创建于 2019/4/20
 * js 店铺装修模板新增编辑
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
        var layDate = layui.laydate;
        var sucMsg = setter.successMsg;//成功提示 数组
		var baseUrl = setter.baseUrl;
		var sucMsg = setter.successMsg;//成功提示 数组
        //以上定义的变量使用小驼峰命名法，与自定义变量区分，主要为 1、layui自带，2、config定义

        //以下为页面使用自定义变量，遵循下划线方式命名变量
        var open_index;//定义弹出层，方便关闭
        var operation_id;//数据表格操作需要用到单条 id
        var arr = {};//全局ajax请求参数
        var ajax_type;//ajax 请求类型，一般用于判断新增或编辑
        var add_edit_form = $('#add_edit_form');//常用的表单
        var pic_url = '';//div 转图片保存的 base64 或 url
		var saa_key = sessionStorage.getItem('saa_key');

        /*diy设置开始*/
        form.render();

        //页面不同属性
        var ajax_method = '/shopDecoration';//新ajax需要的参数 method
        /*diy设置结束*/
		// getAppid();
		// //获取appid
		// function getAppid() {
		// 	var arr = {
		// 		method:'merchantApps',
		// 		type:'get'
		// 	}
		// 	var res = getAjaxReturn(arr);
		// 	var appidString = '';
		// 	res.data && res.data.forEach(function(e){
		// 		appidString += '<option value="'+e.id+'" class="remove">'+e.name+'</option>';
		// 	})
		// 	$(".temSelect remove").remove();
		// 	$(".disabledOption").after(appidString);
		// }
		
		
		
		/**
		 * Created by UICUT.com on 2016/12/31.
		 * Contact QQ: 215611388
		 */
		// https://api2.juanpao.com/shop/design/get  获取json数据
		// https://api2.juanpao.com/shop/design/edit  新增/保存json数据
		// https://www.showdoc.cc/304514022975829?page_id=1741155138234408  图片上传
		$(function() {
			$("#uicut-app").height($(window).height());
		});
		$(window).resize(function(event) {
			$("#uicut-app").height($(window).height());
		});
		var showdew = '',ues = '',type = '',openJumpPage = '',
		selectId = '';//选择的组件id
		var data = {
			addId: 0, //编辑区域选中的元素下标
			pageParam: [], //编辑区域存放的数据
			temp: {},//右侧临时数据对象
			ships:[],//商品数组
			classifications:[],//商品分类的数组
			modelType:'',//打开的模板类型
			coupons:[],//优惠券数组
			search:'',//查询条件存储
			ue:'',
			ues:0,
			ids:[],
			itemImgIndex:'',
			// 1 	轮播图
			// 2 	广告位
			// 3 	宫格导航
			// 4 	标题
			// 5 	图文集
			// 6 	图片列表
			// 7 	三方图
			// 8 	四方图
			// 9 	视频
			// 10 	音频
			// 11 	富文本
			// 12 	辅助空白
			// 13 	分割线
			// 14 	悬浮图标
			// 15 	按钮
			// 16 	表单
			// 17 	客服
			// 18 	公告
			// 19 	流量主
			// 20 	商品集
			// 21 	产品集
			// 22 	搜索框
			// 23	招聘
			// 24	留言板
			// 25	门店
			// 26	位置
			defaultModels: [{
				type: 1,
				edit: true,
				details: {imgs: [{src:"./decoration/images/uc-banner.jpg", link: "link1"}],dotShow: true,color1: "#ff0000",color2: "#fff",boxHeight: 180, style: 1}
			}, {
				type: 2,
				edit: true,
				details: {imgs: [{src: "./decoration/images/uc-banner.jpg",link: "link1",w: '80%'}],boxHeight: 180}
			}, {
				type: 3,
				edit: true,
				details: {
					col: {
						1: '50%',
						2: '33.33%',
						3: '25%',
						4: '20%',
					},
					fontSize: '12px',
					imgs: [
						{src: "./decoration/images/icon-grid.png",text: "名称",link: ""}
					],
					color1: "#333",
					color2: "#fff",
					radius: 20,
					style: 4
				}
			}, {
				type: 4,
				edit: true,
				details: {fontSize: '12px',style: '1',color1: "#333",color2: "#fff",text: "标题名称"}
			}, {
				type: 5,
				edit: true,
				details: {fontSize: '12px',style: '1',color1: "#333",color2: "#fff",imgs: [{src: "./decoration/images/product1.png",title: "标题",text: "内容内容",link: ""}, {src: "./decoration/images/product1.png",title: "标题2",text: "内容内容2",link: ""}]}
			}, {
				type: 6,
				edit: true,
				details: {style: '1',color2: "#fff",radius: 10,imgs: [{src: "./decoration/images/bannerList.png",text: "标题",link: ""}]}
			}, {
				type: 7,
				edit: true,
				details: {imgs: [{src: "./decoration/images/three-1.png",link: ""}, {src: "./decoration/images/three-2.png",link: ""}, {src: "./decoration/images/three-2.png",link: ""}]}
			}, {
				type: 8,
				edit: true,
				details: {
					imgs: [{src: "./decoration/images/four-1.png",link: ""}, {src: "./decoration/images/three-1.png",link: ""}, {src: "./decoration/images/three-1.png",link: ""}, {src: "./decoration/images/three-1.png",link: ""}]}
			}, {
				type: 9,
				edit: true,
				details: {imgs: [{src: "./decoration/images/uc-banner.jpg",link: "link1"}],boxHeight: 180}
			}, {
				type: 10,
				edit: true,
				details: {
					imgs: [{src: "./decoration/images/three-1.png",link: "link1",name: "name1",author: "author1",time: '00:00'}],boxHeight: 180}
			}, {
				type: 11,
				edit: true,
				details: {text: '<p>请输入</p>',color2: "#fff"}
			}, {
				type: 12,
				edit: true,
				details: {color2: '#eee',boxHeight: 10}
			}, {
				type: 13,
				edit: true,
				details: {style: '1',color1: '#eee',color2: '#fff',boxHeight: 10,paddingTopBottom: 5}
			}, {
				type: 14,
				edit: true,
				details: {positionRight: 1,positionBottom: 1,opacity: .9,goTop:false,shire:true,imgs: [{src: "https://imgs.juanpao.com/2019%2F06%2F15%2F15605812215d049465e5868.png",link: "link1"}]}
			}, {
				type: 15,
				edit: true,
				details: {text: '按钮',borderShow: true,iconShow: true,radius: 10,color1: "#000",color2: "#fff",color3: "#ccc",imgs: [{src: "./decoration/images/uc-banner.jpg",link: "link1"}]}
			}, {
				type: 16,
				edit: true,
				details: {text: '请选择表单'}
			}, {
				type: 17,
				edit: true,
				details: {positionRight: 1,positionBottom: 1,opacity: 0.9,imgs: [{src: "https://imgs.juanpao.com/2019%2F06%2F15%2F15605811505d04941ee6a43.png",link: "link1"}]}
			}, {
				type: 18,
				edit: true,
				details: {text: '请填写公告内容',color1: "#ff0000",color2: "#fff",imgs: [{src: "./decoration/images/sound.png",text: '请填写公告内容'}]}
			}, {
				type: 19,
				edit: true,
				details: {boxHeight: 180,id: null}
			}, {
				type: 20,
				edit: true,
				details: {fontSize: '12px',style: "1",color1: "#333",color2: "#fff",imgs: [{src: "./decoration/images/product1.png",title: "标题",text: "内容内容",price: 0.00,link:''}, {src: "./decoration/images/product1.png",title: "标题2",text: "内容内容2",price: 0.00,link:''}]}
			}, {
				type: 21,
				edit: true,
				details: {fontSize: '12px',style: '1',color1: "#333",color2: "#fff",imgs: [{src: "./decoration/images/bannerList.png",text: "标题",link: ""}]}
			}, {
				type: 22,
				edit: true,
				details: {text: '请输入',color1: "#fff",color2: "#fff",color3: "#333"}
			}, {
				type: 23,
				edit: true,
				details: {style: 1,imgs: [{text: "职位：职位名"}]}
			}, {
				type: 24,
				edit: true,
				details: {color2: "#fff"}
			}, {
				type: 25,
				edit: true,
				details: {name: '门店名称',time: '8:00-18:00',tel: '门店名称',color1: "#333",color2: "#fff",imgs: [{src: "./decoration/images/shop.png"}]}
			}, {
				type: 26,
				edit: true,
				details: {color1: "#333",color2: "#fff",addr: '',style: 1,lon: '119.1635674238205',lat: '34.5723181626876'}// 拾取坐标  按钮未处理
			},{
				type: 27,
				edit: true,
				details: {imgs:[{src:'./decoration/images/coupons.png',link:''}]}
			},{
				type: 28,
				edit: true,
				details: {style:'1', show: true}}
			]
		};
		//交换数组
		var swapItems = function(arr, index1, index2) {
			arr[index1] = arr.splice(index2, 1, arr[index1])[0];
			return arr;
		};
		
		//监听data.ue值的变化情况
		Object.defineProperty(data,'ue',{
			get:function(){
				return ues;
			},
			set:function(newValue){
				ues=newValue;
				data.ue.addListener('contentChange',function(){
					console.log(type)
					if(type == 11){
						data.pageParam.forEach(function(e){
							if(e.id == selectId){
								e.details.text = data.ue.getContent()
							}
						})
					}
				})
			}
		})
		
		//打开model，查询商品分类或者优惠券
		function openThis(e,url,param){
			showdew = layer.open({
				type: 1,
				title:false,
				closeBtn :1,
				content: $(".ships"),
				shade: 0.5,
				offset: '100px',
				area: ['970px', '600px'],
				cancel: function () {
					$(".ships").hide();
					data.search = '';
				}
			})
			obj = {pageParam:data.pageParam,addId:data.addId,index:e};
			var res = getAjaxReturnKey({method:url,type:'get',params:param ?param:''});
			if((data.modelType == 'ship' && res.data && res.status == 200) || (data.modelType == 'productSet' && res.data && res.status == 200)){
				res.data.forEach(function(e){
					if(e.sub && e.sub.length>0){
						e.sub.forEach(function(ev){
							data.classifications.push(ev)
						})
					}
				})
				$(".selecteds .remove").remove();
			}else if(data.modelType == 'coupons' && res.data && res.status == 200){
				data.coupons = res.data;
				if(data.coupons.length >=7){
					$(".coupons").css('overflow-y','scroll');
				}
			}
		}
		var typeString = '',//临时存储执行select方法的点击对象
			obj = {},//临时存储选择商品或者优惠券时，需要更新data.pageParam的变量
			flag = 0;
		var vm = new Vue({
			el: "#uicut-app",
			data: data,
			created: function created() {
			},
			methods: {
				// 添加新模块，并设定为编辑状态，其它模块取消编辑状态
				btnAddNewModel: function btnAddNewModel(e) {
					type = e;
					var idx = e - 1;
					var temp = data.pageParam || [];
					temp.forEach(function(item, index) {
						temp[index].edit = false;
					});
					data.pageParam = temp;
					var tempNewModel = JSON.parse(JSON.stringify(data.defaultModels[idx]));
					tempNewModel.id = data.addId = data.pageParam.length;
					data.pageParam.push(tempNewModel);
					selectId = data.pageParam.length-1;
					data.temp = tempNewModel;
					setTimeout(function() {
						colorChooseInit();
					}, 30);
					if(e == 11){
						$(".div").show()
						$("#content").show()
						data.ue =UE.getEditor('content');
						if(!flag){
							data.ue.addListener('ready',function(){
								data.ue.setContent('<p>请输入</p>')
								flag = 1;
								data.ues ++;
							})
						}else{
							data.ue.setContent('<p>请输入</p>')
						}
						
					}else{
						$("#content").hide()
					}
				},
				// 选择模块
				btnChooseModel: function btnChooseModel(id) {
					selectId = data.addId = id;
					var temp = data.pageParam;
					type = temp[id].type;
					console.log(data.temp);
					temp.forEach(function(item, index) {
						if (item.id == id) {
							item.edit = true;
							data.temp = item;
						} else {
							item.edit = false;
						}
					});
					console.log(data.temp)
					if(temp[id].type != 11){
						$(".div").hide()
					}else{
						$(".div").show()
						$("#content").show()
						data.ue =UE.getEditor('content');
						data.ue.addListener('ready',function(){
							data.ue.setContent(temp[id].details.text)
							flag = 1;
							data.ues ++;
						})
					}
					setTimeout(function() {
						colorChooseInit();
					}, 30);
				},
				//选择商品
				select:function(e,str,type,url,param){//e:组件id，str：选择商品还是商品id，type：打开的model类型，url：请求接口，param：获取优惠券需要的参数
					data.modelType = type;
					typeString = str;
					openThis(e,url,param);
				},
				link:function(itemImgIndex){
					data.itemImgIndex = itemImgIndex
					console.log(itemImgIndex)
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
				},
				//选择商品分类
				thisOption:function(e){
					$.ajax({
						url:baseUrl + '/merchantGoods',
						type:'get',
						data:{key:saa_key,m_category_id:e.target.value},
						headers:{'Access-Token': layui.data(setter.tableName).access_token},
						async:false,
						success:function(res){
							if(res.data && res.status == 200){
								data.ships = res.data;
								if(data.ships.length >=7){
									$(".scrolls").css('overflow-y','scroll');
								}
							}else if(res.status == 204){
								data.ships = [];
							}
						}
					})
				},
				//双击选择商品
				productSet :function(ship){
					if(typeString == 'productSet'){
						var lastData = {title:ship.name,text:ship.short_name,price:ship.price,src:ship.pic_urls.split(',')[0],link:ship.id};
						vm.$set(obj.pageParam[obj.addId].details.imgs,obj.index,lastData); //更新data.param中的数据
						layer.close(showdew);
						$(".ships").hide();
						data.ships = [];
					}
					data.search = '';
				},
				//双击选择商品id
				linkSelectShip:function(id){
					if(typeString == 'link'){
						obj.pageParam[obj.addId].details.imgs.forEach(function(e,index){
							if(obj.index == index){
								e.link = id
							}
						})
						data.temp = obj.pageParam[obj.addId]
						layer.close(showdew);
						$(".ships").hide();
						data.ships = [];
					}
					data.search = '';
				},
				searchs:function(url,param,type){
					var res = getAjaxReturnKey({method:url,type:'get',params:param + data.search});
					if(type == 'ship'){
						if(res.data && res.status == 200){
							data.ships = res.data;
							if(data.ships.length >=7){
								$(".scrolls").css('overflow-y','scroll');
							}
						}else if(res.status == 204){
							data.ships = [];
						}
					}else{
						if(res.data && res.status == 200){
							data.coupons = res.data;
							if(data.coupons.length >=7){
								$(".coupons").css('overflow-y','scroll');
							}
						}else if(res.status == 204){
							data.coupons = [];
						}
					}
				},
				upRecord:function(x,y){//上移
					swapItems(y, x, x - 1);
				},
				downRecord:function(x,y){//下移
					swapItems(y, x, x + 1);
				},
				// 删除模块
				btnDeleteModel: function btnDeleteModel(e) {
					var id = e.target.dataset.id;
					if (id == undefined || id == '') {
						layer.msg("未选择模块")
					} else {
						var temp = data.pageParam;
						temp.forEach(function(item, index) {
							if (item.id == id) {
								data.pageParam.splice(index, 1);
								data.temp = {};
							}
						});
						data.pageParam.forEach(function(e,index){
							if(id<=index){
								data.pageParam[index].id = data.pageParam[index].id -1;
							}
						})
					}
				},
				// 上移
				// 下移
				btnMove: function btnMove(e) {
					var type = e.target.dataset.type;
					var id = e.target.dataset.id;
					var temp = JSON.parse(JSON.stringify(data.pageParam));
					var i;
					temp.forEach(function(item, index) {
						if (item.id == id) {
							i = index;
						}
					});
					var thisData = JSON.parse(JSON.stringify(data.pageParam[i]));
					var max = data.pageParam.length - 1;
		
					if (type == "Prev") {
						if (i > 0) {
							var prevData = JSON.parse(JSON.stringify(data.pageParam[i - 1]));
							prevData.id = prevData.id+ 1;
							thisData.id = thisData.id - 1;
							temp[i - 1] = thisData;
							temp[i] = prevData;
							data.temp = temp[i-1];
						}
					}
		
					if (type == "Next") {
						if (i < max) {
							var nextData = data.pageParam[i + 1];
							nextData.id = nextData.id - 1;
							thisData.id = thisData.id + 1;
							temp[i] = nextData;
							temp[i + 1] = thisData;
							data.temp = temp[i+1];
						}
					}
					data.pageParam = temp;
				},
				// 各种子项操作
				// 添加轮播图片
				addBannerImg: function addBannerImg(e) {
					var obj = {
						src: "./decoration/images/bannerList.png",
						link: "",
						text:"",
						title:""
					};
					data.temp.details.imgs.push(obj);
				},
				addBannerImgs: function addBannerImgs(e) {
					var obj = {
						src: "./decoration/images/coupons.png",
						link: "",
						text:"",
						title:""
					};
					data.temp.details.imgs.push(obj);
				},
				// 删除轮播图片
				deleteBannerImg: function deleteBannerImg(e) {
					if(e == 0){
						this.btnDeleteModel(selectId);
					}else{
						var temp = [];
						temp = data.temp.details.imgs;
						temp.forEach(function(item, index) {
							console.log(item,index)
							if (e == index) {
								temp.splice(index,1)
							}
						});
						data.pageParam.forEach(function(e,index){
							if(e.id == selectId){
								data.pageParam[index].details.imgs = temp
							}
						})
					}
					
				}
			}
		});
		vm.$data === data;
		vm.$el === document.getElementById('uicut-app'); // 选择颜色
		
		//监听id= choicePageUrl 的元素 的display = none的时候获取sessionStorage中的值，目前没有别的好办法
		var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
		var element = document.querySelector('#choicePageUrl');
		var observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if (mutation.type == "attributes") {
					if(mutation.target.style.display === 'none'){
						var link = JSON.parse(sessionStorage.getItem('menuLink'))
						data.pageParam[data.temp.id].details.imgs[data.itemImgIndex].link = link.choice_page_url
					}
				}
			});
		});
		observer.observe(element, {
			attributes: true,
			attributeFilter: ['style']
		});
		
		function colorChooseInit(e) {
			$('.chooseColor').each(function(e) {
				$(this).minicolors({
					control: $(this).attr('data-control') || 'hue',
					defaultValue: $(this).attr('data-defaultValue') || '',
					inline: $(this).attr('data-inline') === 'true',
					letterCase: $(this).attr('data-letterCase') || 'lowercase',
					opacity: $(this).attr('data-opacity'),
					position: $(this).attr('data-position') || 'bottom left',
					change: function change(hex, opacity) {
						var log;
						try {
							log = hex ? hex : 'transparent';
							if (opacity) log += ', ' + opacity;
							var name = $(this).attr("data-name");
							var colors = ['color1','color2','color3']
							colors.forEach(function(e,index){
								if(name == e){
									data.temp.details[colors[index]] = log
								}
							})
						} catch (e) {}
					},
					theme: 'default'
				});
			});
		} // 是否显示轮播点
		 
		// 上传图片
		$("body").on('change', '.btnInputUploadImg', function(event) {
			// event.preventDefault();
			console.log(33);
			var _this = $(this);
			var file = event.target.files[0];
			var freader = new FileReader();
			if (file.size > 1024 * 1024 * 1) {
				alert('上传的图片大小超过1M');
				file.value = '';
				return;
			}
			this.imgName = file.name;
			this.imgFile = event.target.files;
			freader.readAsDataURL(file); //读取照片
			freader.onload = function(e) {
				//读取成功
				_this.siblings('img').attr("src", freader.result); // 更新临时数据
				var index = _this.attr("data-id");
				for (var i = data.temp.details.imgs.length - 1; i >= 0; i--) {
					if (i == index) {
						data.temp.details.imgs[i].src = freader.result;
					}
				}
				var url = 'http://' + window.location.hostname + '/api/web/index.php/base64'; 
				var postData = {
					pic_url: freader.result
				};
				$.ajax({
					type: 'POST',
					url: url,
					dataType: 'json',
					data: postData,
					cache: false,
					header: {
						'Content-Type': 'application/json'
					},
					error: function error() {
						console.log('提交失败！');
						return false;
					},
					success: function success(res) {
						if (res.status == 200) {
							var _url = res.data;
		
							for (var i = data.temp.details.imgs.length - 1; i >= 0; i--) {
								if (i == index) {
									data.temp.details.imgs[i].src = _url;
									console.log("图片：网终地址更新成功");
								}
							}
						} else {
							console.log("图片：上传返回失败");
						}
					}
				});
			};
		});
		
		$(".scroll").niceScroll({
			cursorcolor: "#eee",
			cursorwidth: "8px",
			cursorborder: ""
		});
		//编辑进来时，判断sessionStorage.getItem('decoration_id')是否存在
		if (sessionStorage.getItem('myTemplateId')) {
			var arr = {
				method:'shopDecoration/' + sessionStorage.getItem('myTemplateId'),
				type: 'get'
			}
			var res = getAjaxReturnKey(arr);
			if(res.status == 200){
				$("input[name=name]").val(res.data.name);
				$(".temSelect").val(res.data.appid);
				data.pageParam = res.data.info;
				data.pageParam && data.pageParam.forEach(function(e){
					e.edit = false;
				})
				// if(res.data.is_wx_default == 1 && res.data.is_mini_default == 1){
				// 	$(".radios input[value=0]").prop("checked","checked")
				// }else if(res.data.is_wx_default == 1 && res.data.is_mini_default == 0){
				// 	$(".radios input[value=1]").prop("checked","checked")
				// }else if(res.data.is_wx_default == 0 && res.data.is_mini_default == 1){
				// 	$(".radios input[value=2]").prop("checked","checked")
				// }else{
				// 	$(".radios input[value=3]").prop("checked","checked")
				// }
				//$(".temSelect").val(res.data.appid);
			}
		}
		//保存修改页面按钮执行事件
		$(".saving").click(function(){
			//将div转成图片
			var isEdit =  $(this).data("value");
			html2canvas(document.querySelector("#the_img_div"), {
				useCORS: true
			}).then(function(canvas) {
				var canvas2 = document.createElement('canvas');
				var ctx = canvas2.getContext("2d");
				canvas2.width = 395;
				canvas2.height = 831;
				var img = new Image();
				img.src = canvas.toDataURL('image/png');
				img.onload = function() {
					ctx.drawImage(img, 10, 10, 380 ,800);
					// that.imgBase64 =  canvas2.toDataURL('image/png');//生成的图片base64码
					// that.imgpost(that.imgBase64);
					//图片转成功后请求后台新增或编辑
					var is_wx_default = '0',is_mini_default = '1';
					// if($(".radios input").val() == 0){
					// 	is_wx_default = 1;
					// 	is_mini_default = 1;
					// }else if($(".radios input").val() == 1){
					// 	is_wx_default = 1;
					// 	is_mini_default = 0;
					// }else if($(".radios input").val() == 2){
					// 	is_wx_default = 0;
					// 	is_mini_default = 1;
					// }else{
					// 	is_wx_default = 0;
					// 	is_mini_default = 0;
					// }
					console.log(data.pageParam)
					var postData = {
						name: $("input[name=name]").val(),
						//appid: $(".temSelect").val(),
						is_wx_default:is_wx_default,
						is_mini_default:is_mini_default,
						info: data.pageParam.length > 0 ? JSON.stringify(data.pageParam) : '',
						is_edit: isEdit,
						status:'1',
						key:saa_key,
						pic_url: canvas2.toDataURL('image/png')
					};
					$.ajax({
						type: 'put',
						url: baseUrl + '/shopDecoration/' + sessionStorage.getItem('myTemplateId'),
						data: postData,
						async: false,
						headers: {'Access-Token': layui.data(setter.tableName).access_token},
						error: function error(res) {
							return false;
						},
						success: function success(res) {
							if(res.status == 200){
								sessionStorage.removeItem('decoration_id')
								layer.msg(sucMsg.put, {
									icon: 1,
									time: 2000 //2秒关闭（如果不配置，默认是3秒）
								}, function () {
									window.history.go(-1);
								});
							}
						}
					});
					return false;
				}
			});
		})
    });
    exports('decorationEdit', {})
});