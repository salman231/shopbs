<?php
/**
 * Webkul Software.
 * Webkul SearchSuggestion Controller.
 * @category  Webkul
 * @package   Webkul_SearchSuggestion
 * @author    Webkul
 * @copyright Copyright (c)   Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SearchSuggestion\Controller\Search;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Search\Model\AutocompleteInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Suggest extends Action
{
    /**
     * @var  \Magento\Search\Model\AutocompleteInterface
     */
    private $autocomplete;
    /**
     * @var  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $_product;
    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var  \Magento\Search\Model\AutocompleteInterface
     */
    protected $_priceCurrency;
    /**
     * @var  \Magento\Search\Model\AutocompleteInterface
     */
    protected $_reviewFactory;
    /**
     * @var  \Magento\Backend\Block\Template\Context
     */
    protected $_scopeConfig;
    /**
     * @var  \Magento\Catalog\Model\CategoryFactory
     */
    protected $_category;
    /**
     * @var  \Magento\Catalog\Helper\Image $imageHelper
     */
    protected $_imageHelper;

    /**
     * @param  Context                                                        $context,
     * @param  AutocompleteInterface                                          $autocomplete,
     * @param  \Magento\Backend\Block\Template\Context                        $context1,
     * @param  \Magento\Review\Model\ReviewFactory                            $reviewFactory,
     * @param  \Magento\Framework\Pricing\PriceCurrencyInterface              $priceCurrency,
     * @param  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $product,
     * @param  \Magento\Catalog\Model\CategoryFactory                         $category,
     * @param  \Magento\Framework\Image\AdapterFactory                        $imageFactory,
     * @param  \Magento\Catalog\Helper\Image                                  $imageHelper
     */
    public function __construct(
        Context $context,
        AutocompleteInterface $autocomplete,
        \Magento\Backend\Block\Template\Context $context1,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $product,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Bundle\Model\Product\Price $priceModel,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCol
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->autocomplete = $autocomplete;
        $this->_product = $product;
        $this->_storeManager = $context1->getStoreManager();
        $this->_filesystem = $context1->getFilesystem();
        $this->_scopeConfig = $context1->getScopeConfig();
        $this->_imageFactory = $imageFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_category = $category;
        $this->_imageHelper = $imageHelper;
        $this->priceModel = $priceModel;
        $this->productCol = $productCol;
        parent::__construct($context);
        $this->_values = $this->getValues();
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return $resultRedirect;
        }
        $i=1;
        $autocompleteData = $this->autocomplete->getItems();
        $responseData = [];
        foreach ($autocompleteData as $resultItem) {
            if (($i <= $this->_values['show_terms'] && $this->_values['display_terms'] == 1) ||
                ($this->_values['show_terms'] == '' && $this->_values['display_terms'] == 1)) {
                if ($this->_values['display_terms_num'] == 0) {
                    $resultItem['num_results']=" ";
                }
                $responseData[] = $resultItem->toArray();
            }
            $i++;
        }
        $curr=$this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $key=$this->getRequest()->getParam('q');
        $data=[];
        $productData=[];
        if ($this->_values['show_products'] == '') {
            $this->_values['show_products']=2;
        }
        if ($this->_values['display_product'] == 1 && $this->_values['show_products']>0) {
            $data = $this->_product->create()->addAttributeToSelect('*');
            $data->addFieldToFilter('status', '1');
            $data->addFieldToFilter('visibility', ['neq' => '1']);
            $data->addFieldToFilter('name', ['like' => '%'.$key.'%']);
            $data->setPageSize($this->_values['show_products']);
        }
        $category_items=[];
        $proIds=[];
        foreach ($data as $key => $pro) {
            array_push($proIds, $pro->getId());
        }
        $_productCollection = $this->productCol
                ->addMinimalPrice()
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('visibility', 4)
                ->addAttributeToFilter('entity_id', ['in' => $proIds])
                ->load();

        $j=0;
        foreach ($data as $key => $pro) {

            $this->_reviewFactory->create()->getEntitySummary($pro, $this->_storeManager->getStore()->getId());
            $productData[$pro->getId()]['rate'] = $pro->getRatingSummary()->getRatingSummary();
            $productData[$pro->getId()]['name']=$pro->getName();

            $productData[$pro->getId()]['price']=$_productCollection->getData()[$j]['minimal_price'];

            $productData[$pro->getId()]['type']=$pro->getTypeId();
            $productData[$pro->getId()]['id']=$pro->getId();
            $productData[$pro->getId()]['product_url']=$pro->getProductUrl();
            $productData[$pro->getId()]['currency']=$pro->getCurrency();
            $categoryid=$pro->getCategoryIds();
            if ($pro->getVisibility() == 4) {
                $category_items[$pro->getId()]=$this->getCategoryData($categoryid, $pro);
            }
            $image = 'category_page_list';
            $productData[$pro->getId()]['image_url'] = $this->_imageHelper
                ->init($pro, $image)
                ->constrainOnly(false)
                ->keepAspectRatio(true)
                ->keepFrame(false)
                ->resize(50)
                ->getUrl();
            $current_date=date('Y-m-d H:i:s');
            $specialFromDate=$pro->getSpecialFromDate();
            $specialToDate=$pro->getSpecialToDate();
            $pricePre = "";
            if ($productData[$pro->getId()]['type'] == 'configurable') {
                $pricePre = "As low as ";
            } else {
                if ($productData[$pro->getId()]['type'] == 'grouped') {
                    $pricePre = "Starting at ";
                }
            }
            if ($productData[$pro->getId()]['type'] == 'bundle') {
                $max = $_productCollection->getData()[$j]['max_price'];
                if ($pro->getSpecialPrice() && (($current_date >= $specialFromDate) &&
                 ( $specialToDate >= $current_date))) {

                    $productData[$pro->getId()]['price']="From ".$this->
                    getFormatedPrice($pro->getSpecialPrice(), $curr)." to ".$this->getFormatedPrice($max, $curr);
                } else {
                    $label = "From ".$this->getFormatedPrice($productData[$pro->getId()]['price'], $curr).
                    " to ".$this->getFormatedPrice($max, $curr);
                    $productData[$pro->getId()]['price'] = $label;
                }
            } else {
                if ($pro->getSpecialPrice() && (($current_date >= $specialFromDate) &&
                    ( $specialToDate >= $current_date))) {
                    $productData[$pro->getId()]['price']=$pricePre.$this->
                    getFormatedPrice($pro->getSpecialPrice(), $curr);
                } else {
                    $productData[$pro->getId()]['price']=$pricePre.$this->
                    getFormatedPrice($productData[$pro->getId()]['price'], $curr);
                }
            }
            $j++;
        }
        $allData['category']=$category_items;
        $allData['terms'] =$responseData;
        $allData['items'] =$productData;
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($allData);
        return $resultJson;
    }

    /**
     * Get Formated Price.
     *
     * @return Formated price
     */

    public function getFormatedPrice($price, $currency)
    {
        $precision = 2;
        return $this->_priceCurrency->format(
            $price,
            $includeContainer = true,
            $precision,
            $scope = null,
            $currency
        );
    }

    /**
     * Get Sytem Configuration Search Settings.
     *
     * @return array
     */

    public function getValues()
    {
        $options = [];
        $sectionId = 'searchsuggestion';
        $groupId = 'settings';
        $optionArray = [
                        'display_terms',
                        'display_product',
                        'display_categorie',
                        'show_terms',
                        'show_products',
                        'display_terms_num'
                    ];
        foreach ($optionArray as $option) {
            $value = $sectionId.'/'.$groupId.'/'.$option;
            $value = $this->_scopeConfig->getValue($value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $options[$option] = $value;
        }

        return $options;
    }

    /**
     * Get Category Data.
     *
     * @return Category Data Array
     */

    public function getCategoryData($categoryid, $pro)
    {
        $cat_data = [];
        if ($this->_values['display_categorie'] == 1) {
            foreach ($categoryid as $cat) {
                if ($cat != $this->_storeManager->getStore()->getRootCategoryId()) {
                    $category = $this->_category->create()->load($cat);
                    $cat_data['cat'] = $pro->getName();
                    $cat_data['cat_name'] = $category->getName();
                    $cat_data['cat_url'] = $category->getUrl();
                }
            }
        }
        return $cat_data;
    }
}
