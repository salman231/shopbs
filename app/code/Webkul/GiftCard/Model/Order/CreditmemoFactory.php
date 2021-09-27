<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\GiftCard\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\EntityInterface;
use Magento\Sales\Model\Order\InvoiceFactory;

class CreditmemoFactory extends \Magento\Sales\Model\Order\CreditmemoFactory
{
    public function __construct(
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->messageManager = $messageManager;
        $this->convertor = $convertOrderFactory->create();
        $this->taxConfig = $taxConfig;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
        parent::__construct($convertOrderFactory, $taxConfig, $serializer);
    }
   
    /**
     * Prepare order creditmemo based on order items and requested params
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $data
     * @return Creditmemo
     */
    public function createByOrder(\Magento\Sales\Model\Order $order, array $data = [])
    {
        $totalQty = 0;
        $creditmemo = $this->convertor->toCreditmemo($order);
        $qtys = isset($data['qtys']) ? $data['qtys'] : [];
        $i = 0;
        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canRefundItem($orderItem, $qtys)) {
                continue;
            }
            if ($orderItem->getData()['product_type'] == 'giftcard') {
                $i++;
                if ($i == 1) {
                    $this->messageManager->addWarning(__('We can\'t save the credit memo for giftcard type product.'));
                }
                continue;
            }

            $item = $this->convertor->itemToCreditmemoItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = 1;
                $orderItem->setLockedDoShip(true);
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (double)$qtys[$orderItem->getId()];
                } elseif (empty($qtys)) {
                    $qty = $orderItem->getQtyToRefund();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->initData($creditmemo, $data);

        $creditmemo->collectTotals();
        return $creditmemo;
    }
}
