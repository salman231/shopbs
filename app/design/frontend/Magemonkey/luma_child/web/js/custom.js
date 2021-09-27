
require([
    'jquery',
    'jquery/ui',

    'mage/translate'
], function ($) {
	$(document).on("click","#payment_bizfac_pay_option1", function() {
		$(".payment-method._active .action-update span").trigger("click");
    });
    $(document).on("click",".mob-account-tab",function(){
	    $(this).toggleClass("Active");
	    $(".mob-acc-sub").slideToggle(); 
	});
	$(document).on("click",".mob-seller-account-tab",function(){
	    $(this).toggleClass("Active");
	    $(".mob-seller-acc-sub").slideToggle(); 
	});
});