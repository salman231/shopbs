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

use Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory as DeliveryboyResourceCollectionFactory;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;

class UniqueEmailValidator implements \Zend_Validate_Interface
{
    /**
     * @var DeliveryboyResourceCollectionFactory
     */
    private $deliveryboyResourceCollectionFactory;

    /**
     * @param DeliveryboyResourceCollectionFactory $deliveryboyResourceCollection
     */
    public function __construct(
        DeliveryboyResourceCollectionFactory $deliveryboyResourceCollectionFactory
    ) {
        $this->deliveryboyResourceCollectionFactory = $deliveryboyResourceCollectionFactory;
    }

    /**
     * Validation error messages
     *
     * @var array
     */
    private $_messages = [];

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
        $this->_messages = [];
        $currentDeliveryboyEmail = $entity->getEmail();
        $deliveryboyResourceCollection = $this->deliveryboyResourceCollectionFactory->create()
            ->addFieldToFilter(
                DeliveryboyInterface::EMAIL,
                $currentDeliveryboyEmail
            )->addFieldtoFilter(
                DeliveryboyInterface::ID,
                ['neq' => $entity->getId()]
            );
        if ($deliveryboyResourceCollection->getSize() > 0) {
            $this->_messages[] = __('A Delivery boy with email ' . $entity->getEmail() . ' already exists.');
        }
        
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
