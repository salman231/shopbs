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
namespace Webkul\GiftCard\Model\Product\Type;

class GiftCard extends \Magento\Catalog\Model\Product\Type\Virtual
{
    const TYPE_ID = "giftcard";
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
}
