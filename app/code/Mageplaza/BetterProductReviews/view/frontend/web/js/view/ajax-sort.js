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
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'mp_productReviewMessage',
        'accordion'
    ], function ($, mp_productReviewMessage) {
        'use strict';

        return function (config) {
            /**
             * ajax review sorting
             */
            var sortElement = $('#mp-review-sort'),
                defaultSortType = sortElement.val(),
                ajaxSortUrl = config.ajaxSortUrl,
                productId = config.productId,
                writeReviewEnabled = config.writeReviewEnabled,
                writeReviewNotice = config.writeReviewNotice;

            ajaxReviewLoad(defaultSortType, sortElement);
            sortElement.on(
                'change', function () {
                    var el = this;
                    var sortType = $(this).val();
                    ajaxReviewLoad(sortType, el);
                }
            );

            function ajaxReviewLoad(sortType, el) {
                $(el).prop('disabled', true);
                $('.ln_overlay').show();
                $.ajax(
                    {
                        type: "POST",
                        url: ajaxSortUrl,
                        data: {
                            sort_type: sortType,
                            product_id: productId
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#mp-review-items-container').html($(response.review_list).html());
                                $('.ln_overlay').hide();
                                $(el).prop('disabled', false);
                            }
                        },
                        error: function (e) {
                            $('#mp-review-items-container').html(e.responseText);
                        }
                    }
                );
            }

            /**
             * Auto focus to review tab when click reviews link
             */
            focusReviewTab();

            function focusReviewTab() {
                focusReviewTabAction(window.location.href, 'accordion');
                $('.product-info-main .reviews-actions a').click(
                    function (event) {
                        event.preventDefault();
                        focusReviewTabAction($(this).attr('href'), 'tab');
                    }
                );
            }

            /**
             * Auto focus to review tab action type
             * */
            function focusReviewTabAction(href, type) {
                var anchor = href.replace(/^.*?(#|$)/, '');
                if (anchor) {
                    $(".product.data.items [data-role='content']").each(
                        function (index) {
                            if (this.id === 'reviews') {
                                if (type === 'accordion') {
                                    $('.product.data.items').accordion({
                                        "openedState": "active",
                                        "collapsible": true,
                                        "active": index,
                                        "multipleCollapsible": false
                                    });
                                }
                                if (type === 'tab') {
                                    $('.product.data.items').tabs('activate', index);
                                }
                                if (anchor === 'review-form' && writeReviewEnabled) {
                                    $('#mp-review-form').show();
                                    $('#mp-review-write-review-button').prop('disabled', true);
                                    $('html, body').animate(
                                        {
                                            scrollTop: $('#' + anchor).offset().top - 50
                                        }, 300
                                    );
                                }
                                if (anchor === 'reviews' && writeReviewEnabled) {
                                    anchor = anchor.replace('reviews', 'mp-review-list');
                                    $('html, body').animate(
                                        {
                                            scrollTop: $('#' + anchor).offset().top - 50
                                        }, 300
                                    );
                                }
                                if (!writeReviewEnabled) {
                                    var writeMessage = $('#write-review-message');
                                    writeMessage.html(mp_productReviewMessage.getInfoMessage(writeReviewNotice));
                                    $('html, body').animate(
                                        {
                                            scrollTop: writeMessage.offset().top - 50
                                        }, 300
                                    );
                                }
                            }
                        }
                    );
                }
            }
        }
    }
);
