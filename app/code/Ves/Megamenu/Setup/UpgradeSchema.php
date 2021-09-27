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

namespace Ves\Megamenu\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableItems = $installer->getTable('ves_megamenu_item');


        //Update for version 1.1.5
        if (version_compare($context->getVersion(), '1.1.3', '<')) {
                $installer->getConnection()->addColumn(
                    $tableItems,
                    'tab_position',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'comment'  => 'Tab Position'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'before_html',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => '2M',
                        'nullable' => true,
                        'comment'  => 'Before Html'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'after_html',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => '2M',
                        'nullable' => true,
                        'comment'  => 'After Html'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'caret',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'comment'  => 'Caret'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'hover_caret',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'comment'  => 'Hover Caret'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'sub_height',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'comment'  => 'Sub Height'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'hover_icon',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Hover Icon'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableItems,
                    'dropdown_bgcolor',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Dropdown Background Color'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableItems,
                    'dropdown_bgimage',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Dropdown Bakground Image'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableItems,
                    'dropdown_bgimage',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Dropdown Bakground Image'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableItems,
                    'dropdown_bgimagerepeat',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Dropdown Bakground Image Repeat'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'dropdown_bgpositionx',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Dropdown Background Position X'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableItems,
                    'dropdown_bgpositiony',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Dropdown Background Position Y'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableItems,
                    'dropdown_inlinecss',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Dropdown Inline CSS'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableItems,
                    'parentcat',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Parent Category'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'animation_in',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Animation In'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'animation_time',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Animation Time'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'caret',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Caret'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableItems,
                    'hover_caret',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'comment'  => 'Hover Caret'
                    ]
                );

                $installer->getConnection()->modifyColumn(
                    $tableItems,
                    'id',
                    [
                        'type'           => Table::TYPE_BIGINT,
                        'auto_increment' => true,
                        'primary'        => true,
                        'nullable'       => false
                    ]
                    );

                $tableMenu = $installer->getTable('ves_megamenu_menu');
                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'desktop_template',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'comment'  => 'Desktop Template'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'design',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => '2M',
                        'nullable' => true,
                        'comment'  => 'Design'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'params',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => '2M',
                        'nullable' => true,
                        'comment'  => 'Params'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'disable_iblocks',
                    [
                        'type'     => Table::TYPE_SMALLINT,
                        'nullable' => true,
                        'comment'  => 'Disable Item Blocks'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'event',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'comment'  => 'Event'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'classes',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Classes'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'width',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Width'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'scrolltofixed',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => true,
                        'comment' => 'Scroll to fixed'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'current_version',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Current Version'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableMenu,
                    'mobile_menu_alias',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Mobile menu alias'
                    ]
                );
                $installer->endSetup();
                


                /**
                 * Create table 'ves_megamenu_menu_customergroup'
                 */
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ves_megamenu_menu_customergroup')
                )->addColumn(
                    'menu_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'primary' => true],
                    'Menu ID'
                )->addColumn(
                    'customer_group_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )->addIndex(
                    $installer->getIdxName('ves_megamenu_menu_customergroup', ['customer_group_id']),
                    ['customer_group_id']
                )->setComment(
                    'Menu Custom Group'
                );
                $installer->getConnection()->createTable($table);


                /**
                 * Create table 'ves_megamenu_menu_log'
                 */
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ves_megamenu_menu_log')
                )->addColumn(
                    'log_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'primary' => true, 'identity' => true],
                    'Log ID'
                )->addColumn(
                    'menu_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false],
                    'Menu ID'
                )->addColumn(
                    'version',
                    Table::TYPE_TEXT,
                    '255',
                    ['unsigned' => true],
                    'Menu Data'
                )->addColumn(
                    'menu_data',
                    Table::TYPE_TEXT,
                    '30M',
                    ['unsigned' => true],
                    'Menu Data'
                )->addColumn(
                    'menu_structure',
                    Table::TYPE_TEXT,
                    '30M',
                    ['unsigned' => true],
                    'Menu Structure'
                )->addColumn(
                    'note',
                    Table::TYPE_TEXT,
                    '64k',
                    ['unsigned' => true],
                    'Menu Note'
                )->addColumn(
                    'update_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Menu Modification Time'
                )->setComment(
                    'Menu Log'
                );
                $installer->getConnection()->createTable($table);

                /**
                 * Create table 'ves_megamenu_menu_log'
                 */
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ves_megamenu_cache')
                )->addColumn(
                    'cache_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'primary' => true, 'identity' => true],
                    'Cache ID'
                )->addColumn(
                    'menu_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false],
                    'Menu ID'
                )->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Store ID'
                )->addColumn(
                    'html',
                    Table::TYPE_TEXT,
                    '30M',
                    ['unsigned' => true],
                    'Menu Html'
                )->addColumn(
                    'creation_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Menu Creation Time'
                )->addIndex(
                    $installer->getIdxName('ves_megamenu_cache', ['menu_id']),
                    ['menu_id']
                )->addIndex(
                    $installer->getIdxName('ves_megamenu_cache', ['store_id']),
                    ['store_id']
                )->addForeignKey(
                    $installer->getFkName('ves_megamenu_cache', 'menu_id', 'ves_megamenu_menu', 'menu_id'),
                    'menu_id',
                    $installer->getTable('ves_megamenu_menu'),
                    'menu_id',
                    Table::ACTION_CASCADE
                )->addForeignKey(
                    $installer->getFkName('ves_megamenu_cache', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )->setComment(
                    'Menu Log'
                );
                $installer->getConnection()->createTable($table);
            }
         //Update for version 1.1.5
        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $foreignKeys = $this->getForeignKeys($installer);
            $this->dropForeignKeys($installer, $foreignKeys);
            //$this->alterTables($installer, $foreignKeys);
            //$this->createForeignKeys($installer, $foreignKeys);

            $installer->getConnection()->modifyColumn(
                $installer->getTable('ves_megamenu_menu_customergroup'),
                'customer_group_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'identity' => false
                ]
            );
        }

         //Update for version 1.1.6
        if (version_compare($context->getVersion(), '1.1.6', '<')) {
            $foreignKeys = $this->getForeignKeys($installer);
            $this->dropForeignKeys($installer, $foreignKeys);
            $tableCache = $installer->getTable('ves_megamenu_cache');
            $installer->getConnection()->addColumn(
                $tableCache,
                'cache_key',
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment'  => 'Cache block key'
                ]
            );
        }

        //Update for version 1.1.7
        if (version_compare($context->getVersion(), '1.1.7', '<')) {
            $foreignKeys = $this->getForeignKeys($installer);
            $this->dropForeignKeys($installer, $foreignKeys);
            $installer->getConnection()->addColumn(
                $tableItems,
                'child_col_type',
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 50,
                    'comment'  => 'Child Column type: normal or bootstrap'
                ]
            );
        }
        //Update for version 1.1.8
        if (version_compare($context->getVersion(), '1.1.8', '<')) {
            $installer->getConnection()->addColumn(
                $tableItems,
                'submenu_sorttype',
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 50,
                    'comment'  => 'Sub menu sort type'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableItems,
                'isgroup_level',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'length' => 4,
                    'comment'  => 'Is Group Level for sub menu items with type parent category'
                ]
            );
        }
        //Update for version 1.1.9
        if (version_compare($context->getVersion(), '1.1.9', '<')) {
             $installer->getConnection()->modifyColumn(
                $installer->getTable('ves_megamenu_menu'),
                'structure',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '30M'
                ]
            );
            $installer->getConnection()->modifyColumn(
                $installer->getTable('ves_megamenu_menu'),
                'html',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '30M'
                ]
            );
            $installer->getConnection()->modifyColumn(
                $installer->getTable('ves_megamenu_cache'),
                'html',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '30M'
                ]
            );
            $installer->getConnection()->modifyColumn(
                $installer->getTable('ves_megamenu_menu_log'),
                'menu_data',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '30M'
                ]
            );
            $installer->getConnection()->modifyColumn(
                $installer->getTable('ves_megamenu_menu_log'),
                'menu_structure',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '30M'
                ]
            );
        }
        
        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param array $keys
     * @return void
     */
    private function dropForeignKeys(SchemaSetupInterface $setup, array $keys)
    {
        foreach ($keys as $key) {
            $setup->getConnection()->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param array $keys
     * @return void
     */
    private function createForeignKeys(SchemaSetupInterface $setup, array $keys)
    {
        foreach ($keys as $key) {
            $setup->getConnection()->addForeignKey(
                $key['FK_NAME'],
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $key['REF_TABLE_NAME'],
                $key['REF_COLUMN_NAME'],
                $key['ON_DELETE']
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return array
     */
    private function getForeignKeys(SchemaSetupInterface $setup)
    {
        $foreignKeys = [];
        $keysTree = $setup->getConnection()->getForeignKeysTree();
        foreach ($keysTree as $indexes) {
            foreach ($indexes as $index) {
                if ($index['REF_TABLE_NAME'] == $setup->getTable('customer_group')
                    && $index['REF_COLUMN_NAME'] == 'customer_group_id'
                ) {
                    $foreignKeys[] = $index;
                }
            }
        }
        return $foreignKeys;
    }
}