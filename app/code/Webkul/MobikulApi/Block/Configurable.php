<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Block;

/**
 * Block Class Configurable
 */
class Configurable extends \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
{
    /**
     * Function getJsonConfi to get config in jSon
     *
     * @return array
     */
    public function getJsonConfig()
    {
        $objectManager         = \Magento\Framework\App\ObjectManager::getInstance();
        $store                 = $this->getCurrentStore();
        $product               = $this->getProduct();
        $swatchBlock           = $objectManager->create(\Magento\Swatches\Block\Product\Renderer\Configurable::class);
        $swatchBlock->setProduct($product);
        $regularProductPrice   = $product->getPriceInfo()->getPrice("regular_price");
        $finalProductPrice     = $product->getPriceInfo()->getPrice("final_price");
        $productOptions        = $this->helper->getOptions($product, $this->getAllowProducts());
        $attributesData        = $objectManager->create(
            \Webkul\MobikulCore\Model\ConfigurableProduct\ConfigurableAttributeData::class
        )->getAttributesData($product, $productOptions);
        $costomizedAttr        = [];
        $customizedIndex       = [];
        $customizeOptionPrizes = [];
        $optionPrizes          = $this->getOptionPrices();
        if (count($attributesData["attributes"]) > 0) {
            foreach ($attributesData["attributes"] as $value) {
                $costomizedAttr[] = $value;
            }
        }
        if (isset($productOptions["index"])) {
            foreach ($productOptions["index"] as $index => $indexValue) {
                $indexValue["product"] = $index;
                $customizedIndex[]     = $indexValue;
            }
        }
        if (isset($optionPrizes)) {
            foreach ($optionPrizes as $index => $optionPrice) {
                $optionPrice["product"]  = $index;
                $customizeOptionPrizes[] = $optionPrice;
            }
        }
        $optionPrizes                 = $customizeOptionPrizes;
        $productOptions["index"]      = $customizedIndex;
        $attributesData["attributes"] = $costomizedAttr;
        $jsonHelper                   = $objectManager->create(\Magento\Framework\Json\Helper\Data::class);
        $images                       = $this->getImages();
        $index                        = isset($productOptions["index"])  ? $productOptions["index"]  : [];
        $images                       = (!empty($images))?$jsonHelper->jsonEncode($images):"{}";
        $index                        = $jsonHelper->jsonEncode($index);
        $config                       = [
            "attributes"     => $attributesData["attributes"],
            "template"       => str_replace("%s", "<%- data.price %>", $store->getCurrentCurrency()->getOutputFormat()),
            "optionPrices"   => $optionPrizes,
            "prices"         => [
                "oldPrice"   => ["amount"=>$this->_registerJsPrice($regularProductPrice->getAmount()->getValue())],
                "basePrice"  => ["amount"=>$this->_registerJsPrice($finalProductPrice->getAmount()->getBaseAmount())],
                "finalPrice" => ["amount"=>$this->_registerJsPrice($finalProductPrice->getAmount()->getValue())]
            ],
            "productId"      => $product->getId(),
            "chooseText"     => __("Choose an Option..."),
            "images"         => $images,
            "index"          => $index,
            "swatchData"     => $swatchBlock->getJsonSwatchConfig()
        ];
        if ($product->hasPreconfiguredValues() && !empty($attributesData["defaultValues"])) {
            $config["defaultValues"] = $attributesData["defaultValues"];
        }
        $config = array_merge($config, $this->_getAdditionalConfig());
        return $config;
    }

    /**
     * Function getJsonConfi to get config in jSon
     *
     * @return array
     */
    public function getJsonConfig1()
    {
        $objectManager         = \Magento\Framework\App\ObjectManager::getInstance();
        $store                 = $this->getCurrentStore();
        $product               = $this->getProduct();
        $swatchBlock           = $objectManager->create(\Magento\Swatches\Block\Product\Renderer\Configurable::class);
        $swatchBlock->setProduct($product);
        $regularProductPrice   = $product->getPriceInfo()->getPrice("regular_price");
        $finalProductPrice     = $product->getPriceInfo()->getPrice("final_price");
        $productOptions        = $this->helper->getOptions($product, $this->getAllowProducts());
        $attributesData        = $objectManager->create(
            \Webkul\MobikulCore\Model\ConfigurableProduct\ConfigurableAttributeData::class
        )->getAttributesData($product, $productOptions);
        $costomizedAttr        = [];
        $customizedIndex       = [];
        $customizeOptionPrizes = [];
        $optionPrizes          = $this->getOptionPrices();
        if (count($attributesData["attributes"]) > 0) {
            foreach ($attributesData["attributes"] as $value) {
                $costomizedAttr[] = $value;
            }
        }
        if (isset($productOptions["index"])) {
            foreach ($productOptions["index"] as $index => $indexValue) {
                $indexValue["product"] = $index;
                $customizedIndex[]     = $indexValue;
            }
        }
        if (isset($optionPrizes)) {
            foreach ($optionPrizes as $index => $optionPrice) {
                $optionPrice["product"]  = $index;
                $customizeOptionPrizes[] = $optionPrice;
            }
        }
        $optionPrizes                 = $customizeOptionPrizes;
        $productOptions["index"]      = $customizedIndex;
        $attributesData["attributes"] = $costomizedAttr;
        $jsonHelper                   = $objectManager->create(\Magento\Framework\Json\Helper\Data::class);
        $images                       = $this->getImages();
        $index                        = isset($productOptions["index"])  ? $productOptions["index"]  : [];
        $images                       = (!empty($images))?$jsonHelper->jsonEncode($images):"{}";
        $index                        = $jsonHelper->jsonEncode($index);
        $config                       = [
            "attributes"     => $attributesData["attributes"],
            "template"       => str_replace("%s", "<%- data.price %>", $store->getCurrentCurrency()->getOutputFormat()),
            "optionPrices"   => $optionPrizes,
            "prices"         => [
                "oldPrice"   => ["amount"=>$this->_registerJsPrice($regularProductPrice->getAmount()->getValue())],
                "basePrice"  => ["amount"=>$this->_registerJsPrice($finalProductPrice->getAmount()->getBaseAmount())],
                "finalPrice" => ["amount"=>$this->_registerJsPrice($finalProductPrice->getAmount()->getValue())]
            ],
            "productId"      => $product->getId(),
            "chooseText"     => __("Choose an Option..."),
            "images"         => $images,
            "index"          => $index,
            "swatchData"     => $swatchBlock->getJsonSwatchConfig()
        ];
        if ($product->hasPreconfiguredValues() && !empty($attributesData["defaultValues"])) {
            $config["defaultValues"] = $attributesData["defaultValues"];
        }
        $config = array_merge($config, $this->_getAdditionalConfig());
        return $config;
    }

    public function getImages()
    {
        $options = [];
        $allowedProducts = $this->getAllowProducts();
        foreach ($allowedProducts as $product) {
            $images = $this->helper->getGalleryImages($product);
            $productId = $product->getId();
            if ($images) {
                foreach ($images as $image) {
                    $isVideo   = false;
                    $videoUrl  = "";
                    if ($image->getMediaType() == "external-video") {
                        $isVideo   = true;
                        $videoUrl  = $image->getVideoUrl();
                    }
                    $options[$productId][] =
                        [
                            'thumb' => $image->getData('small_image_url'),
                            'img' => $image->getData('medium_image_url'),
                            'full' => $image->getData('large_image_url'),
                            'caption' => $image->getLabel(),
                            'position' => $image->getPosition(),
                            'isMain' => $image->getFile() == $product->getImage(),
                            'isVideo' => $isVideo,
                            'videoUrl' => $videoUrl
                        ];
                }
            }
        }
        return $options;
    }
}
