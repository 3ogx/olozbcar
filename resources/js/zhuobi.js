$(function(){
// tab
 $('.center li').click(function(){
    	$(this).addClass('currli').siblings().removeClass('currli');
    	var index = $(this).index();
    	$('.con .con1').eq(index).removeClass('none').siblings('.con1').addClass('none');
    });
	$('.ul1 li').click(function(){
		 $(this).addClass('blue').siblings().removeClass('blue');
		 var ind = $(this).index();
		 $('.ul2>li').eq(ind).removeClass('none').siblings().addClass('none');
	});
    $('.mo li').click(function(){
    	$(this).addClass('blueli').siblings().removeClass('blueli');
    	var inde = $(this).index();
    	$('.detail li').eq(inde).removeClass('none').siblings().addClass('none');
    });
    $('#dj').click(function(){
    	$('.fenye').slideToggle(500);
    	$('.leftmolud').slideToggle(500);
    });
    
    $('.tDiv tr:not(first)').click(function(){
    	$('.whiteDiv').removeClass('none');
    });

    $('.off').click(function(){
    	$(this).parents('.whiteDiv').addClass('none');
    });


})
