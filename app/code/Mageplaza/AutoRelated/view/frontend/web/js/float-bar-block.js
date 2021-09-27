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

define(['jquery', 'arpDefaultBlock'], function ($, defaultBlock) {
    'use strict';

    $.widget('mageplaza.arp_float_bar_block', defaultBlock, {
        options: {
            type: '',
            entityId: ''
        },
        _create: function () {
            this._super();

            var popupEl = $(".arp-popup-block");

            popupEl.mouseover(function () {
                $(this).find('.btn-close-popup').addClass('fa-caret-down');
                $(this).find('.btn-remove-popup').addClass('fa-close');
            });

            popupEl.mouseout(function () {
                $(this).find('.btn-close-popup').removeClass('fa-caret-down');
                $(this).find('.btn-remove-popup').removeClass('fa-close');
            });

            $('.btn-remove-popup').bind('click', function () {
                $(this).parents('div.arp-popup-block').hide();
            });

            $('.btn-close-popup').click(function () {
                if ($(this).hasClass('fa-caret-down')) {
                    $(this).parent().siblings().toggle(500);
                    $(this).toggleClass("fa-caret-down fa-caret-up");
                }
            });

            $(window).scroll(function () {
                $(".popup-content").show("slow");
                $(".btn-close-popup").removeClass("fa-caret-up");
            });
        }
    });

    return $.mageplaza.arp_float_bar_block;
});