/**
 * Created by UICUT.com on 2016/12/31.
 * Contact QQ: 215611388
 */
// https://api.juanpao.com/shop/design/get  获取json数据
// https://api.juanpao.com/shop/design/edit  新增/保存json数据
// https://www.showdoc.cc/304514022975829?page_id=1741155138234408  图片上传
$(function() {
	$("#uicut-app").height($(window).height());
});
$(window).resize(function(event) {
	$("#uicut-app").height($(window).height());
});
var data = {
	addId: 0,
	pageParam: [],
	temp: {},
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
		details: {height: "",imgs: [{src:"./decoration/images/uc-banner.jpg", link: "link1"}],dotShow: true,color1: "#ff0000",color2: "#999999",boxHeight: 180}
	}, {
		type: 2,
		edit: true,
		details: {height: "",imgs: [{src: "./decoration/images/uc-banner.jpg",link: "link1",w: '80%'}],boxHeight: 180}
	}, {
		type: 3,
		edit: true,
		details: {col: '25%',fontSize: '12px',imgs: [{src: "./decoration/images/icon-grid.png",text: "名称",link: ""}],color1: "#333",color2: "#fff",radius: "20px"}
	}, {
		type: 4,
		edit: true,
		details: {fontSize: '12px',style: 1,color1: "#333",color2: "#fff",text: "标题名称"}
	}, {
		type: 5,
		edit: true,
		details: {fontSize: '12px',style: 1,color1: "#333",color2: "#fff",imgs: [{src: "./decoration/images/product1.png",title: "标题",text: "内容内容",link: ""}, {src: "./decoration/images/product1.png",title: "标题2",text: "内容内容2",link: ""}]}
	}, {
		type: 6,
		edit: true,
		details: {style: 1,color2: "#fff",radius: "0px",imgs: [{src: "./decoration/images/bannerList.png",text: "标题",link: ""}]}
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
		details: {text: '请输入',color2: "#0080ff"}
	}, {
		type: 12,
		edit: true,
		details: {color2: '#eee',boxHeight: 10}
	}, {
		type: 13,
		edit: true,
		details: {style: 1,color1: '#eee',color2: '#fff',boxHeight: 10,paddingTopBottom: '5px'}
	}, {
		type: 14,
		edit: true,
		details: {positionRight: '1%',positionBottom: '1%',opacity: .9,imgs: [{src: "./decoration/images/qq.png",link: "link1"}]}
	}, {
		type: 15,
		edit: true,
		details: {text: '按钮',borderShow: true,iconShow: true,radius: "0px",color1: "#fff",color2: "#0080ff",color3: "#ccc",imgs: [{src: "./decoration/images/uc-banner.jpg",link: "link1"}]}
	}, {
		type: 16,
		edit: true,
		details: {text: '请选择表单'}
	}, {
		type: 17,
		edit: true,
		details: {positionRight: '1%',positionBottom: '1%',opacity: .9,imgs: [{src: "./decoration/images/service.png",link: "link1"}]}
	}, {
		type: 18,
		edit: true,
		details: {text: '请填写公告内容',color1: "#ff0000",color2: "#0080ff",imgs: [{src: "./decoration/images/sound.png"}]}
	}, {
		type: 19,
		edit: true,
		details: {boxHeight: 180,id: null}
	}, {
		type: 20,
		edit: true,
		details: {fontSize: '12px',style: 1,color1: "#333",color2: "#fff",imgs: [{src: "./decoration/images/product1.png",title: "标题",text: "内容内容",price: 0.00}, {src: "./decoration/images/product1.png",title: "标题2",text: "内容内容2",price: 0.00}]}
	}, {
		type: 21,
		edit: true,
		details: {fontSize: '12px',style: 1,color1: "#333",color2: "#fff",imgs: [{src: "./decoration/images/bannerList.png",text: "标题",link: ""}]}
	}, {
		type: 22,
		edit: true,
		details: {text: '请输入',color1: "#fff",color2: "#f2f2f2",color3: "#333"}
	}, {
		type: 23,
		edit: true,
		details: {style: 1,imgs: [{text: "职位：职位名"}]}
	}, {
		type: 24,
		edit: true,
		details: {color2: "#f2f2f2"}
	}, {
		type: 25,
		edit: true,
		details: {name: '门店名称',time: '8:00-18:00',tel: '门店名称',color1: "#333",color2: "#fff",imgs: [{src: "./decoration/images/shop.png"}]}
	}, {
		type: 26,
		edit: true,
		details: {color1: "#333",color2: "#fff",addr: '',style: 1,lon: '119.1635674238205',lat: '34.5723181626876'}}] // 拾取坐标  按钮未处理
};
var vm = new Vue({
	el: "#uicut-app",
	data: data,
	created: function created() {
		// 更新模块
		this.$watch("temp", function() {
			var temp = data.pageParam;
			temp.forEach(function(item, index) {
				if (item.id == data.temp.id) {
					data.pageParam[index] = data.temp;
				}
			});
		});
	},
	methods: {
		// 添加新模块，并设定为编辑状态，其它模块取消编辑状态
		btnAddNewModel: function btnAddNewModel(e) {
			var idx = e - 1;
			var temp = data.pageParam;
			temp.forEach(function(item, index) {
				temp[index].edit = false;
			});
			data.pageParam = temp;
			setTimeout(function() {
				var tempNewModel = JSON.parse(JSON.stringify(data.defaultModels[idx]));
				tempNewModel.id = data.addId;
				data.addId++;
				data.pageParam.push(tempNewModel);
				data.temp = tempNewModel;
				setTimeout(function() {
					colorChooseInit();
				}, 30);
			}, 100);
		},
		// 删除模块
		btnDeleteModel: function btnDeleteModel(e) {
			var id = e.target.dataset.id;
			console.log(id);

			if (id == undefined || id == '') {
				console.log("未选择模块");
			} else {
				var temp = data.pageParam;
				temp.forEach(function(item, index) {
					if (item.id == id) {
						data.pageParam.splice(index, 1);
						data.temp = {};
					}
				});
			}
		},
		// 选择模块
		btnChooseModel: function btnChooseModel(id) {
			var temp = data.pageParam;
			temp.forEach(function(item, index) {
				if (item.id == id) {
					item.edit = true;
					data.temp = item;
				} else {
					item.edit = false;
				}
			});
			setTimeout(function() {
				colorChooseInit();
			}, 30);
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
			console.log('i', i);
			var thisData = JSON.parse(JSON.stringify(data.pageParam[i]));
			var max = data.pageParam.length - 1;

			if (type == "Prev") {
				if (i > 0) {
					var prevData = JSON.parse(JSON.stringify(data.pageParam[i - 1]));
					temp[i - 1] = thisData;
					temp[i] = prevData;
				}
			}

			if (type == "Next") {
				if (i < max) {
					var nextData = JSON.parse(JSON.stringify(data.pageParam[i + 1]));
					var nextData = data.pageParam[i + 1];
					temp[i] = nextData;
					temp[i + 1] = thisData;
				}
			}

			data.pageParam = temp;
		},
		// 各种子项操作
		// 添加轮播图片
		addBannerImg: function addBannerImg(e) {
			var obj = {
				src: "",
				link: ""
			};
			data.temp.details.imgs.push(obj);
		},
		// 删除轮播图片
		deleteBannerImg: function deleteBannerImg(e) {
			console.log(e.target.dataset.id);
			var idx = e.target.dataset.id;
			var temp = data.temp.details.imgs;
			var tempData = [];
			temp.forEach(function(item, index) {
				console.log(item, index);

				if (idx != index) {
					tempData.push(item);
				}
			});
			data.pageParam[0].details.imgs = tempData;
		}
	}
});
vm.$data === data;
vm.$el === document.getElementById('uicut-app'); // 选择颜色

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

					if (name == 'color1') {
						data.temp.details.color1 = log;
					} else if (name == 'color2') {
						data.temp.details.color2 = log;
					}
				} catch (e) {}
			},
			theme: 'default'
		});
	});
} // 是否显示轮播点


$("body").on('click', '.uc-radio', function(event) {
	event.preventDefault();
	$(this).addClass('on').siblings().removeClass('on');
	var type = $(this).attr("data-name"); // 轮播图-轮播点

	if (type == "dotShow") {
		if ($(this).index() == 0) {
			data.temp.details.dotShow = true;
		} else {
			data.temp.details.dotShow = false;
		}
	} // 宫格导航 列数	 二三四五


	if (type == "col") {
		data.temp.details.col = $(this).attr("data-value");
	} // 宫格导航 字号  小中大


	if (type == "fontSize") {
		data.temp.details.fontSize = $(this).attr("data-value");
	}

	if (type == "style") {
		data.temp.details.style = $(this).attr("data-value");
	} // 按钮：边框/图标


	if (type == "borderShow") {
		if ($(this).index() == 0) {
			data.temp.details.borderShow = true;
		} else {
			data.temp.details.borderShow = false;
		}
	}

	if (type == "iconShow") {
		if ($(this).index() == 0) {
			data.temp.details.iconShow = true;
		} else {
			data.temp.details.iconShow = false;
		}
	}
}); // banner框高度

$("body").on('change', '.js_ration', function(event) {
	event.preventDefault();
	var val = $(this).val();
	var type = $(this).attr("data-name"); // 框高

	if (type == "boxHeight") {
		$(this).siblings('.ration-value').text(val + '%');
		data.temp.details.boxHeight = $(this).width() * val / 100;
	}

	if (type == "radius") {
		$(this).siblings('.ration-value').text(val + '%');
		data.temp.details.radius = val + 'px';
	} // 线条 上下padding


	if (type == "paddingTopBottom") {
		$(this).siblings('.ration-value').text(val + '%');
		data.temp.details.paddingTopBottom = val + 'px';
	} // 悬浮图标/客服


	if (type == "positionRight") {
		$(this).siblings('.ration-value').text(val + '%');
		data.temp.details.positionRight = val + '%';
	}

	if (type == "positionBottom") {
		$(this).siblings('.ration-value').text(val + '%');
		data.temp.details.positionBottom = val + '%';
	}

	if (type == "opacity") {
		$(this).siblings('.ration-value').text(val + '%');
		data.temp.details.opacity = val / 100;
	}
}); // 上传图片

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
		console.log('本地图片源:');
		console.log(e);
		console.log(freader);

		_this.siblings('img').attr("src", freader.result); // 更新临时数据


		var index = _this.attr("data-id");

		console.log(index);

		for (var i = data.temp.details.imgs.length - 1; i >= 0; i--) {
			if (i == index) {
				data.temp.details.imgs[i].src = freader.result;
			}
		}

		var url = 'http://api.juanpao.com/base64';
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
				console.log('res: ', res);

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

//保存修改页面按钮执行事件
$(".btns-submit").click(function() {
	//将div转成图片
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
			ctx.drawImage(img, 10, 10, 452, 169);
			// that.imgBase64 =  canvas2.toDataURL('image/png');//生成的图片base64码
			// that.imgpost(that.imgBase64);
			//图片转成功后请求后台新增或编辑
			var url = baseUrl + '/decoration';
			var postData = {
				name: $("input[name=name]").val(),
				appid: $(".temSelect").val(),
				info: '1',
				is_enable: $(this).data("enbale"),
				pic_url: canvas2.toDataURL('image/png'),
				data: data.pageParam
			};
			$.ajax({
				type: 'POST',
				url: url,
				data: postData,
				async: false,
				headers: {
					'Access-Token': layui.data(setter.tableName).access_token
				},
				error: function error() {
					console.log('提交失败！');
					return false;
				},
				success: function success(res) {
					console.log('res: ', res);
				}
			});
			return false;
		}
	});
});

//添加到模板库按钮执行事件
$(".btn_add").click(function() {
	//将div转成图片
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
			ctx.drawImage(img, 10, 10, 452, 169);
			// that.imgBase64 =  canvas2.toDataURL('image/png');//生成的图片base64码
			// that.imgpost(that.imgBase64);
			//图片转成功后请求后台新增或编辑
			var url = 'https://api.juanpao.com/shop/design/edit';
			var postData = {
				name: '',
				pic_url: canvas2.toDataURL('image/png'),
				data: data.pageParam
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
					console.log('res: ', res);
				}
			});
			return false;
		}
	});
});
