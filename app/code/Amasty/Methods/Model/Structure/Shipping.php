<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */


namespace Amasty\Methods\Model\Structure;

class Shipping extends \Amasty\Methods\Model\Structure
{
    public function __construct(
        \Amasty\Methods\Model\ShippingFactory $objectFactory,
        \Amasty\Methods\Model\ResourceModel\Shipping\CollectionFactory $objectCollectionFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Amasty\Methods\Helper\Data $helper,
        $objectCode = 'shipping_method'
    ) {
        $this->_objectCode = $objectCode;
        $this->_objectCollectionFactory = $objectCollectionFactory;
        $this->_objectFactory = $objectFactory;

        parent::__construct(
            $resourceConfig,
            $cacheTypeList,
            $cacheState,
            $helper
        );
    }
}
