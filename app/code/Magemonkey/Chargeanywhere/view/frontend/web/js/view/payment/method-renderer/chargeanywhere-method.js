/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form'
    ],
    function (
        $,
        Component
        )  {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magemonkey_Chargeanywhere/payment/chargeanywhere'
            },
            context: function() {
                return this;
            },

            getCode: function() {
                return 'chargeanywhere';
            },

            isActive: function() {
                return true;
            }

           
        });
    }
);
