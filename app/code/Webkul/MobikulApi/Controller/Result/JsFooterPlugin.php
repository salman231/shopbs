<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApi\Controller\Result;

use Magento\Framework\App\Response\Http;

/**
 * Plugin for putting all js to footer.
 */
class JsFooterPlugin extends \Magento\Theme\Controller\Result\JsFooterPlugin
{
    /**
     * Put all javascript to footer before sending the response.
     *
     * @param Http $subject
     * @return void
     */
    public function beforeSendResponse(Http $subject)
    {
        $content = $subject->getContent();
        if ($content) {
            parent::beforeSendResponse($subject);
        }
    }
}
