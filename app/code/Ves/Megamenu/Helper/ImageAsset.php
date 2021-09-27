<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2018 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Megamenu\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImageAsset extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
      protected $_filesystem;

      protected $_processor;
    /** \Magento\Catalog\Helper\Image */
    protected $_imageFactory;
    protected $_storeManager;

      /**
       * @var bool
       */
      protected $_keepFrame = true;

      /**
       * @var bool
       */
      protected $_keepTransparency = true;

      /**
       * @var bool
       */
      protected $_constrainOnly = true;

      /**
       * @var int[]
       */
      protected $_backgroundColor = [255, 255, 255];
      /** \Magento\Catalog\Helper\Image */
    protected $_imageHelper;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Image\Factory $imageFactory,
        \Magento\Catalog\Helper\Image $imageHelper
		){
		$this->_storeManager = $storeManager;
		$this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->_imageHelper = $imageHelper;
        
		parent::__construct($context);
	}

	public function getBaseMediaDirPath() {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

	public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepAspectRatio($keep)
    {
        $this->_keepAspectRatio = (bool)$keep;
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepFrame($keep)
    {
        $this->_keepFrame = (bool)$keep;
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepTransparency($keep)
    {
        $this->_keepTransparency = (bool)$keep;
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setConstrainOnly($flag)
    {
        $this->_constrainOnly = (bool)$flag;
        return $this;
    }

    /**
     * @param int[] $rgbArray
     * @return $this
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->_backgroundColor = $rgbArray;
        return $this;
    }
  /**
     * @return MagentoImage
     */
    public function getImageProcessor($_imageUrl = null, $qualtity = 100, $keep_ratio = true)
    {
        //if (!$this->_processor) {
        $this->_processor = $this->_imageFactory->create($_imageUrl);
       // }
        $this->_processor->keepAspectRatio($keep_ratio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($qualtity);

        return $this->_processor;
  }

	public function resizeImage($image, $width = 100, $height = 100, $qualtity = 100, $keep_ratio = true){
        if(!$image || $image == "") return;

        $parsed = parse_url($image);
        if (!empty($parsed['scheme'])) {
            return $image;
        }
        
        if($width == 0 || $height == 0) {
            return $this->getBaseMediaUrl().$image;
        }
        $media_base_url = $this->getBaseMediaUrl();
        $image = str_replace($media_base_url, "", $image);
        $media_base_url = str_replace("https://","http://", $media_base_url);
        $image = str_replace($media_base_url, "", $image);

        $_imageUrl = $this->getBaseMediaDirPath().DIRECTORY_SEPARATOR.$image;
        $_imageResized = $this->getBaseMediaDirPath().DIRECTORY_SEPARATOR."resized".DIRECTORY_SEPARATOR.(int)$width."x".(int)$height.DIRECTORY_SEPARATOR.$image;

        if (!file_exists($_imageResized)&&file_exists($_imageUrl)){
            $imageObj = $this->getImageProcessor($_imageUrl, $qualtity, $keep_ratio);
            if($keep_ratio) {
                $imageObj->resize((int)$width, (int)$height);
            } else {
                $imageObj->keepAspectRatio(FALSE);
                $currentRatio = $imageObj->getOriginalWidth() / $imageObj->getOriginalHeight();
                $targetRatio = $width / $height;
                if ($targetRatio > $currentRatio) {
                    $imageObj->resize($width, null);
                } else {
                    $imageObj->resize(null, $height);
                }

                $diffWidth  = $imageObj->getOriginalWidth() - $width;
                $diffHeight = $imageObj->getOriginalHeight() - $height;

                /*POSTION Bottom*/
                $_topRate = 1;
                $_bottomRate = 0;
                /*
                  //POSTION Top
                  $_topRate = 0;
                  $_bottomRate = 1;
                  */
                  /*
                  //POSTION Center
                  $_topRate = 0.5;
                  $_bottomRate = 0.5;
                */
                $imageObj->crop(
                  floor($diffHeight * $_topRate),
                  floor($diffWidth / 2),
                  ceil($diffWidth / 2),
                  ceil($diffHeight * $_bottomRate)
                );
            }
            $imageObj->save($_imageResized);
        }
        return $this->getBaseMediaUrl()."resized/".(int)$width."x".(int)$height."/".$image;
    }

    /**
     * Get image URL of the given product
     *
     * @param \Magento\Catalog\Model\Product    $product        Product
     * @param int                               $w              Image width
     * @param int                               $h              Image height
     * @param string                            $imgVersion     Image version: image, small_image, thumbnail
     * @param mixed                             $file           Specific file
     * @return string
     */
    public function getImg($product, $w=300, $h, $imgVersion='image', $file=NULL)
    {
        if ($h <= 0){
            $image = $this->_imageHelper
            ->init($product, $imgVersion)
            ->constrainOnly(true)
            ->keepAspectRatio(true)
            ->keepFrame(false);
            if($file){
                $image->setImageFile($file);
            }
            if($w){
              $image->resize($w);
            }
            return $image;
        }else{
            $image = $this->_imageHelper
            ->init($product, $imgVersion);
            if($file){
                $image->setImageFile($file);
            }
            if($w){
              $image->resize($w, $h);
            }
            
            return $image;
        }
    }
    /**
     * Get alternative image HTML of the given product
     *
     * @param \Magento\Catalog\Model\Product    $product        Product
     * @param int                               $w              Image width
     * @param int                               $h              Image height
     * @param string                            $imgVersion     Image version: image, small_image, thumbnail
     * @return string
     */
    public function getAltImgHtml($product, $w, $h, $imgVersion='small_image', $column = 'position', $value = 1)
    {
        $product->load('media_gallery');
        if ($images = $product->getMediaGalleryImages())
        {
            $image = $images->getItemByColumnValue($column, $value);
            if(isset($image) && $image->getUrl()){
                $imgAlt = $this->getImg($product, $w, $h, $imgVersion , $image->getFile());
                if(!$imgAlt) return '';
                return $imgAlt;
            }else{
                return '';
            }
        }
        return '';
    }

    public function resizeThumb($image, $width = 100, $height = 0, $qualtity = 100, $keep_ratio = true){
        if($image=='') return;
        $media_base_url = $this->getBaseMediaUrl();
        $image = str_replace($media_base_url, "", $image);
        $media_base_url = str_replace("https://","http://", $media_base_url);
        $image = str_replace($media_base_url, "", $image);
        $_imageUrl = $this->getBaseMediaDirPath().DIRECTORY_SEPARATOR.$image;
        $_imageResized = $this->getBaseMediaDirPath().DIRECTORY_SEPARATOR."resized".DIRECTORY_SEPARATOR.(int)$width."x".(int)$height.DIRECTORY_SEPARATOR.$image;
        if (!file_exists($_imageResized)&&file_exists($_imageUrl)){
            $imageObj = $this->getImageProcessor($_imageUrl, $qualtity, $keep_ratio);
            if($height == 0){
                $this->_keepFrame = false;
                $imageObj->resize($width);
            }else{
                $this->_keepFrame = false;
                $imageObj->resize($width, $height);
            }
            $imageObj->save($_imageResized);
        }
        return $this->getBaseMediaUrl()."resized/".(int)$width."x".(int)$height."/".$image;
    }

}