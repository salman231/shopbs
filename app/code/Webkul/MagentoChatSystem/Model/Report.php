<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MagentoChatSystem\Model;

use Webkul\MagentoChatSystem\Api\Data\ReportInterface;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MagentoChatSystem\Api\Data\ReportInterfaceFactory;

class Report extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'chatsystem_report';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ReportInterfaceFactory
     */
    protected $reportDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ReportInterfaceFactory $reportDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Webkul\MagentoChatSystem\Model\ResourceModel\Report $resource
     * @param \Webkul\MagentoChatSystem\Model\ResourceModel\Report\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ReportInterfaceFactory $reportDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Webkul\MagentoChatSystem\Model\ResourceModel\Report $resource,
        \Webkul\MagentoChatSystem\Model\ResourceModel\Report\Collection $resourceCollection,
        array $data = []
    ) {
        $this->reportDataFactory = $reportDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve report model with report data
     * @return ReportInterface
     */
    public function getDataModel()
    {
        $reportData = $this->getData();
        
        $reportDataObject = $this->reportDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $reportDataObject,
            $reportData,
            ReportInterface::class
        );
        
        return $reportDataObject;
    }
}
