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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit;

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Class SaveAndContinueButton block
 */
class GenericButton
{
    protected $urlBuilder;
    protected $registry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context  context
     * @param \Magento\Framework\Registry           $registry registry
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * Function to get banner Image Id
     *
     * @return int
     */
    public function getBannerimageId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_BANNER_ID);
    }

    /**
     * Function to get Carusel Id
     *
     * @return int
     */
    public function getCarouselId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_CAROUSEL_ID);
    }

    /**
     * Function to get Carousel Image Id
     *
     * @return int
     */
    public function getCarouselimageId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_CAROUSELIMAGE_ID);
    }

    /**
     * Function to get Notification Id
     *
     * @return int
     */
    public function getNotificationId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_NOTIFICATION_ID);
    }

    /**
     * Function to get Featured categories Id
     *
     * @return int
     */
    public function getFeaturedcategoriesId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_FEATUREDCATEGORIES_ID);
    }

    /**
     * Function to get Category Image Id
     *
     * @return int|array
     */
    public function getCategoryimagesId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_CATEGORYIMAGES_ID);
    }

    /**
     * Function to get Url
     *
     * @return string
     */
    public function getUrl($route = "", $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
