/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    "./column",
    "jquery",
    "mage/template",
    "text!Webkul_MobikulCore/template/grid/cells/preview.html",
    "underscore",
    "Magento_Ui/js/modal/modal",
    "mage/translate"
], function (Column, $, mageTemplate, thumbnailPreviewTemplate, _) {
    "use strict";

    return Column.extend({
        defaults: {
            bodyTmpl: "ui/grid/cells/thumbnail",
            fieldClass: {
                "data-grid-thumbnail-cell": true
            }
        },

        /**
         * Get image source data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getSrc: function (row) {
            return row[this.index + "_src"];
        },

        /**
         * Get original image source data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getOrigSrc: function (row) {
            return row[this.index + "_orig_src"];
        },

        /**
         * Get link data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getLink: function (row) {
            return row[this.index + "_link"];
        },

        /**
         * Get alternative text data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getAlt: function (row) {
            return _.escape(row[this.index + "_alt"]);
        },

        /**
         * Check if preview available.
         *
         * @returns {Boolean}
         */
        isPreviewAvailable: function () {
            return this["has_preview"] || false;
        },

        /**
         * Build preview.
         *
         * @param {Object} row
         */
        preview: function (row) {
            var srcStr = this.getSrc(row);
            var data = srcStr.split(",");
            var modalHtml = "";
            if (data instanceof Array && data.length > 1) {
                data.forEach(element => {
                    modalHtml += mageTemplate(
                        thumbnailPreviewTemplate,
                        {
                            src: element,
                            alt: element
                        }
                    );
                });
            } else {
                var modalHtml = mageTemplate(
                    thumbnailPreviewTemplate,
                    {
                        src: srcStr,
                        alt: srcStr
                    }
                );
            }
            var previewPopup = $("<div/>").html(modalHtml);
            previewPopup.modal(
                {
                    innerScroll: true,
                    modalClass: "_image-box",
                    buttons: []
                }
            ).trigger("openModal");
        },

        /**
         * Get field handler per row.
         *
         * @param {Object} row
         * @returns {Function}
         */
        getFieldHandler: function (row) {
            if (this.isPreviewAvailable()) {
                return this.preview.bind(this, row);
            }
        }
    });
});
