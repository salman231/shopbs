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

namespace Mageplaza\DailyDeal\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;
use Mageplaza\DailyDeal\Block\Product\View\Label;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class Pages
 * @package Mageplaza\DailyDeal\Block
 */
class Pages extends ListProduct
{
    /**
     * @var HelperData
     */
    public $_helperData;

    /**
     * @var Label
     */
    protected $_label;

    /**
     * Pages constructor.
     *
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param HelperData $helperData
     * @param Label $label
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        HelperData $helperData,
        Label $label,
        array $data = []
    ) {
        $this->_helperData = $helperData;
        $this->_label      = $label;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /**
     * page config
     *
     * @param string $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPageConfig($field = '', $storeId = null)
    {
        $field = ($field !== '') ? '/' . $field : '';

        return $this->_helperData->getModuleConfig('deal_pages' . $field, $storeId);
    }

    /**
     * Check position can show link
     *
     * @param $position
     *
     * @return bool
     */
    public function canShowLink($position)
    {
        $positionConfig = explode(',', $this->getShowLinksConfig());

        return in_array($position, $positionConfig);
    }

    /**
     * Get Page Deal Url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPageUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();

        return $baseUrl . $this->getRoute() . $this->_helperData->getUrlSuffix();
    }

    /**
     * @return Label
     */
    public function label()
    {
        return $this->_label;
    }
}
