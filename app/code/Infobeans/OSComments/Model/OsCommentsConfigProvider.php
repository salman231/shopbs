<?php

/**
 * InfoBeans Sales Shipment Comment Extension
 *
 * @category   Infobeans
 * @package    Infobeans_OSComments
 * @version    2.0.0
 *
 * Release with version 2.0.0
 *
 * @author     InfoBeans Technologies Limited http://www.infobeans.com/
 * @copyright  Copyright (c) 2017 InfoBeans Technologies Limited
 */

namespace Infobeans\OSComments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class OsCommentsConfigProvider implements ConfigProviderInterface
{
    const XPATH_FRONTEND_ENABLE = 'infobeans_oscomments/general/enable_frontend';

    /**
     * @var \Infobeans\OSComments\Helper\Data
     */
    // @codingStandardsIgnoreLine
    protected $helper;

    /**
     * @param \Infobeans\OSComments\Helper\Data $dataHelper
     */
    public function __construct(
        \Infobeans\OSComments\Helper\Data $dataHelper
    ) {
        $this->helper = $dataHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $isModuleEnable = $this->helper->isModuleEnabled();
        $isFrontendEnabled = $this->helper->getConfig(self::XPATH_FRONTEND_ENABLE);
        $disabled = ($isModuleEnable && $isFrontendEnabled)?true:false;
        $shippingHeading = $this->helper->getShippingLabel();

        $config = [
            'shipping' => [
                'disabled' => $disabled,
                'shippingHeading' => $shippingHeading
            ]
        ];

        return $config;
    }
}
