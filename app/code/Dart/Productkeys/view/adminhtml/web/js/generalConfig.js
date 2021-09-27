require([
	'jquery'
], function($) {
	'use strict',
	
	$(document).ready(function() {
		var timeout = setInterval(function() {
			var ele = $('.fieldset-wrapper[data-index="product-keys"]');
			if (ele.length > 0) {
				clearInterval(timeout);
				ele.on("click", function() {
					gnrlConfigDepends();
				});
			}
		}, 1000);
	});
	
	function gnrlConfigDepends() {
		var gnrlConfig = $("select[name='product[productkey_overwritegnrlconfig]']");
		if (gnrlConfig.val() == 0) {
			$(".generalconfig_attributes").hide();
		}
		gnrlConfig.change(function() {
			if ($(this).val() == 0) {
				$(".generalconfig_attributes").hide();
			} else {
				$(".generalconfig_attributes").show();
			}
		});
	}
});







