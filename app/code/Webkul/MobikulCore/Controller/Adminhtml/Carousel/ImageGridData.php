<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Carousel;

/**
 * Class ImageGridData for Carousel
 */
class ImageGridData extends \Magento\Backend\App\Action
{
    protected $resultLayoutFactory;

    /**
     * Construct function
     *
     * @param \Magento\Backend\App\Action\Context          $context             context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Execute Function for Class ImageGridData
     *
     * @return jSon
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $this->getResponse()->setBody(
            $resultLayout->getLayout()->createBlock(
                \Webkul\MobikulCore\Block\Adminhtml\Edit\Carousel\Tab\Carouselimages::class
            )->toHtml()
        );
    }
}
