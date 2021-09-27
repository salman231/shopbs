<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\SessionManager;

class Logout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        SessionManager $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * Guest Logout
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $isLoggedIn = $this->session->getGuestData();
        if (isset($isLoggedIn['email'])) {
            $types = [
                'config',
                'layout',
                'block_html',
                'collections',
                'reflection',
                'db_ddl',
                'eav',
                'config_integration',
                'config_integration_api',
                'full_page',
                'translate',
                'config_webservice'
            ];
            foreach ($types as $type) {
                $this->_cacheTypeList->cleanType($type);
            }
            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
            $this->session->unsGuestData();
            return $resultRedirect->setPath("*/guest/login");
        }
        return $resultPage;
    }
}
