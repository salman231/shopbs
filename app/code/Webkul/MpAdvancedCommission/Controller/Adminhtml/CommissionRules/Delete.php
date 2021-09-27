<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Controller\Adminhtml\CommissionRules;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpAdvancedCommission\Api\CommissionRulesRepositoryInterface;

/**
 * Class Commission Rules Delete.
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var commissionRulesRepository
     */
    protected $_commissionRulesRepository;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CommissionRulesRepositoryInterface $commissionRulesRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CommissionRulesRepositoryInterface $commissionRulesRepository
    ) {
        $this->_filter = $filter;
        $this->_commissionRulesRepository = $commissionRulesRepository;
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
        $ruleId = $this->getRequest()->getParam('rule_id');
        $collection = $this->_commissionRulesRepository->getById($ruleId);
        if (!$collection) {
            $this->messageManager->addError(
                __(
                    'No commission rule exists with %1 id.',
                    $ruleId
                )
            );
        } else {
            $collection->delete();
            $this->messageManager->addSuccess(
                __(
                    'Commission Rule with id %1 have been deleted.',
                    $ruleId
                )
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('mpadvancedcommission/commissionrules/index/');
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
}
