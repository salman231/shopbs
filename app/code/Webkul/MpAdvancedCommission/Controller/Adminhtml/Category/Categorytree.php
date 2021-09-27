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
 * Webkul MpAdvancedCommission Category Tree controller.
 */
class Categorytree extends Action
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
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

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
        $this->_jsonHelper = $jsonHelper;
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
            $parentCategory = $this->_categoryRepository->get(
                $data['parentCategoryId']
            );
            $parentChildren = $parentCategory->getChildren();
            $parentChildIds = explode(',', $parentChildren);
            $index = 0;
            foreach ($parentChildIds as $parentChildId) {
                $categoryData = $this->_categoryRepository->get(
                    $parentChildId
                );
                if ($this->_categoryResourceModel->getChildrenCount($parentChildId) > 0) {
                    $result[$index]['counting'] = 1;
                } else {
                    $result[$index]['counting'] = 0;
                }
                $result[$index]['id'] = $categoryData['entity_id'];
                $result[$index]['name'] = $categoryData->getName();
                $result[$index]['commission'] = $categoryData->getCommissionForAdmin();
                if (!$categoryData->getCommissionForAdmin()) {
                    $result[$index]['commission'] = '';
                }
                $categories = [];
                $categoryIds = '';
                if (isset($data['categoryIds'])) {
                    $categories = explode(',', $data['categoryIds']);
                    $categoryIds = $data['categoryIds'];
                }
                if ($categoryIds && in_array($categoryData['entity_id'], $categories)) {
                    $result[$index]['check'] = 1;
                } else {
                    $result[$index]['check'] = 0;
                }
                ++$index;
            }
            $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        } catch (\Exception $e) {
            $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode('')
            );
        }
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
