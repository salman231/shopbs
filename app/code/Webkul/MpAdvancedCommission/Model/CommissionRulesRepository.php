<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Webkul\MpAdvancedCommission\Model\ResourceModel\CommissionRules as CommissionRulesModel;
use Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface;

class CommissionRulesRepository implements
    \Webkul\MpAdvancedCommission\Api\CommissionRulesRepositoryInterface
{
    /**
     * @var CommissionRulesFactory
     */
    protected $_commissionRulesFactory;

    /**
     * @var CommissionRules[]
     */
    protected $_instances = [];

    /**
     * @var CommissionRules[]
     */
    protected $_instancesById = [];

    /**
     * @var CommissionRulesModel\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var CommissionRulesModel
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @param CommissionRulesFactory                                $commissionRulesFactory
     * @param CommissionRulesModel\CollectionFactory                $collectionFactory
     * @param CommissionRulesModel                                  $resourceModel
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter  $extensibleDataObjectConverter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CommissionRulesFactory $commissionRulesFactory,
        CommissionRulesModel\CollectionFactory $collectionFactory,
        CommissionRulesModel $resourceModel,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_commissionRulesFactory = $commissionRulesFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($ruleId)
    {
        $commissionRulesData = $this->_commissionRulesFactory->create();
        $commissionRulesData->load($ruleId);
        if (!$commissionRulesData->getId()) {
            return [];
        }
        $this->_instancesById[$ruleId] = $commissionRulesData;

        return $this->_instancesById[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CommissionRulesInterface $commissionRules)
    {
        $ruleId = $commissionRules->getId();
        try {
            $this->_resourceModel->delete($commissionRules);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove commission rules for id %1', $ruleId)
            );
        }
        unset($this->_instancesById[$ruleId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $commissionRules = $this->getById($ruleId);

        return $this->delete($commissionRules);
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        /** @var CommissionRulesModel\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCommissionType($type)
    {
        /** @var CommissionRulesModel\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('commission_type', $type);
        $collection->load();

        return $collection;
    }

    /**
     * Merge data from DB and updates from request.
     *
     * @param array $commissionRulesDataArray
     * @param bool  $createNew
     *
     * @return CommissionRulesInterface|CommissionRules
     *
     * @throws NoSuchEntityException
     */
    protected function initializeRuleData(array $commissionRulesDataArray, $createNew)
    {
        if ($createNew) {
            $commissionRules = $this->_commissionRulesFactory->create();
        } else {
            $commissionRules = $this->getById($commissionRulesDataArray['rule_id']);
        }
        foreach ($commissionRulesDataArray as $key => $value) {
            $commissionRules->setData($key, $value);
        }

        return $commissionRules;
    }
}
