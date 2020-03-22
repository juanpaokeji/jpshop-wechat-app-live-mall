/**
 * Created by UICUT.com on 2016/12/31.
 * Contact QQ: 215611388
 */


// https://api.juanpao.com/shop/design/get  获取json数据
// https://api.juanpao.com/shop/design/edit  新增/保存json数据
// https://www.showdoc.cc/304514022975829?page_id=1741155138234408  图片上传




$(function(){
	$("#uicut-app").height($(window).height())
})
$(window).resize(function(event) {
	$("#uicut-app").height($(window).height())
});




let data={
	isTest:true,
	musicTimer:null,
	loadingPercent:0,
	minLoadingTime:600,
	fullWidth: document.documentElement.clientWidth,
	pageParam:[

	{
		id:0,
		type:"banner",
		edit:true,			//编辑中
		details:{
			height:"",
			imgs:[
			{src:"images/uc-banner.jpg",link:"link1"},
			{src:"",link:"link2"},
			],
			dotShow:true,
			dotColorSelect:"#ff0000",
			dotColor:"#ffffff",
			boxHeight:180,
		},
	},


	],

};
let vm = new Vue({
	el:"#uicut-app",
	data:data,
	created: function(){

	},
	methods: {
		// 添加轮播图片
		addBannerImg:(e)=>{
			let obj={src:"",link:""}
			data.pageParam[0].details.imgs.push(obj)
		},
		// 删除轮播图片
		deleteBannerImg:(e)=>{
			console.log(e.target.dataset.id)
			var idx=e.target.dataset.id
			let temp=data.pageParam[0].details.imgs
			let tempData=[]
			temp.forEach((item,index)=>{
				console.log(item,index)
				if (idx!=index) {
					tempData.push(item)
				}
			})
			data.pageParam[0].details.imgs=tempData
		},
	},
})
vm.$data === data;
vm.$el === document.getElementById('uicut-app');









$.fn.jPicker.defaults.images.clientPath='images/';
	// 焦点颜色
	$('#colorChoose1').jPicker({window:{expandable:true,title:'请选择颜色'}},
		function(color, context){
			var hex = color.val('hex');
			$(".dot-on").css({backgroundColor: hex && '#' + hex || 'transparent'});
			console.log(hex)
			data.pageParam[0].dotColorSelect="#"+hex
		}
		);
	// 背景颜色
	$('#colorChoose2').jPicker({window:{expandable:true,title:'请选择颜色'}},
		function(color, context){
			var hex = color.val('hex');
			$(".dot").eq(1).css({backgroundColor: hex && '#' + hex || 'transparent'});
			console.log(hex)
			data.pageParam[0].dotColor="#"+hex
		}
		);



	// 是否显示轮播点
	$("body").on('click', '.uc-radio', function(event) {
		event.preventDefault();
		$(this).addClass('on').siblings().removeClass('on')
		if ($(this).index()==0) {
			data.pageParam[0].details.dotShow=true
		}else{
			data.pageParam[0].details.dotShow=false
		}
	});

	// banner框高度
	$("body").on('change', '.js_ration', function(event) {
		event.preventDefault();
		var val=$(this).val();
		$(this).siblings('.ration-value').text(val+'%');
		data.pageParam[0].details.boxHeight=$(this).width()*val/100
	});

	// 上传图片
	$("body").on('change', '.btnInputUploadImg', function(event) {
		// event.preventDefault();
		console.log(33)
		let file = event.target.files[0];
		let freader = new FileReader();
		if (file.size > 1024 * 1024 * 1) {
			alert('上传的图片大小超过1M');
			file.value = '';
			return
		}
		this.imgName = file.name;
		this.imgFile = event.target.files;
            freader.readAsDataURL(file);//读取照片
            let _this = this;
            freader.onload = (e) => {//读取成功
            	console.log('本地图片源:')
            	console.log(e)
            	console.log(freader)
               //  event.srcElement.value = "" // 清除路径
               // this.picValue = freader.result;
               // if(from&&from=='editor'){
               //  _this.insertPicture();
               // }
               // https://www.showdoc.cc/304514022975829?page_id=1741155138234408



               let url='https://www.showdoc.cc/304514022975829?page_id='+'1741155138234408'
               let postData={
					// id:999,
					// data:data.pageParam
				};
				$.ajax({
					type: 'POST',
					url: url,
					dataType: 'json',        //jsonp
					data:postData,
					cache: false,
					header: {
						'Content-Type': 'application/json'
					},
					error: function(){
						console.log('提交失败！');
						return false;
					},
					success:function(res){
						console.log('res: ',res)
					}
				});


			};
		});

	$(".btns-submit").click(function(event) {
		console.log(33)

		let url='https://api.juanpao.com/shop/design/edit'
		let postData={
			id:999,
			data:data.pageParam
		};
		$.ajax({
			type: 'POST',
			url: url,
			dataType: 'json',        //jsonp
			data:postData,
			cache: false,
			header: {
				'Content-Type': 'application/json'
			},
			error: function(){
				console.log('提交失败！');
				return false;
			},
			success:function(res){
				console.log('res: ',res)
				// if(res.code==0){
				//   console.log('成功')
				// }else{
				//   console.log('失败: ',res.msg)
				// }
			}
		});

		return false;
	});



	$(".scroll").niceScroll({cursorcolor:"#eee",cursorwidth:"8px",cursorborder:""});