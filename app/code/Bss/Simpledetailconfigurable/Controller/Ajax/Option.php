<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Option as ModelOption;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Option extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ModelOption
     */
    protected $option;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * Option constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param ModelOption $option
     * @param \Magento\Checkout\Model\Session $session
     * @param Json $serializer
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\DataObject\Factory $objectFactory,
        ModelOption $option,
        \Magento\Checkout\Model\Session $session,
        Json $serializer
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->objectFactory = $objectFactory;
        $this->option = $option;
        $this->session = $session;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $product = '';
        $resultJson = $this->resultJsonFactory->create();

        $request = $this->getRequest()->getParams();
        if ($this->getRequest()->isAjax()) {
            $productId = $request['product_id'];
            $product = $this->productRepository->getById($productId);
        }
        $cart = $this->session->getQuote()->getAllVisibleItems();
        $infoBuyRequest = [];
        if ($cart) {
            foreach ($cart as $item) {
                if ($item->getProductType() == "configurable") {
                    $key = (int)$this->getSizeChild($item) - 1;
                    $child = $item->getChildren()[$key];
                    if ($child->getProductId() == $product->getId()) {
                        $infoBuyRequest = $child->getOptionByCode('info_buyRequest')->getData('value');
                        $infoBuyRequest = $this->serializer->unserialize($infoBuyRequest);
                    }
                }
            }
        }

        $optionsHtml = $this->getValuesHtml($product, $infoBuyRequest);
        return $resultJson->setData($optionsHtml);
    }

    /**
     * @param $item
     * @return int|void
     */
    private function getSizeChild($item)
    {
        return count($item->getChildren());
    }

    /**
     * @param $_product
     * @param $infoBuyRequest
     * @return string
     */
    public function getValuesHtml($_product, $infoBuyRequest)
    {
        if (!empty($infoBuyRequest) && isset($infoBuyRequest['options'])) {
            $preConfiguredValues = $this->objectFactory->create();
            $options = ['options' => $infoBuyRequest['options']];

            $preConfiguredValues->setData($options);
            $_product->setPreconfiguredValues($preConfiguredValues);
        }

        $blockOptionData = $this->_view->getLayout()
            ->createBlock(\Magento\Catalog\Block\Product\View\Options::class)
            ->setProduct($_product)
            ->setTemplate('Bss_Simpledetailconfigurable::product/view/simple-options.phtml');

        $types = ['select', 'text', 'file', 'date'];
        foreach ($types as $type) {
            $block_child = "Magento\\Catalog\\Block\\Product\\View\\Options\\Type\\".ucfirst($type);
            $template_child = "Magento_Catalog::product/view/options/type/".$type.'.phtml';
            $blockType = $this->_view->getLayout()->createBlock($block_child)->setTemplate($template_child);
            $blockOptionData->setChild($type, $blockType);
        }

        $option_price_renderer_block = $this->_view->getLayout()
            ->createBlock(
                \Magento\Framework\Pricing\Render::class,
                "product.price.render.default",
                [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                        'use_link_for_as_low_as' => 'true'
                    ]
                ]
            )
            ->setData('area', 'frontend');

        $blockOptionData->setChild('product.price.render.default', $option_price_renderer_block);

        if (!empty($blockOptionData->toHtml()) && strlen($blockOptionData->toHtml()) > 1) {
            $html = $blockOptionData->toHtml();
            $this->getInfoOption($_product, $html);
            return $html;
        } else {
            return '';
        }
    }

    /**
     * @param $_product
     * @param $html
     */
    private function getInfoOption($_product, &$html)
    {
        $typeDate = [
            ModelOption::OPTION_TYPE_DATE_TIME,
            ModelOption::OPTION_TYPE_DATE,
            ModelOption::OPTION_TYPE_TIME
        ];
        $typeMultiple = [
            ModelOption::OPTION_TYPE_CHECKBOX,
            ModelOption::OPTION_TYPE_MULTIPLE
        ];
        if ($_product->getOptions()) {
            $config = [];
            foreach ($_product->getOptions() as $option) {
                if (in_array($option->getType(), $typeDate)) {
                    $config['price-option-calendar-'.$option->getId()] = $_product->getId();
                } elseif ($option->getType() == ModelOption::OPTION_TYPE_FILE) {
                    $config['options_'.$option->getId().'_file'] = $_product->getId();
                } elseif (in_array($option->getType(), $typeMultiple)) {
                    if ($option->hasValues()) {
                        foreach ($option->getValues() as $value) {
                            $key = 'options['.$option->getId().'][]##' . $value->getOptionTypeId();
                            $config[$key] = $_product->getId();
                        }
                    }
                } else {
                    $config['options['.$option->getId().']'] = $_product->getId();
                }
            }
            $json_optionIds = "'".json_encode($config)."'";
            $html .= '<input type="hidden" class="bss-price-option-child-product" data-option-id='.$json_optionIds;
            $html .= ' id="bss-option-price-'.$_product->getId().'" value="" data-excltax-price="">';
        }
    }
}
