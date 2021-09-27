<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetAdditionalOptions implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_serialize;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $configurationPool;

    /**
     * SetAdditionalOptions constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
    ) {
        $this->_serialize = $serialize;
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_itemOptionFactory = $itemOptionFactory;
        $this->configurationPool = $configurationPool;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getQuoteItem();
        if ($item->getChildren()) {
            $key = count($item->getChildren()) - 1;
            $child = $item->getChildren()[$key];
            $additionalOptions = [];
            $additionalOptions[] = $this->configurationPool->getByProductType($child->getProductType())->getOptions($child);
            $option = [
                'product_id' => $item->getProductId(),
                'code' => 'additional_options',
                'value' => $this->_serialize->serialize($additionalOptions[0])
            ];
            if ($item->getId()) {
                $this->_itemOptionFactory->create()->setData($option)->setItem($item)->save();
            } else {
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $this->_serialize->serialize($additionalOptions[0])
                ]);
            }
        }
    }
}
