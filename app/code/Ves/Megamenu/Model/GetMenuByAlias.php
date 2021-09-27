<?php
/**
 * Copyright (c) 2019 Venustheme
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Ves\Megamenu\Model;

use Ves\Megamenu\Api\Data\MenuInterface;

class GetMenuByAlias implements \Ves\Megamenu\Api\GetMenuByAliasInterface
{

    /**
     * @var \Ves\Megamenu\Model\MenuFactory 
     */
    private $menuFactory;

    /**
     * @var ResourceModel\Menu
     */
    private $menuResource;


    /**
     * Constructor.
     *
     * @param \Ves\Megamenu\Model\MenuFactory $menuFactory
     * @param \Ves\Megamenu\Model\ResourceModel\Menu $menuResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @api
     */
    public function __construct(
        \Ves\Megamenu\Model\MenuFactory $menuFactory,
        \Ves\Megamenu\Model\ResourceModel\Menu $menuResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
            $this->menuFactory = $menuFactory;
            $this->menuResource = $menuResource;
            $this->_storeManager   = $storeManager;
    }
    /**
     * {@inheritdoc}
     */
    public function execute( string $alias, string $storeCode = 'all', int $customer_group_id = 0, bool $is_mobile_menu = false): \Ves\Megamenu\Api\Data\MenuInterface
    {
        $menu = $this->menuFactory->create();
        if($storeCode != "all"){
            $store = $this->_storeManager->getStore($storeCode);
        }else {
            $store = $this->_storeManager->getStore();
        }
        $menu->setSTore($store);
        if($customer_group_id){
            $menu->setLoggedCustomerGroupId((int)$customer_group_id);
        }
        $menu->load(addslashes($alias));
        $mobile_template = $menu->getMobileTemplate();
        $mobile_menu_alias = $menu->getMobileMenuAlias();

        if (!$menu->getId()) {
            throw new NoSuchEntityException(__('The Megamenu with the "%1" ID doesn\'t exist.', $alias));
        }
        if (!$menu->getStatus()) {
            throw new NoSuchEntityException(__('The Megamenu with the "%1" ID is disabled.', $alias));
        }

        if($is_mobile_menu && $mobile_template == \Ves\Megamenu\Model\Menu::MENU_TEMPLATE_TYPE_CUSTOM && $mobile_menu_alias){
            $menu->load(addslashes($mobile_menu_alias));
        }
        $menu->generateMenuTree();

        return $menu;
    }

}
