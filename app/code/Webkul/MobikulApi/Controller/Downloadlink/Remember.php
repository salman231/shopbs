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

namespace Webkul\MobikulApi\Controller\Downloadlink;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class Remember extends \Magento\Framework\App\Action\Action
{
    const COOKIE_NAME = "downloadlink";
    private $_cookieManager;
    private $_sessionManager;
    private $_cookieMetadataFactory;

    public function __construct(
        CookieManagerInterface $cookieManager,
        SessionManagerInterface $sessionManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_sessionManager = $sessionManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(86400)
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());
        $this->_cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            "remember",
            $metadata
        );
    }
}
