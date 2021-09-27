<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Webkul\Marketplace\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Directory\Model\Currency;
/**
 * @api
 * @since 100.0.2
 */
class Price extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.price';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
     private $currency;
    protected $localeCurrency;
    protected $_helper;
    protected $mpSaleslistCollectionFactory;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        Currency $currency = null,
        \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $mpSaleslistCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->_helper = $helper;
        $this->productFactory = $productFactory;
        $this->mpSaleslistCollectionFactory = $mpSaleslistCollectionFactory;
        $this->_helper = $helper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $store = $this->storeManager->getStore(
                $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

            $fieldName = $this->getData('name');
            $finalPrice = 0;
            
            foreach ($dataSource['data']['items'] as & $item) {
                $registerwithtax = $item['seller_vat'];
                $total_tax = (double)filter_var($item['total_tax'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $quantity = $item['magequantity'];
                $productPrice = $item['magepro_price'];
                $commission = $this->_helper->getConfigCommissionRate();
                $mainPrice = ($productPrice - ($productPrice * $commission/100 ));
                
                if($total_tax <= 0 && $registerwithtax == '0'){
                    $finalPrice = $quantity * ($mainPrice - (($mainPrice * $total_tax) /100));
                    
                }elseif($total_tax > 0 && $registerwithtax == '1'){
                    $finalPrice = $item['actual_seller_amount'];
                    
                }else{
                    
                    $finalPrice = $quantity * ($productPrice - (($productPrice * $commission) /100));
                }
              $item[$fieldName] = $currency->toCurrency(sprintf("%f", number_format($finalPrice,2)));
            }
        }

        return $dataSource;
    }
}
