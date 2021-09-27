<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Controller\Adminhtml\Menu;
use Magento\Framework\App\Filesystem\DirectoryList;

class SavePreview extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @param \Magento\Backend\App\Action\Context            $context           
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList     
     * @param \Magento\Framework\App\Cache\Frontend\Pool     $cacheFrontendPool 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        parent::__construct($context);
        $this->_cacheTypeList     = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
    	return $this->_authorization->isAllowed('Ves_Megamenu::menu_save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	$data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Ves\Megamenu\Model\Menu');
            $id = $this->getRequest()->getParam('menu_id');
            if ($id) {
                $model->load($id);
            }
            if (isset($data['structure'])) {
                $params = [
                    'structure' => $data['structure'],
                    'design' => $data['design']
                ];
                $model->setParams(serialize($params));
                $model->setData('is_preview', true);
                $model->save();
            }

            // REFRESH CACHE
            $types = ['block_html'];
            foreach ($types as $type) {
                $this->_cacheTypeList->cleanType($type);
            }
            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }

            try {
                $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['status'=>true]));
            } catch (\Exception $e) {
                $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['status'=>false]));
            }
        }
    }
}