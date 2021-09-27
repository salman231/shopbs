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
        'uiComponent',
        'jquery',
        'ko',
        'MSP_ReCaptcha/js/registry'
    ],
    function (Component, $, ko, registry, undefined) {

        var mixin = {
            /**
             * Initialize reCaptcha after first rendering
             */
            initCaptcha: function () {
                var me = this,
                    $parentForm,
                    $wrapper,
                    $reCaptcha,
                    widgetId,
                    listeners;

                if (this.captchaInitialized) {
                    return;
                }

                this.captchaInitialized = true;

                /*
                 * Workaround for data-bind issue:
                 * We cannot use data-bind to link a dynamic id to our component
                 * See: https://stackoverflow.com/questions/46657573/recaptcha-the-bind-parameter-must-be-an-element-or-id
                 *
                 * We create a wrapper element with a wrapping id and we inject the real ID with jQuery.
                 * In this way we have no data-bind attribute at all in our reCaptcha div
                 */
                $wrapper = $('#' + this.getReCaptchaId() + '-wrapper');
                $reCaptcha = $wrapper.find('.g-recaptcha');
                $reCaptcha.attr('id', this.getReCaptchaId());

                $parentForm = $wrapper.parents('form');
                me = this;

                // eslint-disable-next-line no-undef
                widgetId = grecaptcha.render(this.getReCaptchaId(), {
                    'sitekey': this.settings.siteKey,
                    'theme': this.settings.theme,
                    'size': this.settings.size,
                    'badge': this.badge ? this.badge : this.settings.badge,
                    'callback': function (token) { // jscs:ignore jsDoc
                        me.reCaptchaCallback(token);
                        me.validateReCaptcha(true);
                    },
                    'expired-callback': function () {
                        me.validateReCaptcha(false);
                    }
                });

                if (this.settings.size === 'invisible' && $parentForm.length > 0) {
                    if($parentForm.attr('id') === 'review-form'){
                        $parentForm.find('.submit').on('click', function (event) {
                            grecaptcha.execute(widgetId);
                            event.preventDefault(event);
                            event.stopImmediatePropagation();
                        });
                    }

                    $parentForm.submit(function (event) {
                        if (!me.tokenField.value) {
                            // eslint-disable-next-line no-undef
                            grecaptcha.execute(widgetId);
                            event.preventDefault(event);
                            event.stopImmediatePropagation();
                        }
                    });

                    // Move our (last) handler topmost. We need this to avoid submit bindings with ko.
                    listeners = $._data($parentForm[0], 'events').submit;
                    listeners.unshift(listeners.pop());

                    // Create a virtual token field
                    this.tokenField = $('<input type="text" name="token" style="display: none" />')[0];
                    this.$parentForm = $parentForm;
                    $parentForm.append(this.tokenField);
                } else {
                    this.tokenField = null;
                }

                registry.ids.push(this.getReCaptchaId());
                registry.captchaList.push(widgetId);
                registry.tokenFields.push(this.tokenField);

            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
