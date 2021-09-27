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

namespace Mageplaza\DailyDeal\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Mageplaza\DailyDeal\Block\Pages\AllDeals;
use Mageplaza\DailyDeal\Block\Pages\BestsellerDeals;
use Mageplaza\DailyDeal\Block\Pages\FeaturedDeals;
use Mageplaza\DailyDeal\Block\Pages\NewDeals;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class Router
 * @package Mageplaza\DailyDeal\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var AllDeals
     */
    protected $_allDeals;

    /**
     * @var NewDeals
     */
    protected $_newDeals;

    /**
     * @var BestsellerDeals
     */
    protected $_sellerDeals;

    /**
     * @var FeaturedDeals
     */
    protected $_featuredDeals;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param HelperData $helperData
     * @param AllDeals $allDeals
     * @param NewDeals $newDeals
     * @param BestsellerDeals $sellerDeals
     * @param FeaturedDeals $featureDeal
     */
    public function __construct(
        ActionFactory $actionFactory,
        HelperData $helperData,
        AllDeals $allDeals,
        NewDeals $newDeals,
        BestsellerDeals $sellerDeals,
        FeaturedDeals $featureDeal
    ) {
        $this->actionFactory  = $actionFactory;
        $this->_helperData    = $helperData;
        $this->_allDeals      = $allDeals;
        $this->_newDeals      = $newDeals;
        $this->_sellerDeals   = $sellerDeals;
        $this->_featuredDeals = $featureDeal;
    }

    /**
     * @param RequestInterface $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $routePath  = explode('/', $identifier);
        $urlSuffix  = $this->_helperData->getUrlSuffix();

        if (!$this->_helperData->isEnabled() || (count($routePath) !== 1)) {
            return null;
        }

        $route = $routePath[0];

        if ($urlSuffix) {
            if (strpos($route, $urlSuffix) !== false) {
                $pos   = strpos($route, $urlSuffix);
                $route = substr($route, 0, $pos);
            } else {
                return null;
            }
        }

        switch ($route) {
            case $this->_allDeals->getRoute():
                if (!$this->_allDeals->isEnable()) {
                    return null;
                }
                $request->setModuleName('dailydeal')
                    ->setControllerName('pages')
                    ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier)
                    ->setActionName('alldeals')->setPathInfo('/dailydeal/pages/alldeals');
                break;
            case $this->_newDeals->getRoute():
                if (!$this->_newDeals->isEnable()) {
                    return null;
                }
                $request->setModuleName('dailydeal')
                    ->setControllerName('pages')
                    ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier)
                    ->setActionName('newdeals')->setPathInfo('/dailydeal/pages/newdeals');
                break;

            case $this->_sellerDeals->getRoute():
                if (!$this->_sellerDeals->isEnable()) {
                    return null;
                }
                $request->setModuleName('dailydeal')
                    ->setControllerName('pages')
                    ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier)
                    ->setActionName('bestsellerdeals')->setPathInfo('/dailydeal/pages/bestsellerdeals');
                break;
            case $this->_featuredDeals->getRoute():
                if (!$this->_featuredDeals->isEnable()) {
                    return null;
                }
                $request->setModuleName('dailydeal')
                    ->setControllerName('pages')
                    ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier)
                    ->setActionName('featureddeals')->setPathInfo('/dailydeal/pages/featureddeals');
                break;
            default:
                return null;
        }

        return $this->actionFactory->create(Forward::class);
    }
}
