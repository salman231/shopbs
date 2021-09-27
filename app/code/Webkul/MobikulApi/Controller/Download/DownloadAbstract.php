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

namespace Webkul\MobikulApi\Controller\Download;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

/**
 * DownloadAbstract
 */
abstract class DownloadAbstract extends \Magento\Framework\App\Action\Action implements
    \Magento\Framework\App\CsrfAwareActionInterface
{

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
            return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
