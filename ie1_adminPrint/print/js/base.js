$(document).ready(function(){
	$('.table_ul_top p span').click(function(){
		if ($(this).parent('p').parent('.table_ul_top').siblings('.table_ul_bottom').css('display')=='none') {
			$(this).parent('p').parent('.table_ul_top').parent('li').css({'background':'#00cea7','padding-bottom':'20px'});
			$(this).parent('p').parent('.table_ul_top').parent('li').siblings('li').css({'background':'#f9f9f9','padding-bottom':'0'});
			$(this).html('收起');
			$(this).parent('p').parent('.table_ul_top').parent('li').siblings('li').children('.table_ul_top').children('p').children('span').html('展开');
			$('.table_ul_bottom').slideUp(200);
			$(this).parent('p').parent('.table_ul_top').siblings('.table_ul_bottom').slideDown(200);
		}else{
			$(this).html('展开');
			$(this).parent('p').parent('.table_ul_top').parent('li').css({'background':'#f9f9f9','padding-bottom':'0'});
			$(this).parent('p').parent('.table_ul_top').siblings('.table_ul_bottom').slideUp(200);
		}
		
	})

	$('.info_top .btn').click(function(){
		if ($('.btn_mask_ul').css('display')=='none') {
			$('.btn_mask_ul').slideDown(200);
		}else{
			$('.btn_mask_ul').slideUp(200);
		}
	})
});