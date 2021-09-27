<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Api\Data;

interface ReportInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const CUSTOMER_NAME = 'customer_name';
    const CONTENT = 'content';
    const SUBJECT = 'subject';
    const CUSTOMER_ID = 'customer_id';
    const REPORT_ID = 'report_id';
    const AGENT_ID = 'agent_id';

    /**
     * Get report_id
     * @return string|null
     */
    public function getId();

    /**
     * Set report_id
     * @param string $reportId
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setId($reportId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setCustomerId($customerId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Webkul\MagentoChatSystem\Api\Data\ReportExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\MagentoChatSystem\Api\Data\ReportExtensionInterface $extensionAttributes
    );

    /**
     * Get customer_name
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Set customer_name
     * @param string $customerName
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setCustomerName($customerName);

    /**
     * Get agent_id
     * @return string|null
     */
    public function getAgentId();

    /**
     * Set agent_id
     * @param string $agentId
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setAgentId($agentId);

    /**
     * Get subject
     * @return string|null
     */
    public function getSubject();

    /**
     * Set subject
     * @param string $subject
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setSubject($subject);

    /**
     * Get content
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     * @param string $content
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setContent($content);
}
