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
 * Class ProductGridData for Carousel
 */
class ProductGridData extends \Magento\Backend\App\Action
{
    protected $resultLayoutFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Execute Fucntion for Class ProductGridData
     *
     * @return jSon
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $this->getResponse()->setBody(
            $resultLayout->getLayout()->createBlock(
                \Webkul\MobikulCore\Block\Adminhtml\Edit\Carousel\Tab\Carouselproducts::class
            )->toHtml()
        );
    }
}
