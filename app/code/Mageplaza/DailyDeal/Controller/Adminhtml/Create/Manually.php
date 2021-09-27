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
 * @category  Mageplaza
 * @package   Mageplaza_DailyDeal
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Controller\Adminhtml\Create;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\DailyDeal\Cron\Manually as CreateDealManually;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class Manually
 * @package Mageplaza\DailyDeal\Controller\Adminhtml\Create
 */
class Manually extends Action
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var CreateDealManually
     */
    protected $manually;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Manually constructor.
     *
     * @param Action\Context $context
     * @param HelperData $helperData
     * @param CreateDealManually $manually
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        HelperData $helperData,
        CreateDealManually $manually,
        StoreManagerInterface $storeManager
    ) {
        $this->_helperData  = $helperData;
        $this->manually     = $manually;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $storeId        = $this->storeManager->getStore()->getId();

        if ($this->_helperData->isEnabled($storeId)) {
            try {
                $this->manually->process();
                $this->messageManager->addSuccessMessage(__('Success!'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while running manually. Please try again later. %1', $e->getMessage())
                );
            }
        } else {
            $this->messageManager->addNoticeMessage(__('Please enable module!'));
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
