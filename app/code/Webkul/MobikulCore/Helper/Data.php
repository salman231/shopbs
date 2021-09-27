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

use Webkul\MobikulCore\Model\ConstantRepo;

use Magento\Framework\Oauth\Helper\Oauth as OauthHelper;

/**
 * Helper File
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $cache;
    protected $quote;
    protected $country;
    protected $encrypted;
    protected $mobiToken;
    protected $oauthHelper;
    protected $storeManager;
    protected $directoryList;
    protected $helperCatalog;
    protected $checkoutHelper;
    protected $sessionManager;
    protected $countryCollection;
    protected $oauthTokenFactory;

    /**
     * Constructor function for Helper Class
     *
     * @param OauthHelper                                               $oauthHelper       oauthHelper
     * @param \Magento\Quote\Model\Quote                                $quote             quote
     * @param \Magento\Directory\Model\Country                          $country           country
     * @param \Webkul\Mobikul\Model\OauthToken                          $mobiToken         mobiToken
     * @param \Webkul\MobikulCore\Model\CacheFactory                    $cache             cache
     * @param \Magento\Checkout\Helper\Data                             $checkoutHelper    checkoutHelper
     * @param \Magento\Framework\App\Helper\Context                     $context           context
     * @param \Magento\Framework\Filesystem\Driver\File                 $file              file
     * @param \Magento\Framework\Json\Helper\Data                       $jsonHelper        jsonHelper
     * @param \Webkul\MobikulCore\Helper\Catalog                        $helperCatalog     helperCatalog
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager      storeManager
     * @param \Magento\Config\Model\Config\Backend\Encrypted            $encrypted         encrypted
     * @param \Magento\Quote\Api\Data\AddressInterface                  $addressInterface  addressInterface
     * @param \Magento\Framework\Filesystem\DirectoryList               $directoryList     directoryList
     * @param \Webkul\Mobikul\Model\OauthTokenFactory                   $oauthTokenFactory oauthTokenFactory
     * @param \Magento\Framework\Session\SessionManagerInterface        $sessionManager    sessionManager
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection countryCollection
     */
    public function __construct(
        OauthHelper $oauthHelper,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Directory\Model\Country $country,
        \Webkul\MobikulCore\Model\CacheFactory $cache,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MobikulCore\Model\OauthToken $mobiToken,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Config\Model\Config\Backend\Encrypted $encrypted,
        \Magento\Quote\Api\Data\AddressInterface $addressInterface,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Webkul\MobikulCore\Model\OauthTokenFactory $oauthTokenFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->file = $file;
        $this->cache = $cache;
        $this->quote = $quote;
        $this->country = $country;
        $this->mobiToken = $mobiToken;
        $this->encrypted = $encrypted;
        $this->jsonHelper = $jsonHelper;
        $this->oauthHelper = $oauthHelper;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->helperCatalog = $helperCatalog;
        $this->checkoutHelper = $checkoutHelper;
        $this->sessionManager = $sessionManager;
        $this->addressInterface = $addressInterface;
        $this->countryCollection = $countryCollection;
        $this->oauthTokenFactory = $oauthTokenFactory;
        $this->fileDriver = $fileDriver;
        parent::__construct($context);
    }

    /**
     * Function to get Url of directory
     *
     * @param string $dir directory
     *
     * @return string
     */
    public function getUrl($dir)
    {
        return $this->storeManager->getStore()->getBaseUrl($dir);
    }

    /**
     * Function to authorize the auth key
     *
     * @param string $authKey authkey
     *
     * @return array auth data
     */
    public function isAuthorized($authKey)
    {
        $authData = [];
        $authData["code"] = 2;
        $authData["token"] = "";
        $apiKey = $this->getPassword();
        $apiUsername = $this->getConfigData(ConstantRepo::API_USERNAME);
        $sessionToken = $this->sessionManager->getSessionId();
        $H1 = hash("sha256", $apiUsername.":".$apiKey);
        $H2 = hash("sha256", $H1.":".$sessionToken);
        if ($authKey == $H2) {
            $authData["code"] = 1;
        } else {
            $authData["token"] = $sessionToken;
        }
        $authData["code"] = 1;
        return $authData;
    }

    /**
     * Function to print logs
     *
     * @param array  $data      data
     * @param string $key       key
     * @param array  $wholeData wholedata
     *
     * @return prints log details
     */
    public function log($data, $key, $wholeData)
    {
        $flag = $wholeData[$key] ?? 0;
        $this->printLog($data, $flag);
    }

    /**
     * Function to print logs in mobikul.log file
     *
     * @param array   $data     data
     * @param integer $flag     key
     * @param string  $filename wholedata
     *
     * @return prints log in mobikul.log
     */
    public function printLog($data, $flag = 1, $filename = "mobikul.log")
    {
        if ($flag == 1) {
            $path = $this->directoryList->getPath("var");
            $logger = new \Zend\Log\Logger();
            if (!$this->fileDriver->isExists($path."/log/")) {
                $this->fileDriver->createDirectory($path."/log/", 0777);
            }
            $logger->addWriter(new \Zend\Log\Writer\Stream($path."/log/".$filename));
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            $logger->info($data);
        }
    }

    /**
     * Function to validate keys in wholeData
     *
     * @param array  $wholeData wholedata
     * @param string $key       key to be validated
     *
     * @return bool
     */
    public function validate($wholeData, $key)
    {
        if (isset($wholeData[$key]) && $wholeData[$key] != "") {
            return $wholeData[$key];
        } else {
            return false;
        }
    }

    /**
     * Function to get Config Data
     *
     * @param string                              $path  path
     * @param \Magento\Store\Model\ScopeInterface $scope scope
     *
     * @return integer|string|bool
     */
    public function getConfigData($path, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue($path, $scope, $storeId);
    }

    /**
     * Function to check reorder status
     *
     * @param \Magento\Sales\Model\Order $order order
     *
     * @return integer 0 or 1
     */
    public function canReorder(\Magento\Sales\Model\Order $order)
    {
        if (!$this->getConfigData(ConstantRepo::REORDER_ALLOW)) {
            return 0;
        } else {
            return $order->canReorder();
        }
    }

    /**
     * Get Password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->encrypted->processValue(
            $this->scopeConfig->getValue(ConstantRepo::API_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        );
    }

    /**
     * Function to get customer token byt customer Details
     *
     * @param string  $username   $customer username
     * @param string  $password   $customer Pass word
     * @param integer $customerId customer Id
     *
     * @return string
     */
    public function getTokenByCustomerDetails($username, $password, $customerId)
    {
        $token = $this->mobiToken->loadByCustomerId($customerId)->getToken();
        if (!$token && $username && $password) {
            $token = $this->createCustomerAccessToken($customerId);
        }
        return $token;
    }

    /**
     * Function to create customer acces token
     *
     * @param integer $customerId customer Id
     *
     * @return string
     */
    public function createCustomerAccessToken($customerId)
    {
        $token = $this->oauthTokenFactory->create();
        $token->setData(
            [
                "customer_id" => $customerId,
                "token" => $this->oauthHelper->generateToken(),
                "secret" => $this->oauthHelper->generateTokenSecret()
            ]
        );
        $token->save();
        return $token->getToken();
    }

    /**
     * Function to get customer id by provided token
     *
     * @param string $token customer token
     *
     * @return integer
     */
    public function getCustomerByToken($token)
    {
        return $this->mobiToken->loadByToken($token)->getCustomerId();
    }

    /**
     * Function to get customer Quote
     *
     * @param integer $customerId customer id
     *
     * @return integer
     */
    public function getCustomerQuote($customerId)
    {
        $quoteCollection = $this->quote
            ->getCollection()
            ->addFieldToFilter("customer_id", $customerId)
            ->addFieldToFilter("is_active", 1)
            ->addOrder("updated_at", "DESC");
        return $quoteCollection->getFirstItem();
    }

    /**
     * get Quote by Id
     *
     * @param integer $quoteId quote id
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuoteById($quoteId)
    {
        $activeQuoteCollection = $this->quote
            ->getCollection()
            ->addFieldToFilter("entity_id", $quoteId)
            ->addFieldToFilter("is_active", 1)
            ->addOrder("updated_at", "DESC");
        if (!$activeQuoteCollection->getSize()) {
            $inactiveQuoteCollection = $this->quote
                ->getCollection()
                ->addFieldToFilter("entity_id", $quoteId)
                ->addFieldToFilter("is_active", 0)
                ->addOrder("updated_at", "DESC");
            if ($inactiveQuoteCollection->getSize()) {
                foreach ($inactiveQuoteCollection as $inactiveQuote) {
                    $inactiveQuote->setIsActive(1)->save();
                    return $inactiveQuote;
                }
            } else {
                return $this->quote;
            }
        } else {
            return $activeQuoteCollection->getFirstItem();
        }
    }

    /**
     * Function to validate request for cache
     *
     * @param string $cacheString cache string
     * @param string $eTag        etag
     *
     * @return bool
     */
    public function validateRequestForCache($cacheString, $eTag)
    {
        $cacheStatus = (bool)$this->getConfigData("mobikul/cachesettings/enable");
        if (!$cacheStatus) {
            return false;
        }
        $counter = $this->getConfigData("mobikul/cachesettings/counter");
        if ($counter == "") {
            $counter = 5;
        }
        $collection = $this->cache->create()
            ->getCollection()
            ->addFieldToFilter("request_tag", hash("sha256", $cacheString))
            ->addFieldToFilter("e_tag", $eTag)
            ->addFieldToFilter("counter", ["lteq"=>$counter]);
        if (count($collection)) {
            foreach ($collection as $eachEntry) {
                if ($eachEntry->getCounter() == $counter) {
                    $eachEntry->setCounter(1);
                    $eachEntry->setId($eachEntry->getId());
                    $eachEntry->save();
                    return false;
                } else {
                    $eachEntry->setCounter($eachEntry->getCounter()+1);
                    $eachEntry->setId($eachEntry->getId());
                    $eachEntry->save();
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to update cache
     *
     * @param string $cacheString cache string
     * @param string $eTag        etag
     *
     * @return void
     */
    public function updateCache($cacheString, $eTag)
    {
        $cacheStatus = (bool)$this->getConfigData("mobikul/cachesettings/enable");
        if ($cacheStatus) {
            $counter = $this->getConfigData("mobikul/cachesettings/counter");
            if ($counter == "") {
                $counter = 5;
            }
            $collection = $this->cache->create()->getCollection()
                ->addFieldToFilter("request_tag", hash("sha256", $cacheString));
            if (count($collection)) {
                foreach ($collection as $eachEntry) {
                    $eachEntry->setCounter(1);
                    $eachEntry->setETag(hash("sha256", $eTag));
                    $eachEntry->setId($eachEntry->getId());
                    $eachEntry->save();
                }
            } else {
                $this->cache->create()
                    ->setRequestTag(hash("sha256", $cacheString))
                    ->setCounter(1)
                    ->setETag(hash("sha256", $eTag))
                    ->save();
            }
        }
    }

    /**
     * Function to get number of products in count
     *
     * @param \Magento\Quote\Model\Quote $quote quote
     *
     * @return integer
     */
    public function getCartCount($quote)
    {
        $quoteObject = new \Magento\Framework\DataObject();
        if (is_numeric($quote)) {
            $quoteObject = $this->quote->setStoreId($this->storeManager->getStore()->getId())->load($quote);
        } else {
            $quoteObject = $quote;
        }
        if ((bool)$this->getConfigData("checkout/cart_link/use_qty")) {
            return $quoteObject->getItemsQty() * 1;
        } else {
            return $quoteObject->getItemsCount() * 1;
        }
    }

    /**
     * Function to get currency converted and formatted price
     *
     * @param float $price price
     *
     * @return float
     */
    public function getCurrencyConvertedFormattedPrice($price)
    {
        return $this->helperCatalog->stripTags(
            $this->checkoutHelper->formatPrice($price)
        );
    }
    
    /**
     * Get dominant color from saved file
     *
     * @param string $filePath image path
     *
     * @return string Dominant color of the image
     */
    public function getDominantColor($filePath)
    {
        $path = $this->directoryList->getPath("pub");
        $sha1Tag = sha1($filePath);
        $cacheFile = "color.json";
        if (!$this->file->isExists($path."/media/mobikul/")) {
            $this->file->createDirectory($path."/media/mobikul/");
        }
        $allColorTags = "[]";
        try {
            $allColorTags = $this->file->fileGetContents($path."/media/mobikul/".$cacheFile);
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $allColorTags = "[]";
        }
        if ($allColorTags == "") {
            $allColorTags = "[]";
        }
        $allColorTags = $this->jsonHelper->jsonDecode($allColorTags);
        if (empty($allColorTags[$sha1Tag])) {
            $allColorTags[$sha1Tag] = $this->getColor($filePath);
            try {
                $this->file->filePutContents(
                    $path."/media/mobikul/".$cacheFile,
                    $this->jsonHelper->jsonEncode($allColorTags)
                );
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
                $message = "noting";
            }
        }
        return $allColorTags[$sha1Tag];
    }

    /**
     * Function getColor to get image color
     *
     * @param string $filePath file path
     *
     * @return string Dominant color of the image
     */
    public function getColor($filePath)
    {
        $image = null;
        $total = $blueTotal = $greenTotal = $redTotal = 0;
        try {
            $imageInfo = getimagesize($filePath);
            if (isset($imageInfo["mime"])) {
                switch ($imageInfo["mime"]) {
                    case 'image/jpeg':
                        $image = imagecreatefromjpeg($filePath); // @codingStandardsIgnoreLine
                        break;
                    case 'image/png':
                        $image = imagecreatefrompng($filePath); // @codingStandardsIgnoreLine
                        break;
                    case 'image/gif':
                        $image = imagecreatefromgif($filePath); // @codingStandardsIgnoreLine
                        break;
                }
                if ($image) {
                    $imagesx = imagesx($image); // @codingStandardsIgnoreLine
                    for ($x=0; $x < $imagesx; $x = $x+10) {
                        $imagesy = imagesy($image); // @codingStandardsIgnoreLine
                        for ($y=0; $y < $imagesy; $y = $y+10) {
                            $rgb = imagecolorat($image, $x, $y); // @codingStandardsIgnoreLine
                            $red = ($rgb >> 16) &0xFF;
                            $green = ($rgb >> 8) &0xFF;
                            $blue = $rgb & 0xFF;
                            $redTotal += $red;
                            $greenTotal += $green;
                            $blueTotal += $blue;
                            $total++;
                        }
                    }
                    $redAverage = round($redTotal/$total);
                    $greenAverage = round($greenTotal/$total);
                    $blueAverage = round($blueTotal/$total);
                    return sprintf("#%02x%02x%02x", $redAverage, $greenAverage, $blueAverage);
                }
                return sprintf("#f6f6f6");
            } else {
                return sprintf("#f6f6f6");
            }
        } catch (\Exception $e) {
            return '#dfdfdf';
        }
    }

    /**
     * Function to return color code according to order status
     *
     * @param string $status status of order
     *
     * @return string color code corresponding to the status
     */
    public function getOrderStatusColorCode($status)
    {
        $colorCode = '';
        switch ($status) {
            case "complete":
                $colorCode = "#66BB6A";
                break;
            case "pending":
                $colorCode = "#fead4c";
                break;
            case "processing":
                $colorCode = "#3F51B5";
                break;
            case "hold":
                $colorCode = "#F9A825";
                break;
            case "cancel":
                $colorCode = "#E53935";
                break;
            case "new":
                $colorCode = "#448AFF";
                break;
            case "closed":
                $colorCode = "#e44c53";
                break;
            default:
                $colorCode = "#d5d5d5";
        }
        return $colorCode;
    }

    /**
     * Function to validate mFactor value
     *
     * @param float $mFactor original value of mFactor
     *
     * @return integer a valid value for mFactor
     */
    public function calcMFactor($mFactor)
    {
        if ($mFactor == 0) {
            $mFactor = 1;
        }
        return ceil($mFactor) > 2.5 ? 2.5 : ceil($mFactor);
    }

    /**
     * Function to get valid Dimenions of image
     *
     * @param float $mFactor this is mFactor that will decide the quality of image
     * @param int   $length  this can be width or the height of the image
     *
     * @return int a valid dimension for the image
     */
    public function getValidDimensions($mFactor, $length)
    {
        if ($length == 0) {
            $length = 1000;
        }
        $dimension = $mFactor*$length;
        return $dimension > 2500 ? 2500 : $dimension;
    }

    /**
     * Function to gte Extra Address Form Elements
     *
     * @param \Magento\Customer\Model\Customer $customer customer
     *
     * @return array
     */
    public function getAddressFormExtraData($customer)
    {
        $returnData = [];
        $showPrefix = $this->getConfigData("customer/address/prefix_show");
        if ($showPrefix == "req") {
            $returnData["prefixValue"] = $customer->getPrefix() === null ? "" : $customer->getPrefix();
            $returnData["isPrefixVisible"] = true;
            $returnData["isPrefixRequired"] = true;
        } elseif ($showPrefix == "opt") {
            $returnData["prefixValue"] = $customer->getPrefix() === null ? "" : $customer->getPrefix();
            $returnData["isPrefixVisible"] = true;
        }
        $prefixOptions = $this->getConfigData("customer/address/prefix_options");
        if ($prefixOptions != "") {
            $returnData["prefixOptions"] = explode(";", $prefixOptions);
            $returnData["prefixHasOptions"] = true;
        }
        $showMiddleName = $this->getConfigData("customer/address/middlename_show");
        if ($showMiddleName == 1) {
            $returnData["middleName"] = $customer->getMiddlename() === null ? "" : $customer->getMiddlename();
            $returnData["isMiddlenameVisible"] = true;
        }
        $showSuffix = $this->getConfigData("customer/address/suffix_show");
        if ($showSuffix == "req") {
            $returnData["suffixValue"] = $customer->getSuffix() === null ? "" : $customer->getSuffix();
            $returnData["isSuffixVisible"] = true;
            $returnData["isSuffixRequired"] = true;
        } elseif ($showSuffix == "opt") {
            $returnData["suffixValue"] = $customer->getSuffix() === null ? "" : $customer->getSuffix();
            $returnData["isSuffixVisible"] = true;
        }
        $suffixOptions = $this->getConfigData("customer/address/suffix_options");
        if ($suffixOptions != "") {
            $returnData["suffixOptions"] = explode(";", $suffixOptions);
            $returnData["suffixHasOptions"] = true;
        }
        $returnData["allowToChooseState"] = (bool)$this->getConfigData("general/region/display_all");
        return $returnData;
    }

    /**
     * Function to get Country Data for address form
     *
     * @return array
     */
    public function getAddressCountryData()
    {
        $countryCollection = $this->countryCollection->loadByStore()->toOptionArray(true);
        $requiredState = $this->getConfigData("general/region/state_required");
        $requiredState = explode(",", $requiredState);
        $optionalZipCountries = $this->getConfigData("general/country/optional_zip_countries");
        $optionalZipCountries = explode(",", $optionalZipCountries);
        $returnData = [];
        foreach ($countryCollection as $country) {
            $eachCountry = [];
            $result = [];
            if ($country["value"]) {
                $eachCountry["name"] = $country["label"];
                $eachCountry["country_id"] = $country["value"];
                $eachCountry["isStateRequired"] = in_array($country["value"], $requiredState);
                $countryData = $this->country->loadByCode($country["value"]);
                foreach ($countryData->getRegions() as $region) {
                    $eachRegion = [];
                    $eachRegion["code"] = $region->getCode();
                    $eachRegion["name"] = $region->getName();
                    $eachRegion["region_id"] = $region->getRegionId();
                    $eachCountry["isZipOptional"] = in_array($country["value"], $optionalZipCountries);
                    $result[] = $eachRegion;
                }
                if (count($result) > 0) {
                    $eachCountry["states"] = $result;
                }
                $returnData[] = $eachCountry;
            }
        }
        return $returnData;
    }

    /**
     * Function to get Meida directory path
     *
     * @return string
     */
    public function getBaseMediaDirPath()
    {
        return $this->directoryList->getRoot()."/pub/media/";
    }

    /**
     * Function to get File path
     *
     * @param string $url
     * @return string
     */
    public function getDominantColorFilePath($url)
    {
        $url = str_replace(
            '/index.php',
            '',
            $url
        );
        $baseUrl = str_replace(
            '/index.php',
            '',
            $this->storeManager->getStore()->getBaseUrl()
        );
        $url = str_replace(
            $baseUrl,
            $this->directoryList->getRoot()."/",
            $url
        );
        return $url;
    }
}
