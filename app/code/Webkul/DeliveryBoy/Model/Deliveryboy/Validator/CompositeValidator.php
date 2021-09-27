<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Model\Deliveryboy\Validator;

class CompositeValidator implements \Zend_Validate_Interface
{
    /**
     * @var \Zend_Validate_Interface[]
     */
    private $validators;

    /**
     * Validation error messages
     *
     * @var array
     */
    private $_messages = [];

    /**
     * @param array $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * Check whether the entity is valid according to defined validation rules
     *
     * @param \Magento\Framework\DataObject $entity
     * @return bool
     *
     * @throws \Exception
     * @api
     */
    public function isValid($entity)
    {
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($entity)) {
                $this->_messages += $validator->getMessages();
            }
        }
        $this->_messages = array_unique($this->_messages);
        
        return empty($this->_messages);
    }

    /**
     * Return error messages (if any) after the last validation
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
