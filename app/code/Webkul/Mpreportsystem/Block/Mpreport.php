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

namespace Webkul\Mpreportsystem\Block;

use Magento\Framework\Controller\Result;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Mpreportsystem Mpreport Block
 */
class Mpreport extends \Magento\Framework\View\Element\Template
{
    /**
     * url to get chart
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
     * @var TimezoneInterface
     */
    protected $_timezone;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Mpreportsystem\Helper\Data $mpreportHelper
     * @param ProductRepository $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Mpreportsystem\Helper\Data $mpreportHelper,
        ProductRepository $productRepository,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_helperdata = $mpreportHelper;
        $this->_productRepository = $productRepository;
        $this->_priceCurrency = $priceCurrency;
        $this->_timezone = $context->getLocaleDate();
        $this->_mpHelper = $mpHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare Page Layout
     *
     * @return \Webkul\Mpreportsystem\Block\Mpreport
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $data = $this->getParamValues();
        if ($this->getSalesCollection($data)) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpreportsales.pager'
            )
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
     * Get Customer Id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }

    /**
     * Get Current Page Url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * calculate data according to
     * country sale and return sales according to country
     * @param  array $data
     * @return string
     */
    public function getCountrySales($data)
    {
        $sellerId = $this->getCustomerId();
        $data['seller_id'] = $sellerId;
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
        $chldArray = [];
        $i = 0;
        $saleArray = [];

        array_push($colorArray, 'B3BCC0');

        foreach ($countryRegionArr as $countryId => $regionArray) {
            foreach ($regionArray as $regionCode => $countryRegionName) {
                $count = $countryOrderCountArr[$countryId][$regionCode];
                $amount = $this->_helperdata->getCurrentAmount($totalContrySale[$countryId][$regionCode]);
                $label = 'f'.$countryRegionName.
                ':Orders-'.$count.
                ' Sales-'.$amount.
                ',000000,0,'.$i.',10';
                array_push($labelArray, $label);
                array_push($legendsArray, $countryRegionName);
                array_push($colorArray, $this->randString());
                array_push(
                    $saleArray,
                    $this->_helperdata->getCurrentAmount($totalContrySale[$countryId][$regionCode])
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
        //encryption
        $getParamData = urlencode(base64_encode(json_encode($params)));
        $getEncryptedHashData = $this->_helperdata
            ->getChartEncryptedHashData($getParamData);
        // parameteres to pass
        $params = [
            'param_data' => $getParamData,
            'encrypted_data' => $getEncryptedHashData
        ];

        return $this->getUrl(
            'mpreportsystem/report/generatereport',
            [
                '_query' => $params,
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }

    /**
     * generate random string for color
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
     * return graph url for the sales graph
     * @param  array $data
     * @return string
     */
    public function getsalesAmount($data)
    {
        $sellerId = $this->getCustomerId();
        $data['seller_id'] = $sellerId;
        $returnArray = $this->_helperdata->getSalesAmount($data);
        $maxSaleValue = max($returnArray['values'])/2;
        $params = [
            'cht'   =>    'lc',
            'chm'   =>    'B,BFCFFF,0,-1,11',
            'chf'   =>    'bg,s,ffffff',
            'chxt'  =>     'x,y,y',
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
     * return url of image for top selling product
     * @param  array $data
     * @return string
     */
    public function getProductSales($data)
    {
        $sellerId = $this->getCustomerId();
        $data['seller_id'] = $sellerId;
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
        $nameArray = $returnArray['name'];
        $totalQty = $returnArray['totalQty'];
        foreach ($productNameArray as $index => $productId) {
            $productName = $nameArray[$index];
            $productQty = $productQtyData[$index];
            if ($totalQty>0) {
                $percent = round(($productQty/$totalQty)*100, 2);
            } else {
                $percent = round(($productQty/1)*100, 2);
            }
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
     * get best customers collection
     * @param  array $data
     * @return collection
     */
    public function getBestCustomerCollection($data)
    {
        $sellerId = $this->getCustomerId();
        $data['seller_id'] = $sellerId;
        $returnArray = $this->_helperdata->getCustomerCollection($data);
        return $returnArray;
    }

    /**
     * return formatted price
     *
     * @param int $price
     * @param string $currency
     * @return void
     */
    public function getFormatedPrice($price, $currency)
    {
        return $this->_helperdata->convertPrice($price);
    }

    /**
     * get request parameters
     *
     * @return void
     */
    public function getParamValues()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * return category name by category id
     * @param  int $categoryId
     * @return string
     */
    public function getCategoryName($categoryId)
    {
        return $this->_helperdata->getCategory($categoryId)->getName();
    }

    /**
     * return sales collection
     * @param  array $data
     * @return collection
     */
    public function getSalesCollection($data)
    {
        $sellerId = $this->getCustomerId();
        $data['seller_id'] = $sellerId;
        if (!$this->_salesCollection) {
            $this->_salesCollection = $this->_helperdata
                ->getSalesCollection($data);
        }
        return $this->_salesCollection;
    }

    public function getFormattedDate($dateTime)
    {
        $date = $this->_timezone->date($dateTime);
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get Param Value
     *
     * @param string $key
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function getParamValue($key, $defaultValue = '')
    {
        try {
            $value = $this->getRequest()->getParam($key);
            if (empty($value)) {
                return $defaultValue;
            }

            return $value;
        } catch (\Exception $e) {
            return $defaultValue;
        }
    }

    /**
     * get selected categories
     *
     * @return array
     */
    public function getSelectedCategories()
    {
        $categories = $this->getParamValue("categories", []);
        if (!is_array($categories)) {
            $categories = [$categories];
        }

        return $categories;
    }

    /**
     * get selected order status
     *
     * @return array
     */
    public function getSelectedOrderStatus()
    {
        $status = $this->getParamValue("orderstatus", []);
        if (!is_array($status)) {
            $status = [$status];
        }

        return $status;
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
