<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Model;


abstract class Manager extends \Magento\Framework\DataObject
{
    protected $_request;
    protected $_structures = [];

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ){
        $this->_request = $request;
    }

    /**
     * @param $websiteId
     * @return \Amasty\Methods\Model\Structure
     */
    abstract function getMethodsStructure($websiteId);

    /**
     * @return bool
     */
    protected function isBackend()
    {
        return $this->_request->getFullActionName() === 'sales_order_create_loadBlock';
    }

    /**
     * @param $quoteWebsiteId
     * @return int
     */
    public function getWebsiteId($quoteWebsiteId)
    {
        $websiteId = $quoteWebsiteId;
        $adminWebsiteId = 0;

        if ($this->isBackend()) {
            if ($this->getMethodsStructure($adminWebsiteId)->getSize() > 0){
                $websiteId = $adminWebsiteId;
            }
        }

        return $websiteId;
    }
}