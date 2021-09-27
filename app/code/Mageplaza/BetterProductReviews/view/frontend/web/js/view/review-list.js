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
        'mage/translate',
        'underscore'
    ], function ($, $t, _) {
        'use strict';

        return function (config) {
            var reviewPerPage = config.reviewPerPage,
                reviewOffset = config.reviewOffset,
                storeId = config.storeId,
                productId = config.productId,
                ajaxHelpfulUrl = config.ajaxHelpfulUrl,
                ajaxSortUrl = config.ajaxSortUrl;

            /**
             * load more reviews function
             */
            $("#mp-review-list .load-more").on(
                'click', function () {
                    var loadMoreLoader = $('#mp-review-list .mp_image_load_more_loader img');

                    $(this).parent().remove();
                    loadMoreLoader.show();
                    $.ajax(
                        {
                            type: "POST",
                            url: ajaxSortUrl,
                            data: {
                                product_id: productId,
                                review_per_page: reviewPerPage,
                                review_offset: reviewOffset,
                                sort_type: $('#mp-review-sort').val()
                            },
                            success: function (response) {
                                if (response.success) {
                                    loadMoreLoader.remove();
                                    $('#mp-review-items-container').append($(response.review_list).html())
                                }
                            }
                        }
                    );
                }
            );

            /**
             * helpful function
             */
            var helpful = $('.mp-reviews-offset-' + reviewOffset + ' .mp-review-helpful');
            var currentReviewData = JSON.parse(getCookie('mp_betterproductreviews_review_data'));
            helpful.each(
                function () {
                    var self = this;

                    $(self).find('button').on(
                        'click', function () {
                            var el = this;
                            var reviewId = $(el).attr('data-review-id'),
                                helpfulContainer = $(el).parent().parent(),
                                imgLoader = helpfulContainer.find('.loader img'),
                                helpfulVal = helpfulContainer.find('.mp-review-helpful-value'),
                                helpfulLabel = helpfulContainer.find('.mp-review-helpful-label'),
                                helpfulDetails = helpfulContainer.find('.mp-review-helpful-details');
                            var currentReviewObj = {
                                review_id: reviewId,
                                store_id: storeId
                            };

                            if (currentReviewData !== null && isRated(currentReviewObj, currentReviewData)) {
                                helpfulContainer.find('.mp-betterproductreviews-message').html('<strong class="mp-danger">' + $t('You have rated this review already.') + '</strong>');
                            } else {
                                imgLoader.show();
                                $.ajax(
                                    {
                                        type: "POST",
                                        url: ajaxHelpfulUrl,
                                        data: {review_id: reviewId},
                                        success: function (response) {
                                            if (response.success) {
                                                if (helpfulDetails.hasClass('mp-hide')) {
                                                    helpfulDetails.removeClass('mp-hide');
                                                }
                                                if (response.helpful_count > 1) {
                                                    helpfulLabel.html($t('visitors found this helpful'));
                                                } else if (response.helpful_count === 1) {
                                                    helpfulLabel.html($t('visitor found this helpful'));
                                                }
                                                helpfulVal.html(response.helpful_count);
                                                $(el).parent().html('<strong class="mp-success">' + $t('Thanks for your feedback') + '</strong>');
                                                imgLoader.hide();
                                                var reviewData = receiveCookieReviews(reviewId, storeId);
                                                var reviewDataJson = JSON.stringify(reviewData);
                                                document.cookie = 'mp_betterproductreviews_review_data = ' + reviewDataJson;
                                            } else if (!response.success && response.hasOwnProperty(error)) {
                                                helpfulContainer.find('.mp-betterproductreviews-message').html('<strong class="mp-danger">' + response.error + '</strong>');
                                            }
                                        }
                                    }
                                );
                            }
                        }
                    );
                }
            );

            /**
             * get cookie by name
             */
            function getCookie(name) {
                var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
                return v ? v[2] : null;
            }

            /**
             * get stored cookie review objects
             */
            function receiveCookieReviews(reviewId, storeId) {
                var reviewData = {
                    review_id: reviewId,
                    store_id: storeId
                };
                var receivedJsonStr = getCookie('mp_betterproductreviews_review_data');
                var reviewIds = JSON.parse(receivedJsonStr);
                if (reviewIds === null) {
                    reviewIds = [];
                }
                if (!isRated(reviewData, reviewIds)) {
                    reviewIds.push(reviewData);
                }

                return reviewIds;
            }

            /**
             * Check object in array
             *
             * @param   reviewData
             * @param   reviewIds
             * @returns {boolean}
             */
            function isRated(reviewData, reviewIds) {
                return !!_.where(reviewIds, reviewData).length;
            }
        }
    }
);
