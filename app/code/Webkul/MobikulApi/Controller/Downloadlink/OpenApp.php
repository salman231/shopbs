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

use Magento\Framework\Controller\ResultFactory;

class OpenApp extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $resultRedirect;

    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $url = $this->helper->getConfigData("mobikul/appdownload/androidlink");
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
}
