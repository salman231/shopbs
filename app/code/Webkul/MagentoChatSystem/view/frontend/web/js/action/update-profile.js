/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'jquery',
        'mage/storage',
        'mage/url',
        'Webkul_MagentoChatSystem/js/model/reply',
    ],
    function ($, storage, urlBuilder, replyModel) {
        'use strict';

        return function (formData, box, showLoader) {
            var serviceUrl,
                payload;
            showLoader(true);
            $.ajax({
                url : urlBuilder.build('chatsystem/profile/upload'),
                data : formData,
                type : 'post',
                enctype: 'multipart/form-data',
                processData: false,  // tell jQuery not to process the data
                contentType: false,   // tell jQuery not to set contentType
                context:$('#profile-form'),
                success: function (response) {
                    if (response.error === false) {
                        replyModel.profileImageUrl(response.image_name);
                    }
                    $(box).hide();
                    showLoader(false);
                },
                error: function (response) {
                    console.log(response.message);
                }
            });
        };
    }
);
