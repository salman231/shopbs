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

namespace Bss\Simpledetailconfigurable\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Exception\LocalizedException;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving
     */
    private $additionalInfoSaving;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * UpgradeData constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving $additionalInfoSaving
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving $additionalInfoSaving,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->additionalInfoSaving = $additionalInfoSaving;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $areaCode = null;
            $areaCode = $this->state->getAreaCode();

            if ($areaCode === null) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->additionalInfoSaving->updateCustomUrlData();
        }

        $setup->endSetup();
    }
}
