Need to follow below steps to accurately make your module working with Magedelight_Base

1. Your module must have a seprate menu item
2. Your root level menu item must have the naming convension as below.
	<add id="{Vendor}_{Module}::{<small_caps>module_name}_root"
            title="{Title}"
            module="{Vendor}_{Module}"
            sortOrder="50"
            resource="{Vendor}_{Module}::root"
            toolTip="magedelight_base" />
3. Add new item in menu.xml after above menu item.
	<add id="{Vendor}_{Module}::{<small_caps>module_name}_root_commonlyvisible"
            title="{Title}"
            module="{Vendor}_{Module}"
            sortOrder="{your_module_sortorder}"
            parent="Magedelight_Base::md_modules"
            resource="{Vendor}_{Module}::root" />

4. in your etc/adminhtml/system.xml, your section must look like follow.
	<section>
    	<class>md_section_{<small_caps>modulename}</class>
        <tab>magedelight</tab>
        ...
    </section>

5. create following file in your module.
	Path: {Vendor}/{Module}/view/adminhtml/web/css/source/
	File: _module.less

	contents:
		@md-{<small_caps>modulename}-icons-admin__font-name-path: '@{baseDir}{Vendor}_{Module}/fonts/icon';
		@md-{<small_caps>modulename}-icons-admin__font-name : '{Module}';
		.lib-font-face(
		  	@family-name:@md-{<small_caps>modulename}-icons-admin__font-name,
		  	@font-path: @md-{<small_caps>modulename}-icons-admin__font-name-path,
		  	@font-weight: normal,
		  	@font-style: normal
		);

		.admin__menu .item-{<small_caps>modulename}-root-commonlyvisible > a:before,
		.admin__menu .item-{<small_caps>modulename}-root.parent.level-0 .submenu > .submenu-title:before,
		.config-nav-block .md_section_{<small_caps>modulename} a:before {
		  	font-family: @md-{<small_caps>modulename}-icons-admin__font-name;
		  	content: "{content to be taken from icomoon or appropriate library}";
		  	padding-right: 8px;
		}

6. download relevent icon files from icomoon app. Filename should be as it is (icon.ext).
	icon.eot
	icon.svg
	icon.ttf
	icon.woff (copy it with icon.woff2)

	and place above files in following location.
	Path: {Vendor}/{Module}/view/adminhtml/web/fonts/

7. Your module acl.xml should look like this. So every module of Magedelight will fall into Base Resource.
	<?xml version="1.0"?>
	<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Acl/etc/acl.xsd">
	    <acl>
	        <resources>
	            <resource id="Magento_Backend::admin">
	                <resource id="Magedelight_Base::root">
	                    <resource id="Magedelight_Base::md_modules">
	                        <resource id="{Vendor}_{Module}::root" title="{Title}" sortOrder="{your_module_sortorder}" />
	                    </resource>
	                </resource>

	                <resource id="Magento_Backend::stores">
	                    <resource id="Magento_Backend::stores_settings">
	                        <resource id="Magento_Config::config">
	                            <resource id="Magedelight_Base::config_root">
	                                <resource id="{Vendor}_{Module}::config_root" title="{Title}" sortOrder="{your_module_sortorder}" />
	                            </resource>
	                        </resource>
	                    </resource>
	                </resource>
	            </resource>
	        </resources>
	    </acl>
	</config>

8. update your module composer.json file with following content
	...
	"require": {
		...
		"magedelight/base": "*",
		...
	}

9. update etc/module.xml to have following sequence.
	<sequence>
		...
		<module name="Magedelight_Base" />
		...
	</sequence>

10. Update documentation link to your menu.xml file by adding following 2 menu items at last

	<add id="{Vendor}_{Module}::useful_links"
        title="Useful Links"
        module="{Vendor}_{Module}"
        sortOrder="999"
        parent="{Vendor}_{Module}::{<small_caps>module_name}_root"
        resource="{Vendor}_{Module}::{<small_caps>module_name}_root" />

    <add id="{Vendor}_{Module}::documentation"
        title="Documentation"
        module="{Vendor}_{Module}"
        sortOrder="10"
        target="_blank"
        parent="{Vendor}_{Module}::useful_links"
        resource="{Vendor}_{Module}::{<small_caps>module_name}_root" />

11. Create etc/adminhtml/di.xml file or modify content if exists as below

	<?xml version="1.0"?>
	<!-- 
	/**
	 * Magedelight
	 * Copyright (C) 2019 Magedelight <info@magedelight.com>
	 *
	 * @category Magedelight
	 * @package {Vendor}_{Module}
	 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
	 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
	 * @author Magedelight <info@magedelight.com>
	 */ 
	 -->
	<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
	    <type name="Magento\Backend\Model\Menu\Item">
		    <plugin name="md_{<small_caps>module_name}_menu_item_newtab" type="{Vendor}\{Module}\Plugin\Magento\Backend\Model\Menu\Item" />
		</type>
	</config>

12. Create Plugin\Magento\Backend\Model\Menu\Item.php with followng code
	
	<?php
	/**
	 * Magedelight
	 * Copyright (C) 2019 Magedelight <info@magedelight.com>
	 *
	 * @category Magedelight
	 * @package {Vendor}_{Module}
	 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
	 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
	 * @author Magedelight <info@magedelight.com>
	 */

	namespace {Vendor}\{Module}\Plugin\Magento\Backend\Model\Menu;

	class Item
	{
	    public function afterGetUrl($subject, $result)
	    {
	        $menuId = $subject->getId();
	        
	        if ($menuId == '{Vendor}_{Module}::documentation') {
	            $result = 'http://docs.magedelight.com/{documentation_url}';
	        }
	        
	        return $result;
	    }
	}
