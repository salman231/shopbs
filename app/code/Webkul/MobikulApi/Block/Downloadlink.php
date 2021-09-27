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

use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Block class DownloadLink
 */
class Downloadlink extends \Magento\Framework\View\Element\Template
{
    const COOKIE_NAME = "downloadlink";
    public $urlHelper;
    protected $helper;
    public $jsonHelper;
    protected $assetRepo;
    protected $cookieManager;

    /**
     * Constructor function for Downloadlinkn class
     *
     * @param \Magento\Framework\Url                           $urlHelper     urlHelper
     * @param \Webkul\MobikulCore\Helper\Data                      $helper        helper
     * @param CookieManagerInterface                           $cookieManager cookieManager
     * @param \Magento\Framework\Json\Helper\Data              $jsonHelper    jsonHelper
     * @param \Magento\Framework\View\Element\Template\Context $context       context
     * @param array                                            $data          data
     */
    public function __construct(
        \Magento\Framework\Url $urlHelper,
        CookieManagerInterface $cookieManager,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->urlHelper = $urlHelper;
        $this->assetRepo = $context->getAssetRepository();
        $this->jsonHelper = $jsonHelper;
        $this->cookieManager = $cookieManager;
        parent::__construct($context, $data);
    }

    /**
     * Function to get Play store image URL
     *
     * @return string
     */
    public function getPlaystoreImageUrl()
    {
        return $this->assetRepo->getUrl("Webkul_MobikulApi::images/google-play.png");
    }

    /**
     * Function to get app store image url
     *
     * @return string
     */
    public function getAppstoreImageUrl()
    {
        return $this->assetRepo->getUrl("Webkul_MobikulApi::images/app-store.png");
    }

    /**
     * Function to get close Button Url
     *
     * @return string
     */
    public function getCloseButtonImageUrl()
    {
        return $this->assetRepo->getUrl("Webkul_MobikulApi::images/close.png");
    }

    /**
     * Function to get app store Url
     *
     * @return string
     */
    public function getAppstoreUrl()
    {
        return $this->helper->getConfigData("mobikul/appdownload/ioslink");
    }

    /**
     * Function to get theme name
     *
     * @return string
     */
    public function getThemeName()
    {
        return $this->helper->getConfigData("mobikul/appdownload/downloadlinktheme");
    }

    /**
     * Function to check the display status of the cookie name
     *
     * @return bool
     */
    public function toShow()
    {
        return (bool)$this->cookieManager->getCookie(self::COOKIE_NAME);
    }
}
