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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Controller\Adminhtml\Deal;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\DailyDeal\Controller\Adminhtml\Deal;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class Save
 * @package Mageplaza\DailyDeal\Controller\Adminhtml\Deal
 */
class Save extends Deal
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param DealFactory $dealFactory
     * @param Registry $coreRegistry
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        DealFactory $dealFactory,
        Registry $coreRegistry,
        HelperData $helperData
    ) {
        $this->_helperData = $helperData;

        parent::__construct($context, $dealFactory, $coreRegistry);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('deal')) {
            $deal              = $this->_initDeal();
            $sku               = $data['product_sku'];
            $dealQty           = (int) $data['deal_qty'];
            $data['date_from'] .= ' ' . $data['time_from'][0] . ':' .
                $data['time_from'][1] . ':' . $data['time_from'][2];
            $data['date_to']   .= ' ' . $data['time_to'][0] . ':' . $data['time_to'][1] . ':' . $data['time_to'][2];

            if ($data['store_ids'] === '') {
                $data['store_ids'] = 0;
            }

            if (strtotime($data['date_from']) > strtotime($data['date_to'])) {
                $this->messageManager->addErrorMessage(
                    __('End on must be greater than start on')
                );
                $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                return $resultRedirect;
            }

            try {
                $dealCollection = $this->_dealFactory->create()->getCollection()
                    ->addFieldToSelect('product_id')
                    ->addFieldToFilter('product_id', ['eq' => $data['product_id']]);

                if ($dealCollection->getSize() === 0 || $deal->getId()) {
                    if ($this->_helperData->getProductQty($sku) >= $dealQty) {
                        $deal->addData($data)->save();
                        $this->messageManager->addSuccessMessage(__('The Deal has been saved.'));

                        if ($this->getRequest()->getParam('back')) {
                            $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                            return $resultRedirect;
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__('Deal qty must be less than or equal to product qty'));
                        $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                        return $resultRedirect;
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Already set Deal for this product.'));
                    $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                    return $resultRedirect;
                }
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Deal. %1', $e->getMessage())
                );
                $resultRedirect->setPath('*/*/edit', ['id' => $deal->getId(), '_current' => true]);

                return $resultRedirect;
            }
        }
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
