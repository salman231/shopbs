var config = {
    map: {
        '*': {
            checkoutjs: 'Magento_Checkout/js/customcard'
        }
    },
    paths: {
        slick: 'js/slick'
    },
    shim: {
        jquery: {
            exports: '$'
        },
        slick: {
            deps: ['jquery']
        }
    }
};
