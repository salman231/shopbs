<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Block\Adminhtml\Product;

/**
 * Class Texture Image block
 */
class TextureImage extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = "product/textureimage.phtml";

    /**
     * Construct function for Texturemage Class
     *
     * @param \Magento\Backend\Block\Template\Context $context      context
     * @param \Magento\Framework\Registry             $coreRegistry coreRegistry
     * @param array                                   $data         data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Function to get Texture image data
     *
     * @return false|string
     */
    public function getTextureImageData()
    {
        $product = $this->coreRegistry->registry('product');
        $proType = $this->getRequest()->getParam('type');
        $proType = $proType ? $proType : $product->getTypeId();
        $arIosModel = "";
        $arAndroidModel = "";
        $arIosModelArr = explode("/", $product->getArModelFileIos());
        $arAndroidModelArr = explode("/", $product->getArModelFileAndroid());
        if (!empty($arAndroidModelArr)) {
            $arAndroidModel = $arAndroidModelArr[count($arAndroidModelArr) - 1];
        }
        if (!empty($arIosModelArr)) {
            $arIosModel = $arIosModelArr[count($arIosModelArr) - 1];
        }
        $arData = [
            "product_type" => $proType,
            "ar_type" => $product->getArType(),
            "texture_images" => $product->getArTextureImage(),
            "ar_model_file_ios_model" => $arIosModel,
            "ar_model_file_android_model" => $arAndroidModel
        ];
        return $arData;
    }
}
