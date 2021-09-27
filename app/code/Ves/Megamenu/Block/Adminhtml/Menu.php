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

namespace Ves\Megamenu\Block\Adminhtml;

use Ves\All\Model\Config;

class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @var null|array
     */
    protected $items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Ves_All::menu.phtml';


    public function __construct(\Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context);

    }//end __construct()


    public function getMenuItems()
    {
        $id = $this->getRequest()->getParam('menu_id');

        if ($this->items === null) {
            $items = [
                      'menu/edit'    => [
                                'title'    => $id ? __('Edit Menu') : __('New Menu'),
                                'url'      => $this->getUrl('*/menu/new'),
                                'resource' => 'Ves_Megamenu::menu_edit'
                                ],
                      'menu'    => [
                             'title'    => __('Manage Menus'),
                             'url'      => $this->getUrl('*/menu/index'),
                             'resource' => 'Ves_Megamenu::menu',
                             'child'    => [
                                'newAction' => [
                                                'title'    => __('New Menu'),
                                                'url'      => $this->getUrl('*/menu/newAction'),
                                                'resource' => 'Ves_Megamenu::menu_edit',
                                               ],
                               ],
                                    ],
                      'settings' => [
                                     'title'    => __('Settings'),
                                     'url'      => $this->getUrl('adminhtml/system_config/edit/section/vesmegamenu'),
                                     'resource' => 'Ves_Megamenu::config_megamenu',
                                    ],
                      'userguide'   => [
                                     'title'     => __('User Guide'),
                                     'url'       => Config::MEGAMENU_USER_GUIDE,
                                     'attr'      => ['target' => '_blank'],
                                     'separator' => true,
                                    ],
                      'videoguide'   => [
                                     'title'     => __('Video Guide'),
                                     'url'       => Config::MEGAMENU_VIDEO_GUIDE,
                                     'attr'      => ['target' => '_blank']
                                    ],
                      'support'  => [
                                     'title' => __('Get Support'),
                                     'url'   => Config::LANDOFCODER_TICKET,
                                     'attr'  => ['target' => '_blank'],
                                    ],
                     ];
            foreach ($items as $index => $item) {
                if (array_key_exists('resource', $item)) {
                    if (!$this->_authorization->isAllowed($item['resource'])) {
                        unset($items[$index]);
                    }
                }
            }

            $this->items = $items;
        }//end if

        return $this->items;

    }//end getMenuItems()


    /**
     * @return array
     */
    public function getCurrentItem()
    {
        $items          = $this->getMenuItems();
        $controllerName = $this->getRequest()->getControllerName();
        $actionName     = $this->getRequest()->getActionName();

        $key = $controllerName . '/' . $actionName;
        if (array_key_exists($key, $items)) {
            return $items[$key];
        }

        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }

        return $items['page'];

    }//end getCurrentItem()


    /**
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }

        return $result;

    }//end renderAttributes()


    /**
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        $controllerName = $this->getRequest()->getControllerName();
        $actionName     = $this->getRequest()->getActionName();
        $key = $controllerName . '/' . $actionName;
        if ($key == $itemIndex) {
            return true;
        }
        return $itemIndex == $this->getRequest()->getControllerName();

    }//end isCurrent()


}//end class
