/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

require([
    "jquery",
    'mage/translate'
], function ($, $t) {
    if (!$('#chatsystem_chat_config_admin_image_image').length) {
        $('#chatsystem_chat_config_admin_image').attr('required', 'required');
    }
    $('#chatsystem_chat_config_admin_image_delete').on('change', function() {
        if ($('#chatsystem_chat_config_admin_image_delete').is(":checked")) {
            $('#chatsystem_chat_config_admin_image').attr('required', 'required');
        } else {
            $('#chatsystem_chat_config_admin_image').removeAttr('required');
        }
    })
});
    
