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

class MassEnable extends \Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection     = $this->filter->getCollection($this->collectionFactory->create());
        $deliveryboysUpdated = 0;
        $coditionArr    = [];
        foreach ($collection->getAllIds() as $deliveryboyId) {
            $currentDeliveryboy = $this->deliveryboyRepository->getById($deliveryboyId);
            $deliveryboyData = $currentDeliveryboy->getData();
            if (count($deliveryboyData)) {
                $condition = "`id`=" . $deliveryboyId;
                array_push($coditionArr, $condition);
                $deliveryboysUpdated++;
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setDeliveryboyData($coditionData, ["status"=>1]);
        if ($deliveryboysUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were enabled.", $deliveryboysUpdated));
        }
        return $resultRedirect->setPath("expressdelivery/deliveryboy/index");
    }
}
