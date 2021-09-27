<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Block\Adminhtml\Review\Edit\Form;

use Exception;
use Magento\Backend\Block\Media\Uploader;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Element\AbstractBlock;
use Mageplaza\BetterProductReviews\Helper\Data;
use Mageplaza\BetterProductReviews\Helper\Image;

/**
 * Class Images
 *
 * @package Mageplaza\BetterProductReviews\Block\Adminhtml\Review\Edit\Form
 */
class Images extends Widget
{
    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Images constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param Image $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Image $imageHelper,
        array $data = []
    ) {
        $this->_imageHelper = $imageHelper;
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild('uploader', 'Mageplaza\BetterProductReviews\Block\Adminhtml\Review\Edit\Form\Uploader');

        if ($this->helperData->versionCompare('2.3.5')) {
            $uploadUrl = $this->_urlBuilder->getUrl('mpbetterproductreviews/review/upload');
        } else {
            $uploadUrl = $this->_urlBuilder->addSessionParam()->getUrl('mpbetterproductreviews/review/upload');
        }

        $this->getUploader()->getConfig()->setUrl($uploadUrl)->setFileField(
            'image'
        )->setFilters([
            'images' => [
                'label' => __('Images (.gif, .jpg, .png)'),
                'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
            ],
        ]);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader block
     *
     * @return bool|Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    /**
     * @return string
     */
    public function getImagesJson()
    {
        $value = $this->getElement()->getImages();
        if (is_array($value) && !empty($value)) {
            $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $images = $this->sortImagesByPosition($value);
            foreach ($images as $key => &$image) {
                $image['url'] = $this->_imageHelper
                        ->getBaseMediaUrl() . '/' . $this->_imageHelper->getMediaPath($image['file']);
                try {
                    $fileHandler = $mediaDir->stat($this->_imageHelper->getMediaPath($image['file']));
                    $image['size'] = $fileHandler['size'];
                } catch (Exception $e) {
                    $this->_logger->warning($e);
                    unset($images[$key]);
                }
            }

            return Data::jsonEncode($images);
        }

        return '[]';
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     *
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort(
                $images,
                function ($imageA, $imageB) {
                    return ($imageA['position'] < $imageB['position']) ? -1 : 1;
                }
            );
        }

        return $images;
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        return [
            'image' => [
                'code' => 'images',
                'value' => ($this->getElement()->getDataObject())
                    ? $this->getElement()->getDataObject()->getImages() : '',
                'label' => 'Template Images',
                'scope' => 'Template Images',
                'name' => 'template-images',
            ]
        ];
    }

    /**
     * Retrieve JSON data
     *
     * @return string
     */
    public function getImageTypesJson()
    {
        return '[]';
    }
}
