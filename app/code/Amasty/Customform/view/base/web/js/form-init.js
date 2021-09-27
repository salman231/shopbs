define([
    'jquery',
    'jquery/ui',
    'jquery/validate'
], function ($) {
    'use strict';

    $.widget('mage.amFormInit', {
        options: {
            formSelector: '[data-amcform-js="multi-page"]',
            nextButton: '[data-amcform-js="next-button"]',
            prevButton: '[data-amcform-js="prev-button"]',
            pageTitles: '[data-amcform-js="page-titles"]'
        },

        _create: function () {
            var self = this;

            // Call initialization for Ajax events
            $(document).on('amcform-init-multipage', function (e, form) {
                var $form = form.find('[data-amcform-js="multi-page"]');

                self.initialization($form);
            });

            self.initialization(self.element);
        },

        initialization: function (element) {
            var self = this,
                stepsWrap = element.find(self.options.pageTitles);

            $.ui.tabs({
                active: 0
            }, element);

            if (stepsWrap.children().length == 1) {
                stepsWrap.remove();
            }

            $(self.options.pageTitles).children().each(function (index) {
                if (index != 0) {
                    $(this).addClass('-disabled');
                }
            });

            self.nextButtonBehavior();
            self.prevButtonBehavior();

            self.navigatePageForm();
        },

        /**
         *  Behavior for next button: go to the next page
         */

        nextButtonBehavior: function () {
            var self = this;

            $(self.options.nextButton).off().on('click', function () {
                if ($(this).parents('form').valid()) {
                    self.checkFormScrolling(this);
                    var pageId = $(this).parents('.amcform-page-wrap').attr('id'),
                        title = $(this)
                            .parents(self.options.formSelector)
                            .find('[href="#' + pageId + '"]')
                            .parent();
                    title.addClass('-done');
                    title.next().removeClass('-disabled').find('a').trigger('click');
                }
            });
        },

        /**
         *  Behavior for prev button: go to the prev page
         */

        prevButtonBehavior: function () {
            var self = this;

            $(self.options.prevButton).off().on('click', function () {
                self.checkFormScrolling(this);
                var pageId = $(this).parents('.amcform-page-wrap').attr('id'),
                    title = $(this)
                        .parents(self.options.formSelector)
                        .find('[href="#' + pageId + '"]')
                        .parent();
                title.prev().find('a').trigger('click');
            });
        },

        /**
         *  Style behavior of page headers
         */

        navigatePageForm: function () {
            var self = this;

            $(self.options.pageTitles).find('li').off().on('click', function () {
                if ($(this).hasClass('-disabled')) {
                    return
                }

                if ($(this).hasClass('-done')) {
                    $(this).removeClass('-done').addClass('-active');
                }

                if ($(this).prevUntil().hasClass('-active')) {
                    $(this).prevUntil().removeClass('-active').addClass('-done');
                }

                self.updatePages(
                    $(this).parents(self.options.formSelector),
                    $(this).find('a')
                );
            });
        },

        /**
         * Fix default scrolling in the browser
         * @param page - DOM element
         */

        checkFormScrolling: function (page) {
            var form = $(page).parents('[data-amcform-js="multi-page"]'),
                docViewTop = $(window).scrollTop(),
                elemTop = form.offset().top,
                isPopup = form.parents('.amform-popup').length;

            if (docViewTop > elemTop && !isPopup) {
                $('html, body').animate({
                    scrollTop: (form.offset().top) - 50
                },0);
            }
        },

        /**
         * Set class for not current pages for ignore jquery validate
         * @param form
         * @param pageTitle
         */

        updatePages: function (form, pageTitle) {
            form.find('input, textarea').addClass('amcform-hidden-page');
            form
                .find(pageTitle.attr('href') + ' input, ' + pageTitle.attr('href') + ' textarea')
                .removeClass('amcform-hidden-page');
        }
    });

    return $.mage.amformData;
});
