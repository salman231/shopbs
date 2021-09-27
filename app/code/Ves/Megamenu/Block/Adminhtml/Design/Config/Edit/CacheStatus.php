<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Block\Adminhtml\Design\Config\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CacheStatus implements ButtonProviderInterface
{
    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $helper;

    protected $urlBuilder;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context 
     * @param \Ves\Megamenu\Helper\Data             $helper  
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Ves\Megamenu\Helper\Data $helper
    ) {
        $this->helper     = $helper;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($this->helper->getConfig('general_settings/enable_cache')) {
            $title = __('Mega Menu Cache Status: Enabled');
        } else {
            $title = __('Mega Menu Cache Status: Disabled');
        }

        return [
            'label'    => $title,
            'class'    => 'cache-button',
            'on_click' => sprintf("javascript:void()"),
            'style' => 'padding-right: 0; border-right: 0;'
        ];
    }

    public function getFlushCacheUrl()
    {
        return $this->urlBuilder->getUrl('vesmegamenu/menu/flushCache');
    }
}
