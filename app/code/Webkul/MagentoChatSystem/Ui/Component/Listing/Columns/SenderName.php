<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MagentoChatSystem\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\MagentoChatSystem\Model\CustomerDataFactory;
use Webkul\MagentoChatSystem\Model\AgentDataFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\User\Model\UserFactory;

/**
 * Class ViewAction.
 */
class SenderName extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @var CustomerDataFactory
     */
    protected $customerDataFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param CustomerDataFactory $customerDataFactory
     * @param AgentDataFactory $agentDataFactory
     * @param CustomerFactory $customerFactory
     * @param UserFactory $userFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CustomerDataFactory $customerDataFactory,
        AgentDataFactory $agentDataFactory,
        CustomerFactory $customerFactory,
        UserFactory $userFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->agentDataFactory = $agentDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $chatCustomer = $this->customerDataFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('unique_id', ['eq' => $item['sender_unique_id']]);
                    if ($chatCustomer->getSize()) {
                        $customer = $this->customerFactory->create()
                            ->load($chatCustomer->getFirstItem()->getCustomerId());
                        $item[$this->getData('name')] = $customer->getName();
                    } else {
                        $agentCustomer = $this->agentDataFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('agent_unique_id', ['eq' => $item['sender_unique_id']]);

                        $agent = $this->userFactory->create()
                            ->load($agentCustomer->getFirstItem()->getAgentId());
                        $item[$this->getData('name')] = $agent->getName();
                    }
                }
            }
        }

        return $dataSource;
    }
}
