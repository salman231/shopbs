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

class Filter extends \Webkul\Rmasystem\Controller\Index\Filter
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $this->_objectManager->create(
            'Magento\Framework\Session\SessionManager'
        )->unsGuestFilterData();

        $data = $this->getRequest()->getPost();

        $this->_objectManager->create(
            'Magento\Framework\Session\SessionManager'
        )->setGuestFilterData($data);
        return $resultPage;
    }
}
