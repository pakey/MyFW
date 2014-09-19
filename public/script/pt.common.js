$.pt = $.pt || {};
(function ($) {
	$.pt.init = function () {
	};
})($);
$(function () {
	if ($('#fixNav').length) var top1=$('#fixNav').parent().offset().top
	if ($('.nav_menu .subnav').length) var top2=$('.nav_menu .subnav').offset().top;
	console.log(top1);
	console.log(top2);
	$(window).scroll(function () {
		if ($(window).scrollTop() >= 200) {
			$("#backTop").fadeIn(500);
		}else {
			$("#backTop").fadeOut(500);
		}
		if (top1!=undefined){
			if ($(window).scrollTop()>top1){
				$('#fixNav').addClass('fixtop');
			}else{
				$('#fixNav').removeClass('fixtop');
			}
		}
		if (top2!=undefined){
			if ($(window).scrollTop()>top2){
				$('#window-head').fadeIn(500);
			}else{
				$('#window-head').fadeOut(500);
			}
		}
	});
	//当点击跳转链接后，回到页面顶部位置
	$("#backTop").click(function () {
		$('body,html').animate({scrollTop: 0}, 500);
		return false;
	});
});

