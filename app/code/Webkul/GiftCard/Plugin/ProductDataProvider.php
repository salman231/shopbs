<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Plugin;

/**
 * Class ProductDataProvider.
 * product data provider after get meta
 */
class ProductDataProvider
{
    public function afterGetMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider $subject,
        $result
    ) {
    
        $productType = $result['add_attribute_modal']['children']['create_new_attribute_modal']
        ['children']['product_attribute_add_form']['arguments']['data']['config']['productType'];
        if ($productType == 'giftcard') {
            unset($result['custom_options']);
            return $result;
        } else {
            return $result;
        }
    }
}
