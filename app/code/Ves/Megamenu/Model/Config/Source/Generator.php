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

namespace Ves\Megamenu\Model\Config\Source;

class Generator extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Magento\Framework\View\Element\BlockFactory
	 */
	protected $_blockFactory;

	/**
	 * @var \Magento\Framework\App\ResourceConnection
	 */
	protected $_resource;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $_date;

	/**
	 * @var \Ves\Megamenu\Helper\Data
	 */
	protected $helper;

	protected $_logger;
	protected $_dir;

	/**
	 * @param \Magento\Framework\App\Helper\Context        $context      
	 * @param \Magento\Store\Model\StoreManagerInterface   $storeManager 
	 * @param \Magento\Framework\View\Element\BlockFactory $blockFactory 
	 * @param \Magento\Framework\App\ResourceConnection    $resource     
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime  $date         
	 * @param \Ves\Megamenu\Helper\Data                    $helper       
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\View\Element\BlockFactory $blockFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Ves\Megamenu\Helper\Data $helper,
		\Magento\Framework\Filesystem\DirectoryList $dir
	) {
		parent::__construct($context);
		$this->_storeManager = $storeManager;
		$this->_blockFactory = $blockFactory;
		$this->_resource     = $resource;
		$this->_date         = $date;
		$this->helper        = $helper;
		$this->_dir = $dir;
	}

	public function generateHtml($menu, $websiteCode = '', $storeCode = '') {
		if ($websiteCode) {
			$website = $this->_storeManager->getWebsite($websiteCode);
			$this->_generateWebsiteHtml($menu, $website);
		}
		if ($storeCode) {
			$this->_generateStoreHtml($menu, $storeCode);
		}
		if (!$websiteCode && !$storeCode) {
			$websites = $this->_storeManager->getWebsites();
			foreach ($websites as $website) {
				$this->_generateWebsiteHtml($menu, $website); 
			}
		}
	}

	protected function _generateWebsiteHtml($menu, $website) {
		foreach ($website->getStoreCodes() as $storeCode){
			$this->_generateStoreHtml($menu, $storeCode);
		}
	}

	public function getDebugLogger($file_name = "megamenu.log") {
		if(!$this->_logger) {
			$log_path = $this->_dir->getPath('log');
			$writer = new \Zend\Log\Writer\Stream($log_path.'/'.$file_name);
			$this->_logger = new \Zend\Log\Logger();
			$this->_logger->addWriter($writer);
		} 
		return $this->_logger;
	}
	public function getMenuCacheHtml($menu) {
		if (!$this->helper->getConfig('general_settings/enable_cache')) return false;

		$resource   = $this->_resource;
		$connection = $resource->getConnection();
		$store      = $this->_storeManager->getStore();
		$storeId    = $store->getId();
		$menuId     = $menu->getId();
		$table      = $resource->getTableName('ves_megamenu_cache');
		$select     = $connection->select()->from($table)->where('menu_id = ?', $menuId)->where('store_id = ?', $storeId);
		$row        = $connection->fetchRow($select);

		if (empty($row)) {
			$html = $this->generateMenuHtml($menu);
			try{
				$data['menu_id']       = $menuId;
				$data['store_id']      = $storeId;
				$data['html']          = $html;
				$data['creation_time'] = $this->_date->gmtDate('Y-m-d H:i:s');
				$connection->insert($table, $data);
			}catch (\Exception $e) {
				$logger = $this->getDebugLogger();
				$time = $this->_date->gmtDate('Y-m-d H:i:s');
				$logger->info('<==================='.$time.'====================>\n');
				$logger->info('Error Insert: Can not insert menu profile into database table "ves_megamenu_cache". \n More Info:\n');
				$logger->info($e);
				$logger->info('==================================================\n');
            }
		} else {
			$timestamp   = strtotime($row['creation_time']);
			$menuDay     = date("d", $timestamp);
			$currentDate = $this->_date->gmtDate('Y-m-d H:i:s');
			$currentDay  = date("d", strtotime($currentDate));
			if ($currentDay == $menuDay) {
				$html = $row['html'];
			} else {
				$html = $this->generateMenuHtml($menu);
				try{
					$connection->update($table, ['html' => $html, 'creation_time' => $currentDate], ['menu_id = ?' => $menuId, 'store_id = ?' => $storeId]);
				}catch (\Exception $e) {
					$logger = $this->getDebugLogger();
					$time = $this->_date->gmtDate('Y-m-d H:i:s');
					$logger->info('<==================='.$time.'====================>\n');
					$logger->info('Error Update: Can not update menu profile into database table "ves_megamenu_cache". \n More Info:\n');
					$logger->info($e);
					$logger->info('==================================================\n');
	            }
			}
		}
		return $html;
	}

	protected function generateMenuHtml($menu) {
		$html = $this->_blockFactory->createBlock('Ves\Megamenu\Block\MenuHtml')->setMenu($menu)->setTemplate("cache.phtml")->toHtml();
		return $html;
	}

	protected function _generateStoreHtml($menu, $storeCode = '') {
		$resource   = $this->_resource;
		$store      = $this->_storeManager->getStore($storeCode);
		$storeId    = $store->getId();
		$table      = $resource->getTableName('ves_megamenu_cache');
		$connection = $resource->getConnection();
		$menuId     = $menu->getId();
		$select     = $connection->select()->from($table)->where('menu_id = ?', $menuId)->where('store_id = ?', $storeId);
		$row        = $connection->fetchRow($select);
		$html       = $this->generateMenuHtml($menu);

		if (!empty($row)) {
			$connection->update($table, ['html' => $html], ['menu_id = ?' => $menuId, 'store_id = ?' => $storeId]);
		} else {
			try{
				$data['menu_id']       = $menuId;
				$data['store_id']      = $storeId;
				$data['html']          = $html;
				$data['creation_time'] = $this->_date->gmtDate('Y-m-d H:i:s');
				$connection->insert($table, $data);
			}catch (\Exception $e) {
				$logger = $this->getDebugLogger();
				$time = $this->_date->gmtDate('Y-m-d H:i:s');
				$logger->info('<==================='.$time.'====================>\n');
				$logger->info('Error Insert: Can not insert menu profile into database table "ves_megamenu_cache". \n More Info:\n');
				$logger->info($e);
				$logger->info('==================================================\n');
            }
		}
		return $html;
	}
}