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
namespace Webkul\Rmasystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\SessionManager;

class Filter extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SessionManager $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     *
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $this->_objectManager->create(
            'Magento\Framework\Session\SessionManager'
        )->unsetFilterData();
        $data = $this->getRequest()->getPost();

        $this->_objectManager->create(
            'Magento\Framework\Session\SessionManager'
        )->setFilterData($data);
        
        return $resultPage;
    }
}
