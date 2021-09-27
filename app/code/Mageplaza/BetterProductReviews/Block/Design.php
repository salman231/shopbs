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

namespace Mageplaza\BetterProductReviews\Block;

use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;

/**
 * Class Design
 *
 * @package Mageplaza\BetterProductReviews\Block
 */
class Design extends Template
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ThemeProviderInterface
     */
    protected $_themeProvider;

    /**
     * Design constructor.
     *
     * @param Context $context
     * @param ThemeProviderInterface $_themeProvider
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        ThemeProviderInterface $_themeProvider,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_themeProvider = $_themeProvider;
        $this->_helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Get Current Theme Name Function
     *
     * @return string
     */
    public function getCurrentTheme()
    {
        $themeId = $this->_helperData->getConfigValue(DesignInterface::XML_PATH_THEME_ID);

        /**
         * @var $theme ThemeInterface
         */
        $theme = $this->_themeProvider->getThemeById($themeId);

        return $theme->getCode();
    }
}
