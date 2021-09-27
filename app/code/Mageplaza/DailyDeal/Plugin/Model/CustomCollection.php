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
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Plugin\Model;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\RequestInterface;
use Mageplaza\DailyDeal\Block\Widget\AllDeal;
use Mageplaza\DailyDeal\Block\Widget\FeatureDeal;
use Mageplaza\DailyDeal\Block\Widget\NewDeal;
use Mageplaza\DailyDeal\Block\Widget\TopSellingDeal;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Zend_Db_Expr;

/**
 * Class CustomLayer
 * @package Mageplaza\DailyDeal\Plugin\Model
 */
class CustomCollection
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var AllDeal
     */
    protected $_all;

    /**
     * @var NewDeal
     */
    protected $_new;

    /**
     * @var TopSellingDeal
     */
    protected $_seller;

    /**
     * @var FeatureDeal
     */
    protected $_feature;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * CustomCollection constructor.
     *
     * @param HelperData $helperData
     * @param AllDeal $all
     * @param NewDeal $new
     * @param TopSellingDeal $seller
     * @param FeatureDeal $feature
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helperData,
        AllDeal $all,
        NewDeal $new,
        TopSellingDeal $seller,
        FeatureDeal $feature,
        RequestInterface $request
    ) {
        $this->_helperData = $helperData;
        $this->_all        = $all;
        $this->_new        = $new;
        $this->_seller     = $seller;
        $this->_feature    = $feature;
        $this->_request    = $request;
    }

    /**
     * @param $subject
     * @param Collection $collection
     *
     * @return mixed
     * @throws Exception
     */
    public function afterGetProductCollection($subject, $collection)
    {
        if (!$this->_helperData->isEnabled()) {
            return $collection;
        }

        $fullActionName = $this->_request->getFullActionName();
        switch ($fullActionName) {
            case 'dailydeal_pages_alldeals':
                $productIds = $this->_helperData->getProductIdsParent($this->_all->getProductIdsRandomDeal());
                break;
            case 'dailydeal_pages_newdeals':
                $productIds = $this->_helperData->getProductIdsParent($this->_new->getProductIds());
                break;
            case 'dailydeal_pages_bestsellerdeals':
                $productIds = $this->_helperData->getProductIdsParent($this->_seller->getProductIdsSellingDeal());
                break;
            case 'dailydeal_pages_featureddeals':
                $productIds = $this->_helperData->getProductIdsParent($this->_feature->getProductIds());
                break;
            default:
                return $collection;
        }

        $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        if ($productIds) {
            if ($fullActionName === 'dailydeal_pages_newdeals') {
                $collection->getSelect()
                    ->order(new Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $productIds) . ')'));
            }
            if ($fullActionName === 'dailydeal_pages_bestsellerdeals') {
                $collection->getSelect()
                    ->order(new Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $productIds) . ')'));
            }
        } else {
            return $collection;
        }

        return $collection;
    }
}
