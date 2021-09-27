<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Controller\Report;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ProductRepository;
use Webkul\Mpreportsystem\Block\Mpreport;

class Productfilter extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var Webkul\Mpreportsystem\Block\Mpreport
     */
    protected $_mpreportBlock;

    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_modelUrl;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Mpreport $mpreport
     * @param \Magento\Customer\Model\Url $modelUrl
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Mpreport $mpreport,
        \Magento\Customer\Model\Url $modelUrl
    ) {
        $this->_customerSession = $customerSession;
        $this->_modelUrl = $modelUrl;
        $this->_jsonHelper = $jsonHelper;
        $this->_mpreportBlock = $mpreport;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_modelUrl->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Seller product sale graph.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $returnData = $this->_mpreportBlock->getProductSales($params);
        return $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($returnData)
        );
    }
}
