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

namespace Mageplaza\DailyDeal\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Render\FinalPriceBox as CatalogFinalPriceBox;
use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\DailyDeal\Helper\Data;

/**
 * Class FinalPriceBox
 * @package Mageplaza\DailyDeal\Pricing\Render
 */
class FinalPriceBox extends CatalogFinalPriceBox
{
    /**
     * @var AmountFactory
     */
    protected $amountFactory;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * FinalPriceBox constructor.
     *
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param Data $helperData
     * @param AmountFactory $amountFactory
     * @param array $data
     * @param SalableResolverInterface|null $salableResolver
     * @param MinimalPriceCalculatorInterface|null $minimalPriceCalculator
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        Data $helperData,
        AmountFactory $amountFactory,
        array $data = [],
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );

        $this->helperData    = $helperData;
        $this->amountFactory = $amountFactory;
    }

    /**
     * @param $price
     *
     * @return AmountInterface
     */
    public function getDealPriceAmount($price)
    {
        return $this->amountFactory->create($price);
    }
}
