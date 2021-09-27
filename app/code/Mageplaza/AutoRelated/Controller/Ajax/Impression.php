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
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Controller\Ajax;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\AutoRelated\Helper\Rule as HelperRule;
use Mageplaza\AutoRelated\Model\ResourceModel\RuleFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Impression
 * @package Mageplaza\AutoRelated\Controller\Ajax
 */
class Impression extends Action
{
    /**
     * @var RuleFactory
     */
    protected $autoRelatedRuleFac;

    /**
     * @var HelperRule
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Impression constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param HelperRule $helper
     * @param RuleFactory $autoRelatedRuleFac
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        HelperRule $helper,
        RuleFactory $autoRelatedRuleFac
    ) {
        $this->autoRelatedRuleFac = $autoRelatedRuleFac;
        $this->helper             = $helper;
        $this->logger             = $logger;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $params = HelperRule::jsonDecode($this->getRequest()->getContent());
        if ($this->helper->isEnabled() && !empty($params) && isset($params['ruleIds']) && is_array($params['ruleIds'])) {
            $ruleResource = $this->autoRelatedRuleFac->create();

            try {
                foreach ($params['ruleIds'] as $ruleId) {
                    $ruleResource->updateImpression($ruleId);
                }
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
