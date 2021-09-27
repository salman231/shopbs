define([
    "jquery",
    'Amasty_Customform/js/form-builder',
    'Amasty_Customform/js/form-render',
    'Amasty_Customform/js/am-google-map'
], function ($, customFormBuilder) {
    'use strict';

    $.widget('mage.amformData', {
        _create: function () {
            var self = this;

            self.options.formWrapper = $('[data-amcform-js="form-builder-wrap"]');
            self.options.formEditor = $('[data-amcform-js="fb-editor"]');
            self.options.addNewPage = $('[data-amcform-js="add-new-page"]');
            self.options.deletePage = '[data-amcform-js="delete-tab"]';

            self.generateEventsListener();

            self.options.formEditor.customFormBuilder(self.options);
        },

        /**
         * Set events listeners on form builder
         */

        generateEventsListener: function () {
            var self = this,
                tabsWrap = '[data-amcform-js="tabs-wrap"]',
                tab = '[data-amcform-role="page-link"]',
                tabForKeyboard = tab + ', [data-amcform-role="page"], [data-amcform-js="add-new-page"]',
                formJson = '[name="form_json"]',
                keyCodes = [$.ui.keyCode.RIGHT, $.ui.keyCode.DOWN, $.ui.keyCode.LEFT, $.ui.keyCode.UP];

            // jQ UI tabs
            self.options.formWrapper.tabs({
                beforeActivate: function (event, ui) {
                    if (ui.newPanel.selector === "#new-page") {
                        return false;
                    }
                }
            });

            // Save form config
            $('#edit_form').on('beforeSubmit', function (event, submitData, handlerName) {
                if (['saveAndContinueEdit', 'save'].indexOf(handlerName) != -1) {
                    self.options.formEditor.customFormBuilder('generateSaveEvent', event);
                }
            });

            // Switch between page tabs
            $(tabsWrap).on('click', tab, function () {
                self.options.formEditor.customFormBuilder('navigatePageForm', this);
            });

            // Switch between tpage tabs via keyboard
            $(tabsWrap).on('keydown', tabForKeyboard, function (event) {
                if (keyCodes.indexOf(event.keyCode) != -1) {
                    self.options.formEditor.customFormBuilder('navigateKeyboard', this);
                }
            });

            // Delete page
            $(tabsWrap).on('click', self.options.deletePage, function (e) {
                self.options.formEditor.customFormBuilder('deleteCurentPage', this, e);
            });

            // Add new page
            self.options.addNewPage.on('click', function () {
                $(formJson).val('[]');
                self.options.formEditor.customFormBuilder('createNewPage');
            });
        }
    });

    return $.mage.amformData;
});
