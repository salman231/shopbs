<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Token Helper Class
 */
class Token extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\MobikulCore\Helper\Data $helper
     */
    protected $helper;
    
    /**
     * @var \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceToken
     */
    protected $deviceToken;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * Constructor function for Helper Class
     *
     * @param \Webkul\MobikulCore\Helper\Data              $helper       helper
     * @param \Magento\Framework\App\Helper\Context        $context      context
     * @param \Magento\Store\Model\StoreManagerInterface   $storeManager storeManager
     * @param \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceToken  deviceToken
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceToken
    ) {
        $this->helper = $helper;
        $this->deviceToken = $deviceToken;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Function to save Customer Token
     *
     * @param integer $CustomerId customerId
     * @param string $token       token
     * @param string $os          os
     *
     * @return integer|void
     */
    public function saveToken($customerId, $token, $os)
    {
        try {
            $deviceTokenModel = $this->deviceToken->create();
            if ($customerId != "" && $token != "") {
                $collection = $deviceTokenModel->getCollection()->addFieldToFilter("token", $token);
                if ($collection->getSize() > 0) {
                    foreach ($collection as $eachRow) {
                        $this->deviceToken->create()
                            ->load($eachRow->getId())
                            ->setCustomerId($customerId)
                            ->setEmail("")
                            ->save();
                        return $eachRow->getId();
                    }
                } else {
                    return $this->deviceToken->create()
                    ->setToken($token)
                    ->setCustomerId($customerId)
                    ->setOs($os)
                    ->save()
                    ->getId();
                }
            }
            if ($customerId == "" && $token != "") {
                $collection = $deviceTokenModel->getCollection()->addFieldToFilter("token", $token);
                if ($collection->getSize() > 0) {
                    foreach ($collection as $eachRow) {
                        $this->deviceToken->create()->load($eachRow->getId())->setCustomerId($customerId)->save();
                        return $eachRow->getId();
                    }
                } else {
                    return $this->deviceToken
                        ->create()
                        ->setToken($token)
                        ->setCustomerId($customerId)
                        ->setOs($os)
                        ->save()
                        ->getId();
                }
            }
        } catch (\Exception $e) {
            $this->helper->printLog($e->getMessage());
        }
    }
}
