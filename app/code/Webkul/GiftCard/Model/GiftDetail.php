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
namespace Webkul\GiftCard\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class GiftDetail extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Webkul\GiftCard\Model\ResourceModel\GiftDetail::class);
    }
}
