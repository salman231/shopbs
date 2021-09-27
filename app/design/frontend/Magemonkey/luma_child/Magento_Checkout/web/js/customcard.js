define(['jquery'], function($){
    "use strict";
    return function customcards()
    {
        $(window).load(function(){
	        setTimeout(function(){
	        console.log("sdfsdfsdf");
	        var paytext = jQuery("#checkout-payment-method-load .payment-method._active .payment-method-title.field.choice label span").text();
				if(paytext.includes('No Payment') == true){
				  	console.log(paytext.includes('No Payment'));
					var updatebtn = jQuery("#checkout-payment-method-load .payment-method-billing-address .checkout-billing-address fieldset.fieldset .actions-toolbar button.action-update span").text();
					if(updatebtn == "Update"){
						jQuery("#checkout-payment-method-load .payment-method-billing-address .checkout-billing-address fieldset.fieldset .actions-toolbar button.action-update").trigger("click");
					}
				}
	        }, 2000);
	        setTimeout(function(){
	       		jQuery(".giftcard-cart-discount").appendTo("#checkout .opc-wrapper");
	        }, 2000);
	        $("#payment_bizfac_pay_option1").click(function(){
				$(".payment-method._active .action-update span").trigger("click");
			});
	    });
    }
});