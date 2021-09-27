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
class Membership extends \Magento\Framework\View\Element\Template
{
       /**
     * Membership factory
     *
     * @var \Magedelight\MembershipSubscription\Model\MembershipProductsFactory
     */
    protected $_MembershipProductsFactory;
     /**
     *
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    
     /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_MembershipProductsFactory = $MembershipProductsFactory;
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context);
    }
    /**
     * Get current currency symbol
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
    }

     /**
     *
     * @return type
     */
    public function getMembershipOptions($productId)
    {
        $currencySymbol = $this->getCurrentCurrencySymbol();

        $durationOption = [];
        
        $durationArray = $this->getDurationArray($productId);
        
        if (count($durationArray)>0) {
            foreach ($durationArray as $key => $value) {
                $durationOption[$key]['price'] = $currencySymbol.$value['price'];
                $durationOption[$key]['label'] = $value['duration']." ".$value['duration_unit']." - ".$currencySymbol.$value['price'];
                //$durationOption[$key]['value'] = serialize($durationArray[$key]);
            }
        }
        
        return $durationOption;
    }

    /**
     *
     * @return array
     */
    public function getDurationArray($productId)
    {
        //$productId = $this->getProductId();
        
        if ($productId) {
            $model = $this->_MembershipProductsFactory->create();
            $model->load($productId, 'product_id');
            $membershipDuration = $model->getMembershipDuration();
            
            $durationArray = unserialize($membershipDuration);
            if (count($durationArray)>0) {
                //$newDurations = $this->arraySortByColumn($durationArray, 'sort_order', SORT_ASC);
                if (count($durationArray)>0) {
                    return $durationArray;
                }
            }
        }
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
