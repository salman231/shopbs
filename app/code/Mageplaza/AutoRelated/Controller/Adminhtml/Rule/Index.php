<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Controller\Adminhtml\Rule;

use Magento\Backend\Model\View\Result\Page;
use Mageplaza\AutoRelated\Controller\Adminhtml\Rule;

/**
 * Class Index
 * @package Mageplaza\AutoRelated\Controller\Adminhtml\Rule
 */
class Index extends Rule
{
    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Automatic Related Block Rules'));

        return $resultPage;
    }
}
