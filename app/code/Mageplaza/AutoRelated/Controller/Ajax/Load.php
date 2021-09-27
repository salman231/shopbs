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
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\AutoRelated\Block\Product\Block;
use Mageplaza\AutoRelated\Helper\Rule as HelperRule;
use Mageplaza\AutoRelated\Model\Config\Source\DisplayMode;
use Mageplaza\AutoRelated\Model\ResourceModel\RuleFactory;
use Mageplaza\AutoRelated\Model\Rule;

/**
 * Class Load
 * @package Mageplaza\AutoRelated\Controller\Ajax
 */
class Load extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var HelperRule
     */
    protected $helper;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * Load constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param HelperRule $helper
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        HelperRule $helper,
        RuleFactory $ruleFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper            = $helper;
        $this->ruleFactory       = $ruleFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $result = ['status' => false];
        if (!$this->helper->isEnabled()) {
            return $this->getResponse()->representJson(HelperRule::jsonEncode($result));
        }

        $resultPage = $this->resultPageFactory->create();
        $params     = HelperRule::jsonDecode($this->getRequest()->getContent());

        try {
            $pageType = $params['type'];
            $entityId = $params['entity_id'];
            $this->helper->setData('type', $pageType);
            $this->helper->setData('entity_id', $entityId);
            $result = [];
            /** @var Rule[] $activeRules */
            $activeRules = $this->helper->getActiveRulesByMode(DisplayMode::TYPE_AJAX);
            foreach ($activeRules as $rule) {
                if (empty($rule->getApplyProductIds())) {
                    continue;
                }
                $result['data'][] = [
                    'id'      => $rule->getData('location'),
                    'content' => $resultPage->getLayout()
                        ->createBlock(Block::class)
                        ->setRule($rule)
                        ->toHtml()
                ];
                $rule->getResource()->updateImpression($rule->getId());
            }
            if (isset($result['data'])) {
                $result['status'] = true;
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            $resultRedirect    = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            return $resultRedirect;
        }

        return $this->getResponse()->representJson(HelperRule::jsonEncode($result));
    }
}
