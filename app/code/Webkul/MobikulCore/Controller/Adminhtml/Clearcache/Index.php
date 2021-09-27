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

namespace Webkul\MobikulCore\Controller\Adminhtml\Clearcache;

use \Magento\Framework\App\ResourceConnection;

/**
 * Class Index controller
 */
class Index extends \Magento\Backend\App\Action
{
    protected $resource;

    public function __construct(
        ResourceConnection $resource,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * Execute Function for Class Index
     *
     * @return jSon
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
            $connection->truncateTable($this->resource->getTableName("mobikul_cache"));
            $this->messageManager->addSuccess(__("Cache cleared successfully"));
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $resultRedirect->setPath("adminhtml/system_config/edit/section/mobikul/");
    }

    /**
     * Function to check if the controller is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::clearcache");
    }
}
