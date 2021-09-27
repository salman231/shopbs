/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
        'jquery',
        'underscore',
        'mage/translate'
    ], function ($, _, $t) {
        "use strict";

        return function (config, element) {
            var hideDetails = $t('HIDE PRODUCTS'),
                showDetails = $t('PREVIEW PRODUCTS');
            $(element).on('click', 'button', function (event) {
                event.preventDefault();
                if ($(this).hasClass('hide-details')) {
                    $(element).next().empty();
                    $(element).find('button.hide-details').html(showDetails).addClass('show-details');
                    $(element).find('button.show-details').removeClass('hide-details');
                } else {
                    $.ajax({
                        url: config.ajaxUrl,
                        showLoader: true,
                        success: function (data) {
                            if (data) {
                                $(element).next().empty().append(data);
                                $(element).find('button.show-details').html(hideDetails).addClass('hide-details');
                                $(element).find('button.hide-details').removeClass('show-details');
                            }
                        }
                    });
                }
            });
        };
    }
);
