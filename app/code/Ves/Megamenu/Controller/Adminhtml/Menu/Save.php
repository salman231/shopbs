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

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ves\Megamenu\Helper\Generator
     */
    protected $generatorhelper;

    /**
     * Cache
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @param \Magento\Backend\App\Action\Context        $context      
     * @param \Magento\Framework\App\ResourceConnection  $resource     
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager 
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ves\Megamenu\Helper\Generator $generatorhelper,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        parent::__construct($context);
        $this->_resource       = $resource;
        $this->_storeManager   = $storeManager;
        $this->generatorhelper = $generatorhelper;
        $this->_cache = $cache;
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
            
            if(isset($data['cms_page'])){
                unset($data['cms_page']);
            }
            $model = $this->_objectManager->create('Ves\Megamenu\Model\Menu');
            $id = $this->getRequest()->getParam('menu_id');
            if ($id) {
                $model->load($id);
            }
            if(!$data['structure'] || $data['structure']=='' || $data['structure']=='[]'){
                $data['structure'] = $model->getStructure();
            }
            if($this->getRequest()->getParam("revert_previous")){
                $data['revert_previous'] = $this->getRequest()->getParam("revert_previous");
            }
            if($this->getRequest()->getParam("revert_next")){
                $data['revert_next'] = $this->getRequest()->getParam("revert_next");
            }

            if (isset($data['design'])) {
                $data['design'] = serialize($data['design']);
            }

            $model->setData($data);
            try {
                $model->save();
                if(isset($data['revert_previous']) || isset($data['revert_next'])) {
                    if(isset($data['revert_previous'])){
                        $version = $data['revert_previous'];
                    }
                    if(isset($data['revert_next'])){
                        $version = $data['revert_next'];
                    }
                    $this->messageManager->addSuccess(__('You reverted the menu id #%1, current version #%2', $id, $version));
                    return $resultRedirect->setPath('*/*/edit', ['menu_id' => $model->getId()]);
                } else {
                    $this->messageManager->addSuccess(__('You saved this menu.'));
                }

                if ($this->getRequest()->getParam('cache')) {
                    $resource   = $this->_resource;
                    $connection = $resource->getConnection();
                    $where = ['menu_id = ?' => $model->getId()];
                    $connection->delete($resource->getTableName('ves_megamenu_cache'), $where);
                    //flush menu profile cache
                    $cache_menu_profile_tag = \Ves\Megamenu\Model\Menu::CACHE_HTML_TAG;
                    $cache_menu_profile_tag .= "_".$model->getId();
                    $this->_cache->clean([$cache_menu_profile_tag]);

                    $this->messageManager->addSuccess(__('You saved this menu.'));
                    return $resultRedirect->setPath('*/*/edit', ['menu_id' => $model->getId()]);
                }

                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['menu_id' => $model->getId(), '_current' => true]);
                }

                if ($this->getRequest()->getParam("new")) {
                    return $resultRedirect->setPath('*/*/new');
                }

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the menu.'));
            }

            if ($this->getRequest()->getParam("duplicate")) {
                unset($data['menu_id']);
                $data['status']    = 0;
                $data['duplicate'] = true;
                $menu = $this->_objectManager->create('Ves\Megamenu\Model\Menu');
                $menu->setData($data);
                try{
                    $menu->save();
                    $table = $this->_resource->getTableName('ves_megamenu_item');
                    $connection = $this->_resource->getConnection();
                    $select = $connection->select()->from($table)->where('menu_id = ?', $model->getId());
                    $items = $connection->fetchAll($select);
                    foreach ($items as &$item) {
                        unset($item['id']);
                        $item['menu_id'] = $menu->getId();
                    }
                    $connection->insertMultiple($table, $items);
                    $this->messageManager->addSuccess(__('You duplicated this menu.'));
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while duplicating the menu.'));
                }
            }
            return $resultRedirect->setPath('*/*/edit', ['menu_id' => $this->getRequest()->getParam('menu_id')]);
        }
    }
}