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
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define(['jquery', 'mage/storage'], function ($, storage) {
    'use strict';

    return function (config) {
        var payLoad = {
                type: config.type,
                entity_id: config.entity_id
            },
            url = config.url;

        return storage.post(url, JSON.stringify(payLoad), false)
            .done(function (response) {
                if (!response.status) {
                    return;
                }

                $.each(response.data, function (index, value) {
                    var location = value.id;

                    $('#mageplaza-autorelated-block-' + location).append(value.content);
                });

                $('body').trigger('contentUpdated');
            });
    };
});