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

use Magento\Backend\Block\Media\Uploader as MediaUploader;

/**
 * Class Uploader
 * @package Mageplaza\StoreLocator\Block\Adminhtml\Media
 */
class Uploader extends MediaUploader
{
    /**
     * @return $this
     */
    public function getImageUploadConfigData()
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsResizeEnabled()
    {
        return true;
    }
}
