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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Plugin;

class EntityTypeArrayPlugin
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * EntityTypeArrayPlugin constructor.
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\ImportExport\Model\Source\Import\Entity $subject
     * @param array $proceed
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundToOptionArray($subject, $proceed)
    {
        $bssOptions = [];
        $bssOptions[] = ['label' => __('-- Please Select --'), 'value' => ''];
        $options = [];
        $result = $proceed();
        foreach ($result as $entityConfig) {
            if (strpos($entityConfig['value'], 'sdcp_preselect')!==false) {
                $bssOptions[] = $entityConfig;
            } else {
                $options[] = $entityConfig;
            }
        }
        if ($this->request->getRouteName() === 'sdcp') {
            return $bssOptions;
        } else {
            return $options;
        }
    }
}
