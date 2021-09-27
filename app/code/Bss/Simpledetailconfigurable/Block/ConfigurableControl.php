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
namespace Bss\Simpledetailconfigurable\Block;

use Bss\Simpledetailconfigurable\Helper\Integrate;
use Bss\Simpledetailconfigurable\Helper\ModuleConfig;
use Bss\Simpledetailconfigurable\Helper\ProductData;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Framework\Locale\Format;

/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableControl extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    const BSS_SWATCH_RENDERER_TEMPLATE = 'Bss_Simpledetailconfigurable::SimpledetailControl.phtml';
    const BSS_CONFIGURABLE_RENDERER_TEMPLATE = 'Bss_Simpledetailconfigurable::configurable.phtml';

    /**
     * @var ProductData
     */
    private $linkData;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var Format
     */
    private $localeFormat;

    /**
     * @var Format
     */
    private $integrate;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param Format $localeFormat
     * @param ProductData $linkData
     * @param ModuleConfig $moduleConfig
     * @param Integrate $integrate
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        Format $localeFormat,
        ProductData $linkData,
        ModuleConfig $moduleConfig,
        Integrate $integrate,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->linkData = $linkData;
        $this->moduleConfig = $moduleConfig;
        $this->localeFormat = $localeFormat;
        $this->integrate = $integrate;
        $this->json = $json;
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data
        );
    }

    /**
     * get is enable module ConfigurableWholesaleDisplay
     *
     * @return bool
     */
    public function isEnableCpwd()
    {
        $isEnabled = false;
        $product = $this->getProduct();
        $this->linkData->getEventManager()->dispatch('bss_prepare_product_wholesale_table', ['product' => $product]);
        if ($product->getEnabledCpwd()) {
            $isEnabled = true;
        }
        return $isEnabled;
    }

    /**
     * @return string
     */
    public function getJsonChildProductData()
    {
        return $this->json->serialize($this->linkData->getAllData($this->getProduct()->getEntityId()));
    }

    /**
     * @return string
     */
    public function getJsonModuleConfig()
    {
        $data = '';
        if ($this->linkData->getEnabledModuleOnProduct($this->getProduct()->getEntityId())->getEnabled()) {
            $data = $this->moduleConfig->getAllConfig();
            $data['cpwd'] = $this->isEnableCpwd();
        }
        return $this->json->serialize($data);
    }

    /**
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowProducts()
    {
        if (!$this->moduleConfig->isModuleEnable() || !$this->moduleConfig->isShowStockStatus()) {
            return parent::getAllowProducts();
        }
        if (!$this->hasAllowProducts()) {
            $products = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct(), null);
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();

        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->getAttributesDataCustom($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->getLocalFormatNumber($regularPrice->getAmount()->getValue()),
                ],
                'basePrice' => [
                    'amount' => $this->getLocalFormatNumber($finalPrice->getAmount()->getBaseAmount()),
                ],
                'finalPrice' => [
                    'amount' => $this->getLocalFormatNumber($finalPrice->getAmount()->getValue()),
                ],
            ],
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => $this->getOptionImages(),
            'index' => isset($options['index']) ? $options['index'] : []
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
            $config['preselectOptions'] = $currentProduct->getPreconfiguredValues()->getOptions();
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->json->serialize($config);
    }

    /**
     * @param string $value
     * @return float|string
     */
    private function getLocalFormatNumber($value)
    {
        if (version_compare($this->getMagentoVersion(), '2.2.0', '<')) {
            return $this->_registerJsPrice($value);
        } else {
            return $this->localeFormat->getNumber($value);
        }
    }

    /**
     * @param Product $product
     * @param array $options
     * @return array
     */
    public function getAttributesDataCustom($product, array $options = [])
    {
        $defaultValues = [];
        $attributes = [];
        foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
            $attributeOptionsData = $this->getAttributeOptionsDataCustom($attribute, $options);
            if ($attributeOptionsData) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeId = $productAttribute->getId();
                $attributes[$attributeId] = [
                    'id' => $attributeId,
                    'code' => $productAttribute->getAttributeCode(),
                    'label' => $productAttribute->getStoreLabel($product->getStoreId()),
                    'options' => $attributeOptionsData,
                    'position' => $attribute->getPosition(),
                ];
                $defaultValues[$attributeId] = $this->getAttributeConfigValueCustom($attributeId, $product);
            }
        }

        return [
            'attributes' => $attributes,
            'defaultValues' => $defaultValues,
        ];
    }

    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsDataCustom($attribute, $config)
    {
        $attributeOptionsData = [];
        $attributes = $this->getProduct()->getAttributes();
        $options = $attributes[$attribute->getProductAttribute()->getAttributeCode()]->getOptions();
        foreach ($options as $attributeOption) {
            $optionId = $attributeOption->getValue();
            if (isset($config[$attribute->getAttributeId()][$optionId])) {
                $attributeOptionsData[] = [
                    'id' => $optionId,
                    'label' => $attributeOption->getLabel(),
                    'products' => isset($config[$attribute->getAttributeId()][$optionId])
                        ? $config[$attribute->getAttributeId()][$optionId]
                        : [],
                ];
            }
        }
        return $attributeOptionsData;
    }

    /**
     * @param int $attributeId
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed|null
     */
    protected function getAttributeConfigValueCustom($attributeId, $product)
    {
        return $product->hasPreconfiguredValues()
            ? $product->getPreconfiguredValues()->getData('super_attribute/' . $attributeId) : null;
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->moduleConfig->getMagentoVersion();
    }

    /**
     * @return string
     */
    public function getRendererTemplate()
    {
        if ($this->moduleConfig->isModuleEnable()
            && $this->linkData->getEnabledModuleOnProduct($this->getProduct()->getEntityId())->getEnabled()) {
            $this->updateCustomTemplate();
            return $this->isProductHasSwatchAttribute() ?
                self::BSS_SWATCH_RENDERER_TEMPLATE : self::BSS_CONFIGURABLE_RENDERER_TEMPLATE;
        }
        return $this->isProductHasSwatchAttribute() ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }

    /**
     * Move from view/frontend/layout
     * Set template when module is enabled only
     */
    public function updateCustomTemplate()
    {
        $layout = $this->getLayout();
        if ($layout->getBlock('product.info.description')) {
            $layout->getBlock('product.info.description')
                ->setTemplate('Bss_Simpledetailconfigurable::product/description.phtml');
        }
        if ($layout->getBlock('product.attributes')) {
            $layout->getBlock('product.attributes')
                ->setTemplate('Bss_Simpledetailconfigurable::product/attributes.phtml');
        }
    }

    /**
     * @return null
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getShowSwatchTooltip()
    {
        return $this->linkData->getShowSwatchTooltip();
    }
}
