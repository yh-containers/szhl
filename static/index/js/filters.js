$(function(){

    // 筛选框
    $('.filter .tab li').click(function (){
    	var index = $(this).index();
    	var maskHeight = $('body')[0].scrollHeight;
        if($('.filter .filter-select').eq(index).is(':visible')){
            $('body').css('height','auto');
            if(index == 0){
                $('.filter .filter-select-city').removeClass('show');;  
            }
            $('.filter .filter-select').eq(index).hide();
            $(this).removeClass('on');
            $('.filter .mask').hide();
        }
        else{
            $(this).addClass('on').siblings().removeClass('on');
            $('body').css({'height':$(window).height()});
            if(index == 3){
                $('.filter .filter-select-province').addClass('show');
                $('.filter .mask').show().addClass('show');
                $('.filter-select-province').css({'height':$(window).height()}); 
            }
            else{
                $('.filter .filter-select-province').hide().removeClass('show');
                $('.filter .mask').hide().removeClass('show');
            }
            $('.filter .filter-select').eq(index).show().siblings('.filter-select').hide();
            $('.filter .mask').show().css('height',maskHeight);
        }
    });
    $('.filter .mask,.filter .back').click(function (){
        $('.mask').hide();
        $('.filter-select').fadeOut();
        $('.filter .tab li').removeClass('on');
        $('body').css('height','auto');
    });
    $('.filter .filter-select li').on('touchstart',function(){
        $(this).addClass('on').siblings('li').removeClass('on');
    });
    $(".root .option").on("click",function(){
       $(this).toggleClass( "checked" );
    });
});
// 详情固定
$(window).on("scroll", function() {
    setTimeout(function() {
        var U = $(window).scrollTop();
        var S = $(".container-floor");
        var R = $(".container-floor .filters"),
        scrollheight = S.offset().top;
        if (U >= (scrollheight - 38) || U >= parseInt($(".goods-floor").offset().top) + parseInt($(".goods-floor").height())) {
            if (!R.hasClass("fixed")) {
                R.addClass("fixed");
            }
            scrollFlag = true
        } else {
            if (R.hasClass("fixed")) {
                R.removeClass("fixed");
            }
        }
    }, 200)
});