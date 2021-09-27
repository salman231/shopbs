require(['jquery'],function ($) {
    $(document).ready(function () {
        $(".cart-discount").hide();
        $(".continue.primary").click(function () {
            giftCartDiscountShow();
        });
        giftCartDiscountShow();
        $(".opc-progress-bar-item").click(function () {
            giftCartDiscountShow();
        });
        function giftCartDiscountShow()
        {
        var myVar = setInterval(function (event) {
            if ($(".checkout-payment-method").attr('style')=='display: list-item;') {
                myStopFunction(myVar);
                $(".cart-discount").show();
            }
            }, 300);
        }
        function myStopFunction(myVar)
        {
            clearInterval(myVar);
        }
    });
});
