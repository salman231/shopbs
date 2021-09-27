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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Orders;

use Magento\Framework\Exception\LocalizedException;

class SaveNewComment extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $wholeData = $this->getRequest()->getParams();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $deliveryboyComment = $objectManager->create(\Webkul\DeliveryBoy\Model\CommentFactory::class);
        $date = $objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $resultJsonFactory = $objectManager->create(\Magento\Framework\Controller\Result\JsonFactory::class);
        try {
            $comment = $wholeData["comment"] ?? "";
            $senderId = $wholeData["senderId"] ?? 0;
            $incrementId = $wholeData["incrementId"] ?? "";
            $isDeliveryboy = $wholeData["isDeliveryboy"] ?? false;
            $deliveryboyOrderId = $wholeData["deliveryboyOrderId"] ?? 0;

            if ($comment == "") {
                throw new LocalizedException(__("Comment field is required."));
            }
            if (str_word_count($comment < 5)) {
                throw new LocalizedException(__("Comment should be atleast 5 words."));
            }

            if ($senderId == 0) {
                $name = "Admin";
            }

            $deliveryboyComment->create()->setComment($comment)
                ->setSenderId($senderId)
                ->setIsDeliveryboy($isDeliveryboy)
                ->setOrderIncrementId($incrementId)
                ->setDeliveryboyOrderId($deliveryboyOrderId)
                ->setCommentedBy($name)
                ->setCreatedAt($date->gmtDate())
                ->save();

            $result = $resultJsonFactory->create();
            return $result->setData(1);
        } catch (\Throwable $e) {
            $result = $resultJsonFactory->create();
            return $result->setData(0);
        }
    }
}
