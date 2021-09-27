/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {String} url
     * @param {*} fromPages
     */
    function processReviews(url, fromPages) {
        $.ajax({
            url: url,
            cache: true,
            dataType: 'html',
            showLoader: false,
            loaderContext: $('.product.data.items')
        }).done(function (data) {
            $('#product-review-container').html(data).trigger('contentUpdated');
            $('[data-role="product-review"] .pages a').each(function (index, element) {
                $(element).click(function (event) { //eslint-disable-line max-nested-callbacks
                    processReviews($(element).attr('href'), true);
                    event.preventDefault();
                });
            });
            $(document).trigger('contentUpdated');
        }).complete(function () {
            if (fromPages == true) { //eslint-disable-line eqeqeq
                $('html, body').animate({
                    scrollTop: $('#reviews').offset().top - 50
                }, 300);
            }
        });
    }

    return function (config) {
        var reviewTab = $(config.reviewsTabSelector),
            requiredReviewTabRole = 'tab';

        if (reviewTab.attr('role') === requiredReviewTabRole && reviewTab.hasClass('active')) {
            processReviews(config.productReviewUrl, location.hash === '#reviews');
        } else {
            reviewTab.one('beforeOpen', function () {
                if($('#product-review-container').data('additional') == 0) {
                    processReviews(config.productReviewUrl);
                } else {
                    if ($('.super-attribute-select').val() === "") {
                        processReviews(config.productReviewUrl);
                    }
                }
            });
        }

        $(function () {
            $('.product-info-main .reviews-actions a').click(function (event) {
                var anchor, addReviewBlock;

                event.preventDefault();
                anchor = $(this).attr('href').replace(/^.*?(#|$)/, '');
                addReviewBlock = $('#' + anchor);

                if (addReviewBlock.length) {
                    $('.product.data.items [data-role="content"]').each(function (index) { //eslint-disable-line
                        if (this.id == 'reviews') { //eslint-disable-line eqeqeq
                            $('.product.data.items').tabs('activate', index);
                        }
                    });
                    $('html, body').animate({
                        scrollTop: addReviewBlock.offset().top - 50
                    }, 300);
                }

            });
        });
    };
});
