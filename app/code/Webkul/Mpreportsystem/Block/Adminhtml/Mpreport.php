<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Block\Adminhtml;

use Magento\Framework\Controller\Result;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;

class Mpreport extends \Magento\Framework\View\Element\Template
{
    /**
     * url to get chart
     *
     * @var constant
     */
    const GOOGLE_API_URL = 'http://chart.apis.google.com/chart';

    /**
     * @var \Webkul\Mpreportsystem\Helper\Data
     */
    protected $_helperdata;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var sales Collection
     */
    protected $_salesCollection;

    /**
     * @var Webkul\Marketplace\Model\SellerFactory
     */
    protected $_salesPartnerFactory;

    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data $mpHelper
     */
    protected $_mpHelper;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param \View\Element\Template\Context        $context
     * @param \Webkul\Mpreportsystem\Helper\Data    $mpreportHelper
     * @param ProductRepository                     $productRepository
     * @param PriceCurrencyInterface                $priceCurrency
     * @param SellerFactory                         $salesPartnerFactory
     * @param CollectionFactory                     $productCollection
     * @param \Webkul\Marketplace\Helper\Data       $mpHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Mpreportsystem\Helper\Data $mpreportHelper,
        ProductRepository $productRepository,
        PriceCurrencyInterface $priceCurrency,
        SellerFactory $salesPartnerFactory,
        CollectionFactory $productCollection,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_helperdata = $mpreportHelper;
        $this->_productRepository = $productRepository;
        $this->_priceCurrency = $priceCurrency;
        $this->_salesPartnerFactory = $salesPartnerFactory;
        $this->_productCollectionFactory = $productCollection;
        $this->_mpHelper = $mpHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_timezone = $context->getLocaleDate();
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout of page
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $data = $this->getParamValues();
        if ($this->getSalesCollection($data)) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpreportsales.pager'
            )->setAvailableLimit([4 => 4, 8 => 8, 16 => 16])
            ->setCollection(
                $this->getSalesCollection($data)
            );
            $this->setChild('pager', $pager);
            $this->getSalesCollection($data)->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * return customer session id
     *
     * @return void
     */
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }

    /**
     * Give the current url of recently viewed page
     *
     * @return void
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * get country sales graph
     *
     * @param array $data
     * @return void
     */
    public function getCountrySales($data)
    {
        $returnArray = $this->_helperdata->getCountrySalesCollection($data);
        $params = [
            'cht'   =>  'map:fixed=-60,-180,85,180',
            'chma'  =>  '0,110,0,0'
        ];
        $countryArr = $returnArray['country_arr'];
        $totalContrySale = $returnArray['country_sale_arr'];
        $countryOrderCountArr = $returnArray['country_order_count_arr'];
        $countryRegionArr = $returnArray['country_region_arr'];

        $labelArray = [];
        $colorArray = [];
        $legendsArray = [];
        $i = 0;
        $saleArray = [];

        array_push($colorArray, 'B3BCC0');

        foreach ($countryRegionArr as $countryId => $regionArray) {
            foreach ($regionArray as $regionCode => $countryRegionName) {
                $count = $countryOrderCountArr[$countryId][$regionCode];
                $amount = $totalContrySale[$countryId][$regionCode];
                $label = 'f'.$countryRegionName.
                ':Orders-'.$count.
                ' Sales-'.$amount.
                ',000000,0,'.$i.
                ',10';
                array_push($labelArray, $label);
                array_push($legendsArray, $countryRegionName);
                array_push($colorArray, $this->randString());
                array_push(
                    $saleArray,
                    $totalContrySale[$countryId][$regionCode]
                );
                $i++;
            }
        }
        $params['chm'] = implode('|', $labelArray);//text to display
        $params['chld'] = implode('|', $legendsArray);
        $params['chdl'] = implode('|', $legendsArray);
        $params['chco'] = implode('|', $colorArray);//color array
        // seller statistics graph size
        $params['chs'] = 800 . 'x' . 300;
        $getParamData = urlencode(
            base64_encode(
                json_encode($params)
            )
        );
        $getEncryptedHashData = $this->_helperdata
            ->getChartEncryptedHashData($getParamData);
        $params = [
            'param_data' => $getParamData,
            'encrypted_data' => $getEncryptedHashData
        ];

        return $this->getUrl(
            'mpreportsystem/report/generatereport',
            ['_query' => $params, '_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * generates random string for color
     *
     * @param string $charset
     * @return void
     */
    public function randString(
        $charset = 'ABC0123456789'
    ) {
        $length = 6;
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[random_int(0, $count-1)];
        }
        return $str;
    }

    /**
     * get sales graph url
     *
     * @param array $data
     * @return void
     */
    public function getsalesAmount($data)
    {
        $returnArray = $this->_helperdata->getSalesAmount($data);
        $maxSaleValue = max($returnArray['values'])/2;
        $params = [
            'cht'   =>    'lc',
            'chm'   =>    'B,BFCFFF,0,-1,11',
            'chf'   =>    'bg,s,ffffff',
            'chxt'  =>   'x,y,y',
            'chxp' =>      '2,'.$maxSaleValue,
            'chds'  =>   'a',
            'chdl'  =>  __('sales'),
            'chem'  =>  'y',
            'chco'  =>  '76A4FB',
            'chbh'  =>   '55'
        ];
        $getSale = $returnArray['values'];

        if (isset($returnArray['arr'])) {
            $arr = $returnArray['arr'];
            $indexid = 0;
            $tmpstring = implode('|', $arr);
            $valueBuffer[] = $indexid.':|'.$tmpstring;
            $valueBuffer = implode('|', $valueBuffer);
            $params['chxl'] = $valueBuffer;
        } else {
            $params['chxl'] = $returnArray['chxl'];
        }
        if ($data['filter'] == "month") {
            $currMonth = date('m');
            $monthName = date("F", mktime(0, 0, 0, $currMonth, 10));
            $xAxisLabelPos = $returnArray["chxlParamCount"]/2;
            $params['chxp'] = '1,50'.'|3,'.$maxSaleValue;
            $params['chxt'] = 'x,x,y,y';
            $params['chxl'] = $params['chxl'].'|1:|'.__($monthName).'|3:|'.__('Price');
        } elseif ($data['filter'] == "day") {
            $xAxisLabelPos = 24/2;
            $params['chxp'] = '1,50'.'|3,'.$maxSaleValue;
            $params['chxt'] = 'x,x,y,y';
            $params['chxs'] = '0,000000,10,0,t';
            $params['chma'] = '-10,0,0,0|-1,-1';
            $params['chxl'] = $params['chxl'].'|1:|'.__('Time').'|3:|'.__('Price');
        } else {
            $params['chxl'] = $params['chxl'].'|2:|'.__('Price');
        }
        $params['chd'] = 't:'.implode(',', $getSale);
        $valueBuffer = [];
        // seller statistics graph size
        $params['chs'] = 810 . 'x' . 370;
        // return the encoded graph image url
        $getParamData = urlencode(base64_encode(json_encode($params)));
        $getEncryptedHashData = $this->_helperdata
            ->getChartEncryptedHashData($getParamData);
        $params = [
            'param_data' => $getParamData,
            'encrypted_data' => $getEncryptedHashData
        ];
        return $this->getUrl(
            'mpreportsystem/report/generatereport',
            ['_query' => $params, '_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * get top selling products graph
     *
     * @param [type] $data
     * @return void
     */
    public function getProductSales($data)
    {
        $returnArray = $this->_helperdata->getProductsSalesCollection($data);
        $params = [
            'cht'   =>  'p'
        ];
        $chartLabelArray = [];
        $valuesArray = [];
        $legendsArray = [];
        $saleArray = [];
        $productQtyData = $returnArray['returnData'];
        $productNameArray = $returnArray['returnKey'];
        $totalQty = $returnArray['totalQty'];
        $nameArray = $returnArray['name'];
        foreach ($productNameArray as $index => $productId) {
            $productName = $nameArray[$index];
            $productQty = $productQtyData[$index];
            $percent = round(($productQty/$totalQty)*100, 2);
            array_push($chartLabelArray, $productName);
            array_push($valuesArray, $productQty);
            array_push($legendsArray, $percent.'%');
            array_push($saleArray, $productQty);
        }
        $params['chd'] = 't:'.implode(',', $valuesArray); //values for partition
        $params['chdl'] = implode('|', $legendsArray);  //text for legends
        $params['chl'] = implode(
            '|',
            $chartLabelArray
        );
        //text to define chart labels
        $params['chco'] = 'CCCCFF';
        // seller statistics graph size
        $params['chs'] = 800 . 'x' . 200;
        // return the encoded graph image url
        $getParamData = urlencode(base64_encode(json_encode($params)));
        $getEncryptedHashData = $this->_helperdata
            ->getChartEncryptedHashData($getParamData);
        $params = [
            'param_data' => $getParamData,
            'encrypted_data' => $getEncryptedHashData
        ];
        return $this->getUrl(
            'mpreportsystem/report/generatereport',
            ['_query' => $params, '_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * get secure parameter value
     *
     * @return void
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    /**
     * get best customer collection
     *
     * @param array $data
     * @return void
     */
    public function getBestCustomerCollection($data)
    {
        $returnArray = $this->_helperdata->getCustomerCollection($data);
        return $returnArray;
    }

    /**
     * get Formatted Date according
     * to timezone
     * @param void $dateTime
     * @return void
     */
    public function getFormattedDate($dateTime)
    {
        $date = $this->_timezone->date($dateTime);
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * get formatted price
     *
     * @param int $price
     * @param string $currency
     * @return void
     */
    public function getFormatedPrice($price, $currency)
    {
        return $this->_priceCurrency->format(
            $price,
            true,
            2,
            null,
            $currency
        );
    }

    /**
     * get requested parameters value
     *
     * @return void
     */
    public function getParamValues()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * get category name by category id
     * @param  int $categoryId
     * @return string
     */
    public function getCategoryName($categoryId)
    {
        return $this->_helperdata->getCategory($categoryId)->getName();
    }

    /**
     * get sales collection
     * @param  array $data
     * @return collection
     */
    public function getSalesCollection($data)
    {
        if (!$this->_salesCollection) {
            $this->_salesCollection = $this->_helperdata
                ->getSalesCollection($data);
        }
        return $this->_salesCollection;
    }

    /**
     * retrun all sellers name and id
     * @return array
     */
    public function getSellerArray()
    {
        $sellerArray = [];
        $customerEntityTable = $this->_productCollectionFactory
            ->create()->getTable('customer_grid_flat');
        $sellerDataCollection = $this->_salesPartnerFactory->create()
            ->getCollection();
        $sellerDataCollection->getSelect()
            ->join(
                $customerEntityTable.' as customer',
                'main_table.seller_id = customer.entity_id',
                ['customer_name'=>'name']
            );
        foreach ($sellerDataCollection as $sellerData) {
            $data = $sellerData->getData();
            $sellerArray[$data['seller_id']] = $data['shop_url'].
            ' ( '.
            $data['customer_name'].
            ' )';
        }
        return $sellerArray;
    }

    /**
     * get category collection either for admin or for seller
     *
     * @param int $flag
     * @return array
     */
    public function getCategoriesCollection($flag)
    {
        return $this->_helperdata->getCategoriesCollection($flag);
    }

    /**
     * Get all order status
     *
     * @return void
     */
    public function getOrderStatus()
    {
        return $this->_helperdata->getOrderStatus();
    }

    /**
     * Retrieve currency Symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_helperdata->getCurrencySymbol();
    }

    /**
     * encodes data in json format
     *
     * @param array $data
     * @return json array
     */
    public function jsonEncode($data)
    {
        return $this->_jsonHelper->jsonEncode($data);
    }
}
