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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Helper;

/**
 * Class ModuleConfig
 *
 * @package Bss\Simpledetailconfigurable\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModuleConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $magentoVersion;

    /**
     * @var \Magento\ProductVideo\Helper\Media
     */
    private $videoHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * ModuleConfig constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $magentoVersion
     * @param \Magento\ProductVideo\Helper\Media $videoHelper
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $magentoVersion,
        \Magento\ProductVideo\Helper\Media $videoHelper,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->localeFormat = $localeFormat;
        $this->jsonEncoder = $jsonEncoder;
        $this->currency = $currency;
        $this->magentoVersion = $magentoVersion;
        $this->videoHelper = $videoHelper;
        $this->serialize = $serialize;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @param array $data
     * @return false|string
     */
    public function serialize($data)
    {
        if (version_compare($this->getMagentoVersion(), '2.2.0', '<')) {
            return $this->serialize->serialize($data);
        } else {
            $result = json_encode($data);
            if (false === $result) {
                throw new \InvalidArgumentException('Unable to serialize value.');
            }
            return $result;
        }
    }

    /**
     * @param string $string
     * @return array
     */
    public function unserialize($string)
    {
        if (version_compare($this->getMagentoVersion(), '2.2.0', '<')) {
            return $this->serialize->unserialize($string);
        } else {
            $result = json_decode($string, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Unable to unserialize value.');
            }
            return $result;
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyRates()
    {
        $result = [];
        $currencies = $this->storeManager->getStore()->getAvailableCurrencyCodes(true);
        foreach ($currencies as $currency) {
            $result[$currency] = $this->storeManager->getStore()->getBaseCurrency()->getRate($currency);
        }
        return $result;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->storeId = $this->storeManager->getStore()->getId();
        }
        return $this->storeId;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/Simpledetailconfigurable/Enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowSku()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/sku',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowName()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowDescription()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/desc',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowTierPrice()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/tier_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowStockStatus()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowImage()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/image',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildImageConfig()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/child_image',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSuffix()
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/product_url_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowTax()
    {
        return $this->scopeConfig->getValue(
            'tax/display/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCatalogPriceIncludeTax()
    {
        return $this->scopeConfig->getValue(
            'tax/calculation/price_includes_tax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCrossBorder()
    {
        return $this->scopeConfig->getValue(
            'tax/calculation/cross_border_trade_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTaxCalculationBased()
    {
        return $this->scopeConfig->getValue(
            'tax/calculation/based_on',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function preselectConfig()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/preselect',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowAdditionalInfo()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/additional_info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isChangeMetaData()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/meta_data',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isUseForXmlSitemap($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'Bss_Commerce/SDCP_advanced/url_sitemap',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function customUrl()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * @return string
     */
    public function getFomatPrice()
    {
        $config = $this->localeFormat->getPriceFormat();
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->magentoVersion->getVersion();
    }

    /**
     * @return array
     */
    public function getVideoConfig()
    {
        $videoSettingData[] = [
            'playIfBase' => $this->videoHelper->getPlayIfBaseAttribute(),
            'showRelated' => $this->videoHelper->getShowRelatedAttribute(),
            'videoAutoRestart' => $this->videoHelper->getVideoAutoRestartAttribute(),
        ];
        return $videoSettingData;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllConfig()
    {
        $result = [];
        $result['enabled'] = $this->isModuleEnable();
        $result['baseUrl'] = $this->getBaseUrl();
        $result['CurrencySymbol'] = $this->getCurrencySymbol();
        $result['currency_rate'] = $this->getCurrencyRates();
        $result['fomatPrice'] = $this->getFomatPrice();
        $result['tax'] = $this->isShowTax();
        $result['tax_based_on'] = $this->getTaxCalculationBased();
        $result['catalog_price_include_tax'] = $this->isCatalogPriceIncludeTax();
        $result['cross_border'] = $this->isCrossBorder();
        $result['url_suffix'] = $this->getSuffix();
        $result['video'] = $this->getVideoConfig();
        if ($result['enabled']) {
            $result['sku'] = $this->isShowSku();
            $result['name'] = $this->isShowName();
            $result['desc'] = $this->isShowDescription();
            $result['stock'] = $this->isShowStockStatus();
            $result['tier_price'] = $this->isShowTierPrice();
            $result['images'] = $this->isShowImage();
            $result['url'] = $this->customUrl();
            $result['preselect'] = $this->preselectConfig();
            $result['additional_info'] = $this->isShowAdditionalInfo();
            $result['meta_data'] = $this->isChangeMetaData();
        } else {
            $result['sku'] = 0;
            $result['name'] = 0;
            $result['desc'] = 0;
            $result['stock'] = 0;
            $result['tier_price'] = 0;
            $result['images'] = 0;
            $result['url'] = 0;
            $result['preselect'] = 0;
            $result['additional_info'] = 0;
            $result['meta_data'] = 0;
        }
        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnableChildOption()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/child_options',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @param $path string
     * @return mixed
     */
    public function getValueofConfig($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get website code
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteCode()
    {
        return $this->storeManager->getWebsite()->getCode();
    }

    /**
     * Check is enable msi and has msi module
     *
     * @return bool
     */
    public function isEnableMsi()
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }
}
