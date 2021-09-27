<?php
/**
 * Dart Productkeys Record Delete Controller.
 * @package   Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Controller\Adminhtml\Productkeys;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Dart\Productkeys\Model\ResourceModel\Productkeys\CollectionFactory;
use Dart\Productkeys\Helper\Data;
 
class MassDelete extends Action
{
    /**
     * Massactions filter
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Data $helperData
    ) {

        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $recordDeleted = 0;
        $prdQtyChanged = 0;
        $distinctrecord = [];
        foreach ($collection->getItems() as $record) {
            $Sku = $record->getSku();
            $record->setId($record->getId());
            $record->delete();
            if ($record->getStatus() == 0) {
                $productUpdated = $this->helperData->changeQty($Sku);
                if (!in_array($Sku, $distinctrecord)) {
                    $prdQtyChanged = $prdQtyChanged + $productUpdated;
                    $distinctrecord[] = $Sku;
                }
            }
            $recordDeleted++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));
        if ($prdQtyChanged > 0) {
            $this->messageManager->addSuccess(__('A total of %1 Product(s) Qty has been updated', $prdQtyChanged));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Check Category Map recode delete Permission.
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dart_Productkeys::add_row');
    }
}
