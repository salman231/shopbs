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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;

class AddComment extends AbstractDeliveryboy
{
    const COMMENTOR_ADMIN = "Admin";

    /**
     * Current user name
     *
     * @var string
     */
    protected $name;

    /**
     * @return \Magento\Framework\Conroller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->validateRequestData();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if (empty($this->comment)) {
                throw new LocalizedException(__("Comment field is required."));
            }
            if (str_word_count($this->comment < 5)) {
                throw new LocalizedException(__("Comment should be atleast 5 words."));
            }
            $this->order = $this->orderFactory->create()->loadByIncrementId($this->incrementId);
            if (!$this->order->getId()) {
                throw new LocalizedException(__("Invalid order."));
            }
            $deliveryboyOrder = $this->deliveryboyOrderResourceCollection->create()
                ->addFieldToFilter('id', $this->deliveryboyOrderId)
                ->getFirstItem();
            if (!($deliveryboyOrder->getId() > 0)) {
                throw new LocalizedException(__("Invalid deliveryboy order."));
            }
            $this->getName();
            $deliveryboyCommentModel = $this->deliveryboyComment->create()
                ->setComment($this->comment)
                ->setSenderId($this->senderId)
                ->setIsDeliveryboy($this->isDeliveryboy)
                ->setOrderIncrementId($this->incrementId)
                ->setDeliveryboyOrderId($this->deliveryboyOrderId)
                ->setCommentedBy($this->name)
                ->setCreatedAt($this->date->gmtDate())
                ->save();
            $this->returnArray['comment'] = $this->getCommentData($deliveryboyCommentModel);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Comment added successfully.");
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->comment = $this->wholeData["comment"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->senderId = $this->wholeData["senderId"] ?? 0;
            $this->incrementId = $this->wholeData["incrementId"] ?? "";
            $this->deliveryboyOrderId = $this->wholeData["deliveryboyOrderId"] ?? 0;
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
            $this->isDeliveryboy = $this->wholeData["isDeliveryboy"] ?? false;
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
        if (empty($this->comment)) {
            throw new LocalizedException(__("Comment field is required."));
        }
        if (str_word_count($this->comment < 5)) {
            throw new LocalizedException(__("Comment should be at least 5 words."));
        }
    }

    /**
     * @return void
     */
    protected function getName()
    {
        if ($this->isDeliveryboy()) {
            $deliveryBoy = $this->deliveryboyResourceCollection->create()
                ->addFieldToSelect("name")
                ->addFieldToSelect("id")
                ->addFieldToFilter("id", $this->senderId)
                ->getFirstItem();
            $this->name = $deliveryBoy->getName();
        } elseif ($this->isAdmin()) {
            $this->name = self::COMMENTOR_ADMIN;
        }
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\Comment $comment
     * @return array
     */
    protected function getCommentData(\Webkul\DeliveryBoy\Model\Comment $comment): array
    {
        $commentArray['comment'] = $comment->getComment();
        $commentArray["createdAt"] = $this->timezone
            ->date(new \DateTime($comment->getCreatedAt()))->format("M d, Y h:i:s A");
        $commentArray["commentedBy"] = $comment->getCommentedBy();

        return $commentArray;
    }

    /**
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }

    /**
     * @return bool
     */
    protected function isDeliveryboy(): bool
    {
        return $this->isDeliveryboy && ($this->senderId > 0);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function validateRequestData()
    {
        if (!($this->isDeliveryboy() || $this->isAdmin())) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }
}
