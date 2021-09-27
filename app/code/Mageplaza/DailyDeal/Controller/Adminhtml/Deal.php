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

namespace Mageplaza\DailyDeal\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class Deal
 * @package Mageplaza\DailyDeal\Controller\Adminhtml
 */
abstract class Deal extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Mageplaza_DailyDeal::dealgrid';

    /**
     * Daily Deal Factory
     *
     * @var DealFactory
     */
    protected $_dealFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Deal constructor.
     *
     * @param Context $context
     * @param DealFactory $dealFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        DealFactory $dealFactory,
        Registry $coreRegistry
    ) {
        $this->_dealFactory  = $dealFactory;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * Init Deal
     *
     * @return \Mageplaza\DailyDeal\Model\Deal
     */
    protected function _initDeal()
    {
        $deal = $this->_dealFactory->create();

        $dealId = (int) $this->getRequest()->getParam('id');
        if ($dealId) {
            $deal->load($dealId);
        }
        $this->_coreRegistry->register('mageplaza_dailydeal_deal', $deal);

        return $deal;
    }
}
