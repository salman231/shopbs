/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'domReady',
        'mage/cookies'
    ],
    function (ko, domReady) {
        'use strict';
        return {
            initialize: function () {
                var admin = $.mage.cookies.get('admin');
                console, log(admin);
            }
        }
    }
)