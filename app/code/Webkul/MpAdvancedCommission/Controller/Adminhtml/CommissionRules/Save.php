<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Controller\Adminhtml\CommissionRules;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action
{
    /**
     * @var CommissionRulesFactory
     */
    protected $_commissionRulesFactory;

    /**
     * @var commissionRulesRepository
     */
    protected $_commissionRulesRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

     /**
      * @param Context $context
      * @param \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory $commissionRulesFactory
      * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
      * @param \Webkul\MpAdvancedCommission\Api\CommissionRulesRepositoryInterface $commissionRulesRepository
      */
    public function __construct(
        Context $context,
        \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory $commissionRulesFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MpAdvancedCommission\Api\CommissionRulesRepositoryInterface $commissionRulesRepository
    ) {
    
        $this->_commissionRulesFactory = $commissionRulesFactory;
        $this->_commissionRulesRepository = $commissionRulesRepository;
        $this->_date = $date;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        $ruleId = $this->getRequest()->getParam('rule_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $isRuleExist = $this->checkAvailabilityOfRule($data['price_from'], $data['price_to'], $ruleId);
                if (is_array($isRuleExist)) {
                    if (!empty($isRuleExist)) {
                        $this->messageManager->addError(__('Price range already exist.'));
                        $this->_redirect('*/*/');
                        return;
                    }
                }
                $commissionRulesById = $this->_commissionRulesRepository->getById($ruleId);
                $existingRuleId = '';
                if ($commissionRulesById) {
                    $existingRuleId = $commissionRulesById->getId();
                }
                if (!$existingRuleId || $existingRuleId == $ruleId) {
                    $commissionRules = $this->_commissionRulesFactory->create();
                    if ($ruleId) {
                        $commissionRules->load($ruleId);
                        $commissionRules->setUpdatedAt($this->_date->gmtDate());
                    }
                    $commissionRules->addData($data)->save();
                    $ruleId = $commissionRules->getId();
                    $this->messageManager->addSuccess(
                        __('Commission Rule has been saved successfully.')
                    );
                } else {
                    $this->messageManager->addError(
                        __(
                            'Commission Rule with %1 id already exist.',
                            $ruleId
                        )
                    );
                    $redirectBack = $ruleId ? true : 'index';
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $redirectBack = $ruleId ? true : 'index';
            }
        } else {
            $resultRedirect->setPath('mpadvancedcommission/commissionrules/');
            $this->messageManager->addError('No data to save');

            return $resultRedirect;
        }

        if ($redirectBack === 'index') {
            $resultRedirect->setPath(
                'mpadvancedcommission/commissionrules/index'
            );
        } elseif ($redirectBack) {
            $resultRedirect->setPath(
                'mpadvancedcommission/commissionrules/edit',
                ['rule_id' => $ruleId, '_current' => true]
            );
        } else {
            $resultRedirect->setPath('mpadvancedcommission/commissionrules/');
        }

        return $resultRedirect;
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAdvancedCommission::commissionrules');
    }

    /**
     * check status of price range
     *
     * @param [type] $minPrice
     * @param [type] $maxPrice
     * @return void
     */
    private function checkAvailabilityOfRule($minPrice, $maxPrice, $ruleId)
    {
        $collection = $this->_commissionRulesFactory
                        ->create()
                        ->getCollection();
        $collection->getSelect()->where(
            'price_from < '.$minPrice.' AND '.$maxPrice.' < price_to
            OR '.$minPrice.' BETWEEN price_from AND price_to 
            OR '.$maxPrice.' BETWEEN price_from AND price_to
            OR price_from > '. $minPrice.' AND '.' price_to < '.$maxPrice
        );
        $ids = [];
        if ($collection->getSize()) {
            foreach ($collection as $record) {
                if ($ruleId !== $record->getId()) {
                    $ids[] = $record->getId();
                }
            }
            return $ids;
        }
        return true;
    }
}
