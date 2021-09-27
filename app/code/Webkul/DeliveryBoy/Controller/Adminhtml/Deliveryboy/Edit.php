<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;
use Webkul\DeliveryBoy\Controller\RegistryConstants;

class Edit extends \Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy
{
    /**
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $deliveryboyId = $this->initCurrentDeliveryboy();
        $isExistingDeliveryboy = (bool)$deliveryboyId;
        if ($isExistingDeliveryboy) {
            try {
                $baseTmpPath = "";
                $target = $this->storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $baseTmpPath;
                $deliveryboyData = [];
                $deliveryboyData["expressdelivery_deliveryboy"] = [];
                $deliveryboy = null;
                $deliveryboy = $this->deliveryboyRepository->getById($deliveryboyId);
                $result = $deliveryboy->getData();
                if (count($result)) {
                    $deliveryboyData["expressdelivery_deliveryboy"] = $result;
                    $deliveryboyData["expressdelivery_deliveryboy"]["image"] = [];
                    $deliveryboyData["expressdelivery_deliveryboy"]["image"][0] = [];
                    $deliveryboyData["expressdelivery_deliveryboy"]["image"][0]["url"] = $target . $result["image"];
                    $deliveryboyData["expressdelivery_deliveryboy"]["image"][0]["name"] = $result["image"];
                    $filePath = $this->mediaDirectory->getAbsolutePath($baseTmpPath) . $result["image"];
                    try {
                        if ($this->fileDriver->isFile($filePath)) {
                            $fileStats = $this->fileDriver->stat($filePath);
                            $deliveryboyData["expressdelivery_deliveryboy"]["image"][0]["size"] =
                                empty($fileStats['size']) ? 0 : $fileStats['size'];
                        } else {
                            $deliveryboyData["expressdelivery_deliveryboy"]["image"][0]["size"] = 0;
                        }
                    } catch (\Throwable $t) {
                        $this->logger->debug($t->getMessage());
                    }
                    $deliveryboyData["expressdelivery_deliveryboy"][DeliveryboyInterface::ID] = $deliveryboyId;
                    unset($deliveryboyData["expressdelivery_deliveryboy"]["password"]);
                    $this->_getSession()->setDeliveryboyFormData($deliveryboyData);
                } else {
                    $this->messageManager->addError(__("Requested delivery boy doesn't exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("expressdelivery/deliveryboy/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __("Something went wrong while editing the delivery boy."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("expressdelivery/deliveryboy/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultDeliveryboyTitle($resultPage);
        $resultPage->setActiveMenu("Webkul_DeliveryBoy::deliveryboy");
        if ($isExistingDeliveryboy) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $deliveryboyId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Deliveryboy"));
        }
        return $resultPage;
    }

    /**
     * @return int|null
     */
    protected function initCurrentDeliveryboy()
    {
        $deliveryboyId = (int)$this->getRequest()->getParam("id");
        if ($deliveryboyId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_DELIVERYBOY_ID, $deliveryboyId);
        }
        return $deliveryboyId;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultDeliveryboyTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__("Deliveryboy Image"));
    }
}
