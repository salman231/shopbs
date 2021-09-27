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

class ImportCategory extends \Magento\Backend\App\Action
{
    /**
     * @var \Ves\Megamenu\Model\Item
     */
    protected $menuItem;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $heleprData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ves\Megamenu\Helper\Editor
     */
    protected $editor;

    /**
     * @param \Magento\Backend\App\Action\Context        $context      
     * @param \Magento\Framework\App\ResourceConnection  $resource     
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager 
     * @param \Ves\Megamenu\Helper\Editor                $editor       
     * @param \Ves\Megamenu\Helper\Data                  $heleprData   
     * @param \Ves\Megamenu\Model\Item                   $menuItem     
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ves\Megamenu\Helper\Editor $editor,
        \Ves\Megamenu\Helper\Data $heleprData,
        \Ves\Megamenu\Model\Item $menuItem
    ) {
        parent::__construct($context);
        $this->menuItem      = $menuItem;
        $this->_resource     = $resource;
        $this->heleprData    = $heleprData;
        $this->_storeManager = $storeManager;
        $this->editor        = $editor;
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

        if ($data && isset($data['item'])) {
            $item = json_decode($data['item'], true);
            $connection = $this->_resource->getConnection();
            $table      = $this->_resource->getTableName('ves_megamenu_item');
            $select     = $connection->select()->from($table)->where('item_id = ?', $item['item_id']);

            $menuItem   = $connection->fetchRow($select);
            $model      = $this->_objectManager->create('Ves\Megamenu\Model\Item'); 
            if (!empty($menuItem)) {
                $model->load($menuItem['id']);
                $item['id'] = $menuItem['id'];
            }
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $fields = $this->editor->getFields(); 

            foreach ($item as $x => $y) {
                if(isset($fields[$x]) && ($fields[$x]['type']=='image' || $fields[$x]['type']=='file') ){
                    $item[$x] = str_replace($mediaUrl, "", $y);
                }
                if(isset($fields[$x]) && $fields[$x]['type']=='editor'){
                    $item[$x] = $this->heleprData->decodeImg($y);
                }
            }

            $item['menu_id'] = $data['menu_id'];
            $model->setData($item);
            try {
                $model->save();
                $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['status'=>true]));
            } catch (\Exception $e) {
                $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['status'=>false]));
            }
        }
    }
}