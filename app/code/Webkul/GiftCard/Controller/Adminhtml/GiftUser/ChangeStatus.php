<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\GiftCard\Controller\Adminhtml\GiftUser;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

class ChangeStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\GiftCard\Model\ResourceModel\GiftUser\CollectionFactory
     */
    protected $_giftUserCollection;

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @param \Magento\Backend\App\Action\Context                             $context
     * @param \Webkul\GiftCard\Model\ResourceModel\GiftUser\CollectionFactory $giftUserCollection
     * @param Filter                                                          $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\GiftCard\Model\ResourceModel\GiftUser\CollectionFactory $giftUserCollection,
        Filter $filter
    ) {
    
        parent::__construct($context);
        $this->_giftUserCollection = $giftUserCollection;
        $this->_filter = $filter;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $GiftCardIds = $this->_filter->getCollection($this->_giftUserCollection->create());
        try {
            foreach ($GiftCardIds as $GiftCardId) {
                    $GiftCardId->setIsActive($this->getRequest()->getParam('entity_id'))
                    ->save();
            }
            $this->messageManager->addSuccess(__(
                'Total of %1 record(s) were successfully updated',
                $GiftCardIds->getSize()
            ));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect->setPath('giftcard/giftuser/index');
        return $resultRedirect;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_GiftCard::giftuser_index');
    }
}
