<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Catalog Helper Class
 */
class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Variable date
     *
     * @var DateTime $date
     */
    protected $date;

    /**
     * \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Variable Directory
     *
     * @var \Magento\Framework\Filesystem\DirectoryList $directory
     */
    protected $directory;

    /**
     * \Magento\Framework\Stdlib\DateTime $dateTime
     */
    protected $dateTime;

    /**
     * \Magento\Customer\Api\CustomerRepositoryInterface $customer,
     */
    protected $customer;

    /**
     * \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
     */
    protected $localeDate;

    /**
     * \Magento\Framework\Registry $coreRegistry
     */
    protected $coreString;

    /**
     * \Magento\Framework\Pricing\Helper\Data $priceFormat
     */
    protected $priceFormat;

    /**
     * \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * \Magento\Framework\Image\Factory $imageFactory,
     */
    protected $imageFactory;

    /**
     * \Magento\Framework\Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * \Magento\Review\Model\Review
     */
    protected $reviewModel;

    /**
     * \Magento\Catalog\Model\Layer\Resolver
     */
    protected $catalogLayer;

    /**
     * \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    protected $priceCurrency;

    /**
     * \Magento\Catalog\Model\Layer\Resolver $layerResolver
     */
    protected $layerResolver;

    /**
     * \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * \Magento\Catalog\Helper\Data $catalogHelper
     */
    protected $catalogHelper;

    /**
     * \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    protected $stockRegistry;

    /**
     * \Magento\Framework\Session\SessionManagerInterface $sessionManager
     */
    protected $sessionManager;

    /**
     * \Magento\Checkout\Helper\Data $checkoutHelper
     */
    protected $checkoutHelper;

    /**
     * \Magento\Cms\Model\Template\FilterProvider $filterProvider
     */
    protected $filterProvider;

    /**
     * \Magento\Framework\HTTP\Header
     */
    protected $httpHeader;

    /**
     * \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute
     */
    protected $layerAttribute;

    /**
     * \Webkul\MobikulCore\Block\Configurable
     */
    protected $configurableBlock;

    /**
     * CategoryRepositoryInterface $categoryRepository
     */
    protected $categoryRepository;

    /**
     * \Magento\Wishlist\Model\WishlistFactory $wishlistRepository
     */
    protected $wishlistRepository;

    /**
     * \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $wishListCollection;

    /**
     * \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
     */
    protected $layerFilterData;

    /**
     * \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider
     */
    protected $filterPriceDataprovider;

    /**
     * \Magento\Catalog\Model\Layer\Search
     */
    protected $layerSearch;

    /**
     * \Magento\Store\Model\StoreRepository
     */
    protected $storeRepository;

    /**
     * Function construct for Helper class catalog
     *
     * @param DateTime                                                      $date                    date
     * @param \Magento\Framework\Escaper                                    $escaper                 escaper
     * @param \Magento\Framework\Registry                                   $coreRegistry            coreRegistry
     * @param \Magento\Review\Model\Review                                  $reviewModel             reviewModel
     * @param \Magento\Framework\HTTP\Header                                $httpHeader              httpHeader
     * @param \Magento\Catalog\Helper\Image                                 $imageHelper             imageHelper
     * @param \Magento\Catalog\Helper\Data                                  $catalogHelper           catalogHelper
     * @param \Magento\Store\Block\Switcher                                 $storeSwitcher           storeSwitcher
     * @param \Magento\Framework\Stdlib\DateTime                            $dateTime                dateTime
     * @param \Magento\Checkout\Helper\Data                                 $checkoutHelper          checkoutHelper
     * @param \Magento\Framework\App\Helper\Context                         $context                 context
     * @param \Magento\Framework\Image\Factory                              $imageFactory            imageFactory
     * @param CategoryRepositoryInterface                                   $categoryRepository      categoryRepository
     * @param \Magento\Catalog\Model\Layer\Search                           $layerSearch             layerSearch
     * @param \Magento\Framework\Stdlib\StringUtils                         $stringUtils             stringUtils
     * @param \Magento\Framework\Pricing\Helper\Data                        $priceFormat             priceFormat
     * @param \Magento\Catalog\Model\Layer\Resolver                         $layerResolver           layerResolver
     * @param \Magento\Store\Model\StoreRepository                          $storeRepository         storeRepository
     * @param \Magento\Framework\Filesystem\DirectoryList                   $directory               directory
     * @param \Webkul\MobikulCore\Block\Configurable                         $configurableBlock       cofigurableBlock
     * @param \Magento\Store\Model\StoreManagerInterface                    $storeManager            storeManager
     * @param \Magento\Cms\Model\Template\FilterProvider                    $filterProvider          filterProvider
     * @param \Magento\Customer\Api\CustomerRepositoryInterface             $customer                customer
     * @param \Magento\Wishlist\Model\WishlistFactory                       $wishlistRepository      wishlistRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface             $priceCurrency           priceCurrency
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface          $localeDate              localeDate
     * @param \Magento\Framework\Session\SessionManagerInterface            $sessionManager          sessionManager
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory               $filterItemFactory       filterItemFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface          $stockRegistry           stockRegistry
     * @param \Magento\Catalog\Model\Layer\Filter\AttributeFactory          $layerAttribute          layerAttribute
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Collection         $wishListCollection      wishListcollection
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute   $layerFilterAttribute
     * layerFilterAttribute
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider
     * filterPriceDataprovider
     */
    public function __construct(
        DateTime $date,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Review\Model\Review $reviewModel,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Store\Block\Switcher $storeSwitcher,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\Factory $imageFactory,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Layer\Search $layerSearch,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        \Magento\Framework\Pricing\Helper\Data $priceFormat,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Webkul\MobikulCore\Block\Configurable $configurableBlock,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Customer\Api\CustomerRepositoryInterface $customer,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishListCollection,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute $layerFilterAttribute,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->date = $date;
        $this->escaper = $escaper;
        $this->customer = $customer;
        $this->dateTime = $dateTime;
        $this->directory = $directory;
        $this->httpHeader = $httpHeader;
        $this->localeDate = $localeDate;
        $this->coreString = $stringUtils;
        $this->imageHelper = $imageHelper;
        $this->reviewModel = $reviewModel;
        $this->priceFormat = $priceFormat;
        $this->layerSearch = $layerSearch;
        $this->coreRegistry = $coreRegistry;
        $this->catalogLayer = $layerResolver->get();
        $this->storeManager = $storeManager;
        $this->imageFactory = $imageFactory;
        $this->storeSwitcher = $storeSwitcher;
        $this->catalogHelper = $catalogHelper;
        $this->priceCurrency = $priceCurrency;
        $this->stockRegistry = $stockRegistry;
        $this->sessionManager = $sessionManager;
        $this->checkoutHelper = $checkoutHelper;
        $this->filterProvider = $filterProvider;
        $this->layerAttribute = $layerAttribute;
        $this->storeRepository = $storeRepository;
        $this->configurableBlock = $configurableBlock;
        $this->filterItemFactory = $filterItemFactory;
        $this->wishlistRepository = $wishlistRepository;
        $this->categoryRepository = $categoryRepository;
        $this->wishListCollection = $wishListCollection;
        $this->layerFilterAttribute = $layerFilterAttribute;
        $this->filterPriceDataprovider = $filterPriceDataprovider;
        $this->fileDriver = $fileDriver;
        parent::__construct($context);
    }

    /**
     * Function to get Current StoreId
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * Function getAttributeInputType to get input type of attribute
     *
     * @param object $attribute attribute
     *
     * @return string
     */
    public function getAttributeInputType($attribute)
    {
        $dataType = $attribute->getBackend()->getType();
        $inputType = $attribute->getFrontend()->getInputType();
        if ($inputType == "select" || $inputType == "multiselect") {
            return "select";
        } elseif ($inputType == "boolean") {
            return "yesno";
        } elseif ($inputType == "price") {
            return "price";
        } elseif ($dataType == "int" || $dataType == "decimal") {
            return "number";
        } elseif ($dataType == "datetime") {
            return "date";
        } else {
            return "string";
        }
    }

    /**
     * Function renderRangeLabel to get price range of the product
     *
     * @param float $fromPrice price strting form
     * @param float $toPrice   price ending  to
     * @param float $storeId   Store Id
     *
     * @return string
     */
    public function renderRangeLabel($fromPrice, $toPrice, $storeId)
    {
        if ($fromPrice == "") {
            $fromPrice = 0;
        }
        if ($toPrice == "") {
            $toPrice = 0;
        }
        $formattedFromPrice = $this->stripTags($this->priceFormat->currency($fromPrice));
        if ($toPrice === "" || $toPrice < 1) {
            return __("%1 and above", $formattedFromPrice);
        } elseif ($fromPrice == $toPrice &&
            $this->scopeConfig->getValue(
                "catalog/layered_navigation/one_price_interval",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        ) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
            return __("%1 - %2", $formattedFromPrice, $this->stripTags($this->priceFormat->currency($toPrice)));
        }
    }

    /**
     * Function to get filter data for attributes
     *
     * @param object $attributeFilterModel attributeFilterModel
     * @param object $_filter              $filter
     *
     * @return array
     */
    public function getAttributeFilter($attributeFilterModel, $_filter)
    {
        $options = $_filter->getFrontend()->getSelectOptions();
        $optionsCount = $this->layerFilterAttribute
            ->getCount($attributeFilterModel);
        $data = [];
        foreach ($options as $option) {
            if (is_array($option["value"])) {
                continue;
            }
            if ($this->coreString->strlen($option["value"])) {
                if ($_filter->getIsFilterable() == 1) {
                    if (!empty($optionsCount[$option["value"]])) {
                        $data[] = [
                            "id" => $option["value"],
                            "count" => $optionsCount[$option["value"]],
                            "label" => htmlspecialchars_decode($option["label"])
                        ];
                    }
                } else {
                    $data[] = [
                        "id" => $option["value"],
                        "label" => htmlspecialchars_decode($option["label"]),
                        "count" => isset($optionsCount[$option["value"]]) ? $optionsCount[$option["value"]] : 0
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * Fucntion to get query array
     *
     * @param string $queryStringArray queryStringArray
     *
     * @return array
     */
    public function getQueryArray($queryStringArray)
    {
        $queryArray = [];
        foreach ($queryStringArray as $each) {
            if (in_array($each["inputType"], ["string", "yesno"]) && !empty($each["value"])) {
                if ($each["value"] != "") {
                    $queryArray[$each["code"]] = $each["value"];
                }
            } elseif (in_array($each["inputType"], ["price", "date"]) && !empty($each["value"])) {
                $valueArray = $each["value"];
                if (!empty($valueArray["from"]) &&
                    !empty($valueArray["to"]) &&
                    $valueArray["from"] != "" &&
                    $valueArray["to"] != ""
                ) {
                    $queryArray[$each["code"]] = ["from"=>$valueArray["from"], "to"=>$valueArray["to"]];
                }
            } elseif ($each["inputType"] == "select" && !empty($each["value"])) {
                $valueArray = $each["value"];
                $selectedArray = [];
                foreach ($valueArray as $key => $value) {
                    if ($value == "true") {
                        $selectedArray[] = $key;
                    }
                }
                if (count($selectedArray) > 0) {
                    $queryArray[$each["code"]] = $selectedArray;
                }
            }
        }
        return $queryArray;
    }

    /**
     * Function to check is tax is included in Price
     *
     * @return bool
     */
    public function getIfTaxIncludeInPrice()
    {
        return $this->scopeConfig->getValue("tax/display/type", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Function to get one product;s relevant data to display at any page
     *
     * @param \maganeto\Catalog\Model\Product $product    loaded product
     * @param integer                         $storeId    store Id
     * @param integer                         $width      width
     * @param integer                         $customerId customer Id
     *
     * @return array
     */
    public function getOneProductRelevantData($product, $storeId, $width, $customerId = 0)
    {
        $this->coreRegistry->unregister("current_product");
        $this->coreRegistry->unregister("product");
        $this->coreRegistry->register("current_product", $product);
        $this->coreRegistry->register("product", $product);
        $reviews = $this->reviewModel
            ->getResourceCollection()
            ->addStoreFilter($storeId)
            ->addEntityFilter("product", $product->getId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();
        $eachProduct = [];
        $eachProduct["reviewCount"] = $reviews->getSize();
        $ratings = [];
        if (count($reviews) > 0) {
            foreach ($reviews->getItems() as $review) {
                foreach ($review->getRatingVotes() as $vote) {
                    $ratings[] = $vote->getPercent();
                }
            }
        }
        $isIncludeTaxInPrice = false;
        if ($this->getIfTaxIncludeInPrice() == 2) {
            $isIncludeTaxInPrice = true;
        }
        if ($product->getTypeId() == "configurable") {
            $configurableBlock = $this->configurableBlock;
            $eachProduct["configurableData"] = $configurableBlock->getJsonConfig1();
        } else {
            $eachProduct["configurableData"] = new \stdClass();
        }
        $eachProduct["isInWishlist"] = false;
        $eachProduct["wishlistItemId"] = 0;
        if ($customerId != 0) {
            $wishlist = $this->wishlistRepository->create()->loadByCustomerId($customerId, true);
            $wishlistCollection = $this->wishListCollection
                ->create()
                ->addFieldToFilter("wishlist_id", $wishlist->getId())
                ->addFieldToFilter("product_id", $product->getId());
            $item = $wishlistCollection->getFirstItem();
            if ($item->getId() > 0) {
                $eachProduct["isInWishlist"] = true;
                $eachProduct["wishlistItemId"] = (int)$item->getId();
            } else {
                $eachProduct["isInWishlist"] = false;
                $eachProduct["wishlistItemId"] = 0;
            }
        }
        $eachProduct["typeId"] = $product->getTypeId();
        $eachProduct["entityId"] = $product->getId();
        if ($product->getTypeId() == "downloadable") {
            $eachProduct["linksPurchasedSeparately"] = $product->getLinksPurchasedSeparately();
        }
        $rating = 0;
        if (count($ratings) > 0) {
            $rating = number_format((5 * (array_sum($ratings) / count($ratings))) / 100, 1, ".", "");
        }
        $eachProduct["rating"] = $rating;
        if ($product->isAvailable()) {
            $eachProduct["isAvailable"] = true;
        } else {
            $eachProduct["isAvailable"] = false;
        }
        $price = $product->getPrice();
        $finalPrice = $product->getFinalPrice();
        if ($product->getTypeId() == "configurable") {
            $regularPrice = $product->getPriceInfo()->getPrice("regular_price");
            $price = $regularPrice->getAmount()->getBaseAmount();
        } elseif (!empty($price)) {
            $price = $this->priceFormat->currency($price, false, false);
            $finalPrice = $this->priceFormat->currency($finalPrice, false, false);
        } elseif (empty($price)) {
            $price = 0.0;
        }
        if ($isIncludeTaxInPrice) {
            $eachProduct["price"] = $this->catalogHelper->getTaxPrice($product, $price);
            $eachProduct["finalPrice"] = $this->catalogHelper->getTaxPrice($product, $finalPrice);
            $eachProduct["formattedPrice"] = $this->stripTags(
                $this->priceCurrency->format($this->catalogHelper->getTaxPrice($product, $price))
            );
            $eachProduct["formattedFinalPrice"] = $this->stripTags(
                $this->priceCurrency->format($this->catalogHelper->getTaxPrice($product, $product->getFinalPrice()))
            );
        } else {
            $eachProduct["price"] = $price;
            $eachProduct["finalPrice"] = $finalPrice;
            $eachProduct["formattedPrice"] = $this->stripTags($this->priceCurrency->format($price));
            $eachProduct["formattedFinalPrice"] = $this->stripTags($this->priceCurrency->format($finalPrice));
        }
        $eachProduct["name"] = htmlspecialchars_decode($product->getName());
        $returnArray["msrpEnabled"] = $product->getMsrpEnabled();
        $eachProduct["hasRequiredOptions"] = ((bool)$product->getRequiredOptions() || (bool)$product->getHasOptions());
        $returnArray["msrpDisplayActualPriceType"] = $product->getMsrpDisplayActualPriceType();
        if ($product->getTypeId() == "grouped") {
            $minPrice = 0;
            $minPrice = $product->getMinimalPrice();
            if ($isIncludeTaxInPrice) {
                $eachProduct["groupedPrice"] = $this->stripTags(
                    $this->priceFormat->currency($this->catalogHelper->getTaxPrice($product, $minPrice))
                );
            } else {
                $eachProduct["groupedPrice"] = $this->stripTags($this->priceFormat->currency($minPrice));
            }
            $eachProduct["formattedFinalPrice"] = $eachProduct["groupedPrice"];
        }
        if ($product->getTypeId() == "bundle") {
            $eachProduct["priceView"] = $product->getPriceView();
            $priceModel = $product->getPriceModel();
            if ($isIncludeTaxInPrice) {
                list($minimalPriceInclTax, $maximalPriceInclTax) = $priceModel->getTotalPrices(
                    $product,
                    null,
                    true,
                    false
                );
                $eachProduct["minPrice"] = $this->catalogHelper->getTaxPrice($product, $minimalPriceInclTax);
                $eachProduct["maxPrice"] = $this->catalogHelper->getTaxPrice($product, $maximalPriceInclTax);
                $eachProduct["formattedMaxPrice"] = $this->stripTags(
                    $this->priceFormat->currency($this->catalogHelper->getTaxPrice($product, $maximalPriceInclTax))
                );
                $eachProduct["formattedMinPrice"] = $this->stripTags(
                    $this->priceFormat->currency($this->catalogHelper->getTaxPrice($product, $minimalPriceInclTax))
                );
            } else {
                list($minimalPriceTax, $maximalPriceTax) = $priceModel->getTotalPrices($product, null, null, false);
                $eachProduct["minPrice"] = $minimalPriceTax;
                $eachProduct["maxPrice"] = $maximalPriceTax;
                $eachProduct["formattedMinPrice"] = $this->stripTags($this->priceFormat->currency($minimalPriceTax));
                $eachProduct["formattedMaxPrice"] = $this->stripTags($this->priceFormat->currency($maximalPriceTax));
            }
            $eachProduct["formattedPrice"] = $eachProduct['minPrice'];
            $eachProduct["formattedFinalPrice"] = $eachProduct['formattedMinPrice'];
        }
        $todate = $product->getSpecialToDate();
        $fromdate = $product->getSpecialFromDate();
        $eachProduct["isNew"] = $this->isProductNew($product);
        $eachProduct["isInRange"] = $this->getIsInRange($todate, $fromdate);
        $eachProduct["thumbNail"] = $this->getImageUrl($product, $width/2.5);
        $objectManager = \Magento\Framework\app\ObjectManager::getInstance();
        $mobikulHelper = $objectManager->create(\Webkul\MobikulCore\Helper\Data::class);
        $eachProduct["dominantColor"] = $mobikulHelper->getDominantColor($eachProduct["thumbNail"]);
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minQty = $stockItem->getMinSaleQty();
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        $tier_price = $product->getTierPrice();
        if (count($tier_price) > 0) {
            foreach ($tier_price as $pirces) {
                foreach (array_reverse($pirces) as $k => $v) {
                    if ($k == "price") {
                        $tp = number_format($v, 2, '.', '');
                        $eachProduct['tierPrice'] = $tp;
                        $eachProduct['formattedTierPrice'] = $this->stripTags($this->priceCurrency->format($tp));
                    }
                }
            }
        } else {
            $eachProduct['tierPrice'] = "";
            $eachProduct['formattedTierPrice'] = "";
        }

        if ($configQty > $minQty) {
            $minQty = $configQty;
        }
        $eachProduct["minAddToCartQty"] = $minQty;
        if ($product->isAvailable()) {
            $eachProduct["availability"] = __("In stock");
            $eachProduct["isAvailable"] = true;
        } else {
            $eachProduct["availability"] = __("Out of stock");
            $eachProduct["isAvailable"] = false;
        }
        $eachProduct["arUrl"] = (string)$this->getArModelUrl($product);
        $eachProduct["arType"] = $this->getArModelType($product);
        $eachProduct["arTextureImages"] = $this->getTextureImages($product);
        return $eachProduct;
    }

    /**
     * Function to get is in range value
     *
     * @param string $todate   $todate
     * @param string $fromdate $fromdate
     *
     * @return bool
     */
    public function getIsInRange($todate, $fromdate)
    {
        $isInRange = false;
        $today = $this->date->date("Y-m-d H:i:s");
        $toTime = strtotime($todate);
        $fromTime = strtotime($fromdate);
        $todayTime = strtotime($today);
        if (isset($fromdate) && isset($todate)) {
            if ($todayTime >= $fromTime && $todayTime <= $toTime) {
                $isInRange = true;
            }
        }
        if (isset($fromdate) && !isset($todate)) {
            if ($todayTime >= $fromTime) {
                $isInRange = true;
            }
        }
        if (!isset($fromdate) && isset($todate)) {
            if ($todayTime <= $fromTime) {
                $isInRange = true;
            }
        }
        return $isInRange;
    }

    /**
     * Function to get the value from core config
     *
     * @return bool
     */
    public function useCalenderForCustomOption()
    {
        return $this->scopeConfig->getValue(
            "catalog/custom_options/use_calendar",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to check if the product is new
     *
     * @param Magento\Catalog\Model\Product $product product
     *
     * @return bool
     */
    protected function isProductNew($product)
    {
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $productNewFromDate = $product->getNewsFromDate();
        $productNewToDate = $product->getNewsToDate();
        if (($productNewFromDate && strtotime($todayStartOfDayDate) >= strtotime($productNewFromDate))
            || ($productNewToDate && strtotime($todayEndOfDayDate) <= strtotime($productNewToDate))
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to get Website Data
     *
     * @return array
     */
    public function getWebsiteData()
    {
        $websiteData = [];
        $websites = $this->storeManager->getWebsites();
        $storeList = $this->storeRepository->getList();
        $storeData = [];
        foreach ($websites as $website) {
            $websiteData[] = [
                "id" => $website->getWebsiteId(),
                "name" => $website->getName()
            ];
        }
        return $websiteData;
    }

    /**
     * Function to get StoreData from websiteId
     *
     * @param int $websiteId website id
     *
     * @return array
     */
    public function getStoreData($websiteId = "1")
    {
        $storeData = [];
        $storeBlock = $this->storeSwitcher;
        foreach ($storeBlock->getGroups() as $group) {
            if ($group->getWebsiteId() == $websiteId) {
                $groupArr = [];
                $groupArr["id"] = $group->getGroupId();
                $groupArr["name"] = $group->getName();
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    if (!$store->isActive()) {
                        continue;
                    }
                    $storeArr = [];
                    $storeArr["id"] = $store->getStoreId();
                    $code = explode("_", $this->getLocaleCodes($store->getId()));
                    $storeArr["code"] = $code[0];
                    $storeArr["name"] = $store->getName();
                    $groupArr["stores"][] = $storeArr;
                }
                $storeData[] = $groupArr;
            } else {
                continue;
            }
        }
        return $storeData;
    }

    /**
     * Function to strip Tags
     *
     * @param string $data data
     *
     * @return string
     */
    public function stripTags($data)
    {
        return strip_tags($data);
    }

    /**
     * Function to get Locale Codes
     *
     * @param integer $store storeId
     *
     * @return string
     */
    public function getLocaleCodes($store)
    {
        return $this->scopeConfig->getValue(
            "general/locale/code",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Function to check whether to show out of stock products or not
     *
     * @return bool
     */
    public function showOutOfStock()
    {
        return $this->scopeConfig->getValue(
            "cataloginventory/options/show_out_of_stock",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to get Page Size
     *
     * @return integer
     */
    public function getPageSize()
    {
        return $this->scopeConfig->getValue(
            "mobikul/configuration/pagesize",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to get Page Size
     *
     * @return integer
     */
    public function getPriceRangeCalculation()
    {
        return $this->scopeConfig->getValue(
            "catalog/layered_navigation/price_range_calculation",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to get Maximum length for search query
     *
     * @return integer
     */
    public function getMaxQueryLength()
    {
        return $this->scopeConfig->getValue(
            \Magento\Search\Model\Query::XML_PATH_MAX_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to get Date Format
     *
     * @param string $date   date
     * @param string $format format of required date
     *
     * @return string
     */
    public function formatDate($date, $format = null)
    {
        if ($format != null) {
            return $this->dateTime->formatDate($date, $format);
        } else {
            return $this->dateTime->formatDate($date);
        }
    }

    /**
     * Function to escape html from text string
     *
     * @param string $text text string to remove html from
     *
     * @return string
     */
    public function escapeHtml($text)
    {
        return $this->escaper->escapeHtml($text);
    }

    /**
     * Function to base path of a folder
     *
     * @param string $folder folder of which the base path is required
     *
     * @return string
     */
    public function getBasePath($folder = "media")
    {
        return $this->directory->getPath($folder);
    }

    /**
     * Function to get Image Url
     *
     * @param \Magento\Catalog\Model\Product $product   product data
     * @param integer                        $size      size
     * @param string                         $imageType type of image
     * @param bool                           $keepFrame keep frame or not
     *
     * @return string
     */
    public function getImageUrl($product, $size, $imageType = "product_page_image_small", $keepFrame = true)
    {
        return $this->imageHelper
            ->init($product, $imageType)
            ->keepFrame($keepFrame)
            ->resize($size)
            ->getUrl();
    }

    /**
     * Function to get resize image and create cache for that image
     *
     * @param string  $basePath    base path
     * @param string  $newPath     destination path
     * @param integer $width       width of the image type of image
     * @param integer $height      height of the image type of image
     * @param bool    $forCustomer is for customer or not
     *
     * @return string
     */
    public function resizeNCache($basePath, $newPath, $width, $height, $forCustomer = false)
    {
        if (!$this->fileDriver->isFile($newPath) || $forCustomer) {
            $imageObj = $this->imageFactory->create($basePath);
            $imageObj->keepAspectRatio(false);
            $imageObj->backgroundColor([255, 255, 255]);
            $imageObj->keepFrame(false);
            $imageObj->resize($width, $height);
            $imageObj->save($newPath);
        }
    }

    /**
     * Function to get price filter options
     *
     * @param array  $filter     filter array to be applied on
     * @param object $collection product Collection to be applied on
     *
     * @return array
     */
    public function getPriceFilterOptions($filter, $collection)
    {
        try {
            $facets = $collection->getFacetedData($filter->getAttributeCode());
        } catch (\Exception $e) {
            return [];
        }
        $productSize = $collection->getSize();
        $data = [];
        if (count($facets) > 1) {
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation["count"];
                if (strpos($key, "_") === false) {
                    continue;
                }
                if (!$this->isOptionReducesResults($count, $productSize)) {
                    continue;
                }
                $data[] = $this->prepareData($key, $count, $data);
            }
        }
        return $data;
    }

    /**
     * Function to get price filter
     *
     * @param object  $priceFilterModel instance of price model filter
     * @param integer $storeId          product Collection to be applied on
     *
     * @return array
     */
    public function getPriceFilter($priceFilterModel, $storeId)
    {
        if ($this->getPriceRangeCalculation() == "improved") {
            $algorithmModel = $this->_objectManager->create(\Magento\Catalog\Model\Layer\Filter\Price\Algorithm::class);
            $collection = $priceFilterModel->getLayer()->getProductCollection();
            $appliedInterval = $priceFilterModel->getInterval();
            if ($appliedInterval && $collection->getPricesCount() <= $priceFilterModel->getIntervalDivisionLimit()) {
                return [];
            }
            $algorithmModel->setPricesModel($priceFilterModel)->setStatistics(
                $collection->getMinPrice(),
                $collection->getMaxPrice(),
                $collection->getPriceStandardDeviation(),
                $collection->getPricesCount()
            );
            if ($appliedInterval) {
                if ($appliedInterval[0] == $appliedInterval[1] || $appliedInterval[1] === "0") {
                    return [];
                }
                $algorithmModel->setLimits($appliedInterval[0], $appliedInterval[1]);
            }
            $items = [];
            foreach ($algorithmModel->calculateSeparators() as $separator) {
                $items[] = [
                    "label" => $this->stripTags(
                        $this->renderRangeLabel($separator["from"], $separator["to"], $storeId)
                    ),
                    "id" => (($separator["from"] == 0) ? "" : $separator["from"])."-".$separator["to"].
                        $priceFilterModel->_getAdditionalRequestData(),
                    "count" => $separator["count"]
                ];
            }
        } elseif ($priceFilterModel->getInterval()) {
            return [];
        }
        $range = $priceFilterModel->getPriceRange();
        $dbRanges = $priceFilterModel->getRangeItemCounts($range."");
        $data = [];
        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];
            foreach ($dbRanges as $index => $count) {
                $fromPrice = ($index == 1) ? "" : (($index - 1) * $range);
                $toPrice = ($index == $lastIndex) ? "" : ($index * $range);
                $data[] = [
                    "label" => $this->stripTags($this->renderRangeLabel($fromPrice, $toPrice, $storeId)),
                    "id" => $fromPrice."-".$toPrice,
                    "count" => $count
                ];
            }
        }
        return $data;
    }

    public function getFilterOptions($filter, $productCollection)
    {
        $allData = [];
        $attributeFilterModel = $this->layerAttribute->create()->setAttributeModel($filter);
        try {
            $optionsFacetedData = $productCollection->getFacetedData($filter->getAttributeCode());
        } catch (\Exception $e) {
            return [];
        }
        $options = $filter->getFrontend()->getSelectOptions();
        $productSize = $productCollection->getSize();
        foreach ($options as $option) {
            $value = $this->getOptionValue($option);
            if ($value === false) {
                continue;
            }
            $isAttributeFilterable = (int)$filter->getIsFilterable() === 1;
            $count = $this->getOptionCount($value, $optionsFacetedData);
            if ($isAttributeFilterable && (!$this->isOptionReducesResults($count, $productSize) || $count === 0)) {
                continue;
            }
            $each["id"] = $value;
            $each["label"] = $option["label"];
            $each["count"] = $count;
            $allData[] = $each;
        }
        return $allData;
    }

    public function getProductListColl($categoryId, $type = null)
    {
        if ($type == "search") {
            $layer = $this->layerSearch;
        } else {
            $layer = $this->catalogLayer;
        }
        $origCategory = null;
        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (\NoSuchEntityException $e) {
                $category = null;
            }
            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }
        $collection = $layer->getProductCollection();
        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }
        return $collection;
    }

    public function getOptionValue($option)
    {
        if (empty($option["value"]) && !is_numeric($option["value"])) {
            return false;
        }
        return $option["value"];
    }

    public function getOptionCount($value, $optionsFacetedData)
    {
        return $optionsFacetedData[$value]["count"] ?? 0;
    }

    public function isOptionReducesResults($optionCount, $totalSize)
    {
        return $optionCount < $totalSize;
    }

    public function _createItem($label, $value, $count = 0)
    {
        return $this->filterItemFactory->create()
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count);
    }

    private function prepareData($key, $count)
    {
        list($from, $to) = explode("_", $key);
        if ($from == "*") {
            $from = $this->getFrom($to);
        }
        if ($to == "*") {
            $to = $this->getTo($to);
        }
        $label = $this->_renderPriceRangeLabel($from, $to);
        $value = $from . "-" . $to . $this->filterPriceDataprovider->create()->getAdditionalRequestData();
        $data = [
            "id" => $value,
            "label" => $label,
            "count" => $count
        ];
        return $data;
    }

    protected function _renderPriceRangeLabel($fromPrice, $toPrice)
    {
        $fromPrice = empty($fromPrice) ? 0 : $fromPrice * $this->getCurrencyRate();
        $toPrice = empty($toPrice) ? $toPrice : $toPrice * $this->getCurrencyRate();
        $formattedFromPrice = $this->stripTags($this->priceCurrency->format($fromPrice));
        if ($toPrice === "") {
            return __("%1 and above", $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $this->filterPriceDataprovider->create()->getOnePriceIntervalValue()) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
            return __("%1 - %2", $formattedFromPrice, $this->stripTags($this->priceCurrency->format($toPrice)));
        }
    }

    /**
     * Function to get To Date
     *
     * @param string $from from data
     *
     * @return string
     */
    public function getTo($from)
    {
        $to = "";
        $interval = $this->filterPriceDataprovider->create()->getInterval();
        if ($interval && is_numeric($interval[1]) && $interval[1] > $from) {
            $to = $interval[1];
        }
        return $to;
    }

    /**
     * Function to get Currency Rate
     *
     * @return integer
     */
    public function getCurrencyRate()
    {
        $rate = $this->storeManager->getStore() ->getCurrentCurrencyRate();
        if (!$rate) {
            $rate = 1;
        }
        return $rate;
    }

    /**
     * Function to get From Date
     *
     * @param string $from from data
     *
     * @return string
     */
    public function getFrom($from)
    {
        $to = "";
        $interval = $this->filterPriceDataprovider->create()->getInterval();
        if ($interval && is_numeric($interval[0]) && $interval[0] < $from) {
            $to = $interval[0];
        }
        return $to;
    }

    /**
     * Function to get Ar Model Type
     *
     * @param Magento\Catalog\Model\Product $product product
     *
     * @return string
     */
    public function getArModelType($product)
    {
        if ($product->getArType() == 1) {
            return "3D";
        }
        return "2D";
    }

    /**
     * Function to get Model Url
     *
     * @param Magento\Catalog\Model\Product $product product
     *
     * @return string
     */
    public function getArModelUrl($product)
    {
        $userAgent = $this->httpHeader->getHttpUserAgent();
        if ($product->getArType() == 1 && strpos(strtolower($userAgent), "alamofire") !== false) {
            return $product->getArModelFileIos();
        } elseif ($product->getArType() == 1) {
            return $product->getArModelFileAndroid();
        }
        return $product->getAr2dFile();
    }

    /**
     * Function to get Texture Images
     *
     * @param Magento\Catalog\Model\Product $product product
     *
     * @return string
     */
    public function getTextureImages($product)
    {
        $textureImages = [];
        $userAgent = $this->httpHeader->getHttpUserAgent();
        if ($product->getArType() == 1 &&
            $product->getArTextureImage() &&
            strpos(strtolower($userAgent), "alamofire") !== false
        ) {
            $textureImages = array_values((array)json_decode($product->getArTextureImage()));
        }
        return $textureImages;
    }
}
