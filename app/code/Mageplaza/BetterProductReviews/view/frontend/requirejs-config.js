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

var config = {
    config: {
        mixins: {
            'MSP_ReCaptcha/js/reCaptcha': {
                'Mageplaza_BetterProductReviews/js/reCaptcha-mixins': true
            }
        }
    },

    paths: {
        review_list: 'Mageplaza_BetterProductReviews/js/view/review-list',
        mp_productReviewAjaxSort: 'Mageplaza_BetterProductReviews/js/view/ajax-sort',
        mp_productReviewMessage: 'Mageplaza_BetterProductReviews/js/message',
        mp_productReviewSlider: 'Mageplaza_Core/js/owl.carousel.min',
        mp_productReviewPopup: 'Mageplaza_Core/js/jquery.magnific-popup.min'
    },
    shim: {
        mp_productReviewSlider: ['jquery', 'jquery/ui']
    }
};
