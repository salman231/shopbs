/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'uiComponent',
    'mage/validation',
    'mage/template',
    'ko',
    'Magento_Ui/js/modal/alert',
    'text!Webkul_Rmasystem/template/preview.html',
    'Magento_Ui/js/modal/modal'
], function (
    $,
    Component,
    validation,
    mageTemplate,
    ko,
    alert,
    thumbnailPreviewTemplate
) {
        'use strict';

        return Component.extend({
            totalImages: ko.observable(0),
            initialize: function () {
                this._super();
                //this._intializeSorting();
                var self = this;

                $('body').off('click', '.wk_rma_add_images').on('click', '.wk_rma_add_images', function (e) {
                    var self = $(this);
                    var modalHtml = mageTemplate(
                        thumbnailPreviewTemplate,
                        {
                            src: self.attr('src'), alt: self.attr('alt'), link: window.rmaData.downloadUrl + 'file_name/' + self.attr('alt'),
                            linkText: $.mage.__('Download')
                        }
                    );
                    var previewPopup = $('<div/>').html(modalHtml);
                    previewPopup.modal({
                        title: $.mage.__('RMA Image'),
                        innerScroll: true,
                        modalClass: '_image-box',
                        buttons: []
                    }).trigger('openModal');
                });
            },
            addImageBlock: function (event) {
                var templateEngine = new ko.nativeTemplateEngine();

                templateEngine.addTemplate = function (templateName, templateMarkup) {
                    document.write(document.getElementById("rma-image-template"));
                };
                var parentDiv = $('#image_block').clone();
                var imageContainer = $('body').find('#image_block').append(document.createElement("DIV"));
                ko.renderTemplate('rmaImageTemplate', this, { templateEngine: templateEngine }, imageContainer, "replaceNode");
                $(parentDiv).insertAfter('.image-field button');

            },
            deleteImage: function (data, event) {
                $(event.currentTarget).parent('div').remove();
                if ($(event.currentTarget).siblings('img').length > 0) {
                    this.totalImages(this.totalImages() - 1);
                }
            },
            fileSelect: function (elemet, event) {
                this.totalImages(this.totalImages() + 1);
                var self = this;
                var files = event.target.files;// FileList object
                // Loop through the FileList and render image files as thumbnails.
                for (var i = 0, f; f = files[i]; i++) {
                    // Only process image files.
                    if (!f.type.match('image.*')) {
                        alert({
                            content: $.mage.__('This file type is not supported, allowed extensions are: png, jpg, jpeg.'),

                        });
                        continue;
                    }
                    var url = window.URL || window.webkitURL;
                    var image = new Image();
                    image.onload = function () {
                        $(event.currentTarget).parent('div').find('.wk-default-block').remove();
                        $(event.currentTarget).parent('div').append(image);
                    }
                    image.onerror = function () {
                        self.totalImages(self.totalImages() - 1);
                        $(event.currentTarget).parent('div').remove();
                        $('#wk_rma_label_image').trigger('click');
                        alert({
                            content: $.mage.__('This file type is not supported, allowed extensions are: png, jpg, jpeg.'),

                        });
                    };
                    image.src = url.createObjectURL(f);
                }
            },
        });
    });
