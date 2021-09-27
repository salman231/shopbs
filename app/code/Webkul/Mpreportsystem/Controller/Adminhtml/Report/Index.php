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

namespace Webkul\Mpreportsystem\Controller\Adminhtml\Report;

use Webkul\Mpreportsystem\Controller\Adminhtml\Report as Report;
use Magento\Framework\Controller\ResultFactory;

class Index extends Report
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpreportsystem::mpreports');
        $resultPage->getConfig()->getTitle()->prepend(
            __('Marketplace Advance Report System')
        );
        return $resultPage;
    }
}
