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

namespace Mageplaza\BetterProductReviews\Model\ResourceModel\Reply;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Mageplaza\BetterProductReviews\Model\ResourceModel\Reply
 */
class Collection extends AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'reply_id';

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Mageplaza\BetterProductReviews\Model\Reply',
            'Mageplaza\BetterProductReviews\Model\ResourceModel\Reply'
        );
    }
}
