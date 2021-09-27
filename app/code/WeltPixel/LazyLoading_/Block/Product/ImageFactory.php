<?php
namespace WeltPixel\LazyLoading\Block\Product;

use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use WeltPixel\LazyLoading\Helper\Data as LazyLoadingHelper;


/**
 * Create imageBlock from product and view.xml
 */
class ImageFactory extends \Magento\Catalog\Block\Product\ImageFactory
{
    /**
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var AssetImageFactory
     */
    private $viewAssetImageFactory;

    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PlaceholderFactory
     */
    private $viewAssetPlaceholderFactory;

    /**
     * @var LazyLoadingHelper
     */
    private $lazyLoadingHelper;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $presentationConfig
     * @param AssetImageFactory $viewAssetImageFactory
     * @param PlaceholderFactory $viewAssetPlaceholderFactory
     * @param ParamsBuilder $imageParamsBuilder
     * @param LazyLoadingHelper $lazyLoadingHelper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig,
        AssetImageFactory $viewAssetImageFactory,
        PlaceholderFactory $viewAssetPlaceholderFactory,
        ParamsBuilder $imageParamsBuilder,
        LazyLoadingHelper $lazyLoadingHelper
    ) {
        $this->objectManager = $objectManager;
        $this->presentationConfig = $presentationConfig;
        $this->viewAssetPlaceholderFactory = $viewAssetPlaceholderFactory;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->lazyLoadingHelper = $lazyLoadingHelper;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @param array $attributes
     * @return string
     */
    private function getStringCustomAttributes(array $attributes): string
    {
        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * Retrieve image class for HTML element
     *
     * @param array $attributes
     * @return string
     */
    private function getClass(array $attributes): string
    {
        return $attributes['class'] ?? 'product-image-photo';
    }

    /**
     * Calculate image ratio
     *
     * @param int $width
     * @param int $height
     * @return float
     */
    private function getRatio(int $width, int $height): float
    {
        if ($width && $height) {
            return $height / $width;
        }
        return 1.0;
    }

    /**
     * Get image label
     *
     * @param Product $product
     * @param string $imageType
     * @return string
     */
    private function getLabel(Product $product, string $imageType): string
    {
        $label = $product->getData($imageType . '_' . 'label');
        if (empty($label)) {
            $label = $product->getName();
        }
        return (string) $label;
    }

    /**
     * Create image block from product
     *
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     * @return ImageBlock
     */
    public function create(Product $product, string $imageId, array $attributes = null): ImageBlock
    {
        $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );

        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        $originalFilePath = $product->getData($imageMiscParams['image_type']);

        if ($originalFilePath === null || $originalFilePath === 'no_selection') {
            $imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type']
                ]
            );
        } else {
            $imageAsset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath' => $originalFilePath,
                ]
            );
        }

        $data = [
            'data' => [
                'template' => 'Magento_Catalog::product/image_with_borders.phtml',
                'image_url' => $imageAsset->getUrl(),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
                'class' => $this->getClass($attributes),
                'product_id' => $product->getId()
            ],
        ];

        /** Check if module is enabled */
        if (!$this->lazyLoadingHelper->isEnabled()) {
            return $this->objectManager->create(ImageBlock::class, $data);
        }

        $data['data']['template'] = 'WeltPixel_LazyLoading::product/image_with_borders.phtml';

        return $this->objectManager->create(ImageBlock::class, $data);
    }
}