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

namespace Webkul\MagentoChatSystem\Model\Data;

use Webkul\MagentoChatSystem\Api\Data\ReportInterface;

class Report extends \Magento\Framework\Api\AbstractExtensibleObject implements ReportInterface
{

    /**
     * Get report_id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::REPORT_ID);
    }

    /**
     * Set report_id
     * @param string $reportId
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setId($reportId)
    {
        return $this->setData(self::REPORT_ID, $reportId);
    }

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Webkul\MagentoChatSystem\Api\Data\ReportExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\MagentoChatSystem\Api\Data\ReportExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get customer_name
     * @return string|null
     */
    public function getCustomerName()
    {
        return $this->_get(self::CUSTOMER_NAME);
    }

    /**
     * Set customer_name
     * @param string $customerName
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * Get agent_id
     * @return string|null
     */
    public function getAgentId()
    {
        return $this->_get(self::AGENT_ID);
    }

    /**
     * Set agent_id
     * @param string $agentId
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setAgentId($agentId)
    {
        return $this->setData(self::AGENT_ID, $agentId);
    }

    /**
     * Get subject
     * @return string|null
     */
    public function getSubject()
    {
        return $this->_get(self::SUBJECT);
    }

    /**
     * Set subject
     * @param string $subject
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setSubject($subject)
    {
        return $this->setData(self::SUBJECT, $subject);
    }

    /**
     * Get content
     * @return string|null
     */
    public function getContent()
    {
        return $this->_get(self::CONTENT);
    }

    /**
     * Set content
     * @param string $content
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }
}
