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

namespace Mageplaza\BetterProductReviews\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class Uninstall
 *
 * @package Mageplaza\BetterProductReviews\Setup
 */
class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(Unused)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->dropColumn($setup->getTable('review_detail'), 'mp_bpr_images');
        $setup->getConnection()->dropColumn($setup->getTable('review_detail'), 'mp_bpr_recommended_product');
        $setup->getConnection()->dropColumn($setup->getTable('review_detail'), 'mp_bpr_verified_buyer');
        $setup->getConnection()->dropColumn($setup->getTable('review_detail'), 'mp_bpr_helpful');
    }
}
