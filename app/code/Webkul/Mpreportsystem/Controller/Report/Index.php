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

/**
 * Webkul Marketplace Mpreportsystem Report Index Controller.
 */
class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelper;

    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_modelUrl;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Magento\Customer\Model\Url $modelUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Customer\Model\Url $modelUrl
    ) {
        $this->_customerSession = $customerSession;
        $this->_modelUrl = $modelUrl;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_marketplaceHelper = $marketplaceHelper;
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
     * Seller Report Dashboard page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_marketplaceHelper;
        $isPartner= $helper->isSeller();
        if ($isPartner==1) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->_resultPageFactory->create();
            if ($helper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpreportsystem_layout2_report_index');
            }
            $resultPage->getConfig()
                ->getTitle()
                ->set(
                    __('Marketplace Seller Report Dashboard')
                );
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
