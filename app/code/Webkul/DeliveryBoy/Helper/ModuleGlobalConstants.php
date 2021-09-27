<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Helper;

abstract class ModuleGlobalConstants
{
    const DEFAULT_ADMIN_EMAIL_XML_PATH = "trans_email/ident_general/email";
    const DEFAULT_ADMIN_NAME = 'Admin';
    const DELIVEYBOY_NEW_ACCOUNT_EMAIL_TEMPLATE_ID = 'deliveryboy_new_account';
    const DELIVEYBOY_NEW_REVIEW_EMAIL_TEMPLATE_ID = 'deliveryboy_new_review';
    const DELIVEYBOY_REVIEW_EVALUATION_EMAIL_TEMPLATE_ID = 'deliveryboy_review_evaluation_template';
    const RATING_MAX_VALUE = 5;
    const DEFAULT_RATING_MANAGER_NAME = 'Admin';
}
