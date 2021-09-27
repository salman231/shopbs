<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Block\Adminhtml\ProductType;

class Product extends \Magento\Catalog\Block\Adminhtml\Product
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Module\Manager $manager,
        array $data = []
    ) {
        $this->moduleManager = $manager;
        $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;

        parent::__construct($context, $typeFactory, $productFactory, $data);
    }
    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );
        $giftCard = $this->moduleManager->isOutputEnabled('Webkul_GiftCard');
        foreach ($types as $typeId => $type) {
            if ($typeId == 'giftcard' && $giftCard == 0) {
                continue;
            }
            $splitButtonOptions[$typeId] = [
                'label' => __($type['label']),
                'onclick' => "setLocation('" . $this->_getProductCreateUrl($typeId) . "')",
                'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $typeId,
            ];
        }
        return $splitButtonOptions;
    }
}
