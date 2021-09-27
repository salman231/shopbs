<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;

/**
 * Webkul MpAdvancedCommission Category Commission Save controller.
 */
class Save extends Action
{
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $_categoryResourceModel;

     /**
      * @param Action\Context $context
      * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
      * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel
      * @param \Magento\Framework\Json\Helper\Data $jsonHelper
      */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_categoryRepository = $categoryRepository;
        $this->_categoryResourceModel = $categoryResourceModel;
        $this->jsonHelper=$jsonHelper;
        parent::__construct($context);
    }

    /**
     * Get Category tree action.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        try {
            $decodedData = $this->jsonHelper->jsonDecode($data['object']);
            foreach ($decodedData as $key => $val) {
                $this->saveCategoryData($key, $val);
            }
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode(true)
            );
        } catch (\Exception $e) {
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode('')
            );
        }
    }

   /**
    * Undocumented function
    *
    * @param int $key
    * @param decimal $val
    * @return void
    */
    protected function saveCategoryData($key, $val)
    {
        $category = $this->_categoryRepository->get($key);
        $category->setCommissionForAdmin($val);
        $category->save();
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
