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

namespace Mageplaza\BetterProductReviews\Block\Adminhtml\Review\Edit\Form\Renderer;

use Exception;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;

/**
 * Class Images
 *
 * @package Mageplaza\BetterProductReviews\Block\Adminhtml\User\Edit\Tab\Renderer
 */
class Images extends AbstractElement
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Layout
     */
    protected $_layout;

    /**
     * Images constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Registry $coreRegistry
     * @param Layout $layout
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Registry $coreRegistry,
        Layout $layout,
        $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_layout = $layout;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('mp_bpr_images');
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $html .= $this->_layout
            ->createBlock('Mageplaza\BetterProductReviews\Block\Adminhtml\Review\Edit\Form\Images')
            ->setTemplate('Mageplaza_BetterProductReviews::review/form/gallery.phtml')
            ->setId('media_gallery_content')
            ->setElement($this)
            ->setFormName('edit_form')
            ->toHtml();

        return $html;
    }

    /**
     * @return mixed
     */
    public function getDataObject()
    {
        return $this->_coreRegistry->registry('review_data');
    }

    /**
     * Get product images
     *
     * @return array|null
     */
    public function getImages()
    {
        $images = ($this->getDataObject()) ? $this->getDataObject()->getMpBprImages() : '';
        if ($images) {
            try {
                $images = HelperData::jsonDecode($images);
            } catch (Exception $e) {
                $images = [];
            }
        } else {
            $images = [];
        }

        return $images;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'images';
    }
}
