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

use Webkul\DeliveryBoy\Controller\RegistryConstants;

class Delete extends \Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Deliveryboy could not be deleted."));
            return $resultRedirect->setPath("expressdelivery/deliveryboy/index");
        }
        $deliveryboyId = $this->initCurrentDeliveryboy();
        if (!empty($deliveryboyId)) {
            try {
                $this->deliveryboyRepository->deleteById($deliveryboyId);
                $this->messageManager->addSuccess(__("Deliveryboy has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("expressdelivery/deliveryboy/index");
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
}
