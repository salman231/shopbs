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
        'ko',
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/view/customer',
        "mage/translate",
        'mp_productReviewMessage',
        'jquery/file-uploader'
    ], function ($, ko, Component, customerData, customer, $t, mp_productReviewMessage) {
        'use strict';

        return Component.extend(
            {
                initialize: function () {
                    this._super();

                    this.review = customerData.get('review').extend({disposableCustomerData: 'review'});
                },
                nickname: function () {
                    return this.review().nickname || customerData.get('customer')().firstname;
                },

                initObservable: function () {
                    this._super();
                    var self = this,
                        formData = new FormData(),
                        imgMessage = $('.review-field-images .mp-betterproductreviews-message');
                    window.mpbetterproductreviews_uploadedImages = 0;

                    //ajax upload images
                    self.onFileSelectedEvent = function (vm, evt) {
                        var files = evt.target.files;
                        var len = files.length;
                        imgMessage.html('');
                        this.uploadProcessor(formData, files, 0, len, evt, imgMessage);
                    };

                    //ajax submit review form
                    self.ajaxSubmit = function () {
                        var reviewForm = $('#mp-review-form form'),
                            reviewFormData = new FormData(reviewForm[0]),
                            reviewFormImgLoader = $('.review-form-actions .mp_image_loader'),
                            newImageFields = $('.review-field-images .mp-new-image'),
                            recommendProductField = $('.review-field-recommend #recommend_field'),
                            termField = $('.review-field-term #term_field');

                        $('#mp-form-submit-message').html('');
                        if (reviewForm.valid()) {
                            reviewFormImgLoader.show();
                            $.ajax(
                                {
                                    type: "POST",
                                    url: reviewForm.attr('action'),
                                    data: reviewFormData,
                                    processData: false,
                                    contentType: false,
                                    success: function (response) {
                                        newImageFields.remove();
                                        reviewFormImgLoader.hide();
                                        recommendProductField.prop('checked', false);
                                        termField.prop('checked', false);
                                        window.mpbetterproductreviews_uploadedImages = 0;
                                        imgMessage.html('');
                                        if (response.success) {
                                            $('#mp-form-submit-message').html(mp_productReviewMessage.getSuccessMessage(response.responseMessage));
                                        } else {
                                            $('#mp-form-submit-message').html(mp_productReviewMessage.getErrorMessage(response.responseMessage));
                                        }
                                    },
                                    error: function (e) {
                                        $('#mp-form-submit-message').html(e.responseText);
                                    }
                                }
                            );
                        }
                    };
                    this.recommend.subscribe(
                        function (newValue) {
                            self.recommendHidden(+newValue);
                        }
                    );

                    return this;
                },

                recommend: ko.observable(0),
                recommendHidden: ko.observable(0),

                uploadProcessor: function (formData, files, counter, limit, evt, imgMessage) {
                    var self = this;
                    var currentDate = new Date();
                    var imgLoader = $('.review-field-images .loader img'),
                        imgWrapper = $('#mp-review-image-wrapper i');
                    if (counter < limit) {
                        if (window.mpbetterproductreviews_limitUpload === false || window.mpbetterproductreviews_uploadedImages < window.mpbetterproductreviews_limitUpload) {
                            var file = files[counter];

                            imgLoader.show();
                            imgWrapper.hide();
                            if (formData) {
                                formData.delete('image');
                                formData.delete('position');
                                formData.delete('value_id');
                                formData.append('image', file);
                                formData.append('position', $('#mp-image-place-holder').prev().find('input.position').val());
                                formData.append('value_id', currentDate.getTime());
                                $.ajax(
                                    {
                                        type: "POST",
                                        url: window.mpbetterproductreviews_ajaxUrl,
                                        data: formData,
                                        processData: false,
                                        contentType: false,
                                        success:
                                            function (response) {
                                                imgLoader.hide();
                                                imgMessage.html('');
                                                $('#mp-review-image-wrapper').removeClass('no-before');
                                                imgWrapper.show();
                                                if (response.success) {
                                                    $('#mp-image-place-holder').before($(response.review_images).html());
                                                    window.mpbetterproductreviews_uploadedImages++;
                                                } else {
                                                    imgMessage.html('<strong class="mp-danger">' + response.error + '</strong>');
                                                }
                                                counter++;
                                                self.uploadProcessor(formData, files, counter, limit, evt, imgMessage);
                                            }
                                    }
                                );
                            }
                        } else {
                            var textImg = (window.mpbetterproductreviews_limitUpload === 1) ? ' image' : ' images';
                            var warningMessage = $t('You can only upload limit of ' + window.mpbetterproductreviews_limitUpload + textImg);
                            imgMessage.html('<strong class="mp-danger">' + warningMessage + '</strong>');
                            $(evt.target).val('');
                        }
                    } else {
                        $(evt.target).val('');
                    }
                }
            }
        );
    }
);

