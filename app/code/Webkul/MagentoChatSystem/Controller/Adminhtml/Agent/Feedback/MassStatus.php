<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Agent\Feedback;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentRating\CollectionFactory;
use Webkul\MagentoChatSystem\Helper\Data;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatus extends Action
{
    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Webkul\MagentoChatSystem\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Data $helper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }
    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = (int) $this->getRequest()->getParam('status');
        try {
            foreach ($collection as $item) {
                $item->setStatus($status);
                $this->helper->saveObject($item);
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been updated.', $collection->getSize())
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException(
                $e,
                __('Something went wrong while updating the part finder(s) status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/agent/feedback');
    }
}
