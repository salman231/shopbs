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
namespace Webkul\MagentoChatSystem\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Webkul\MagentoChatSystem\Api\ChangeStatusInterface;

class CustomerLogOutObserver implements ObserverInterface
{
    /**
     * @var ChangeStatusInterface
     */
    protected $changeStatus;

   /**
    * @param ChangeStatus $changeStatus
    */
    public function __construct(
        ChangeStatusInterface $changeStatus
    ) {
        $this->changeStatus = $changeStatus;
    }

    public function execute(EventObserver $observer)
    {
        $this->changeStatus->changeStatus(0);
    }
}
