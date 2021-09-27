<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Block\Adminhtml\Config;

class AdditionalExtension extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\Integrate
     */
    private $integrate;

    /**
     * AdditionalExtension constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Bss\Simpledetailconfigurable\Helper\Integrate $integrate
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Bss\Simpledetailconfigurable\Helper\Integrate $integrate,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->integrate = $integrate;
    }

    /**
     * @return array
     */
    protected function getIntegrateExtensions()
    {
        return [
            [
                'name' => __('Configurable Product Wholesale Display'),
                'status' => $this->integrate->isConfigurableProductWholesaleEnabled(),
                'description' => __(
                    'Order configurable products faster - %1',
                    "<a style='color:#006bb4'
                        target='_blank'
                        href='https://bsscommerce.com/magento-2-configurable-product-wholesale-display-extension.html'>
                        Check now!
                    </a>"
                )
            ]
        ];
    }

    /**
     * Return list of Integrated Bss Module and installed status
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed|string
     */
    protected function _getHeaderHtml($element)
    {
        $headerHtml = parent::_getHeaderHtml($element);

        $rowExt = '';
        foreach ($this->getIntegrateExtensions() as $extension) {
            $statusValueText = $this->getStatusModuleHtml($extension['status']);
            $statusText = __('Status');
            $rowExt .=
                "<tr id='row_tax_display_type'>
                    <td class='label'><label for='tax_display_type'>{$extension['name']}</span></label></td>
                    <td class='value'>
                        $statusText: $statusValueText
                        <p class='note'><span>{$extension['description']}</span></p>
                    </td>
                </tr>";
        }

        $headerHtml = str_replace(
            '<table cellspacing="0" class="form-list">',
            '<table cellspacing="0" class="form-list">' . "<tbody>$rowExt</tbody>",
            $headerHtml
        );

        return $headerHtml;
    }

    /**
     * @param int $status
     * @return string
     */
    public function getStatusModuleHtml($status)
    {
        if ($status) {
            $statusValueText = "<b style='color:green'>" . __('Installed') . "</b>";
        } else {
            $statusValueText = "<b style='color:red'>" . __('Not Installed') . "</b>";
        }

        return $statusValueText;
    }
}
