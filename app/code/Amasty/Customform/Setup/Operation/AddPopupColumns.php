<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Customform\Api\Data\FormInterface;

class AddPopupColumns
{
    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('am_customform_form');
        $setup->getConnection()->addColumn(
            $tableName,
            FormInterface::POPUP_SHOW,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Use popup for form',
            ]
        );
        $setup->getConnection()->addColumn(
            $tableName,
            FormInterface::POPUP_BUTTON,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Button for trigger form',
            ]
        );
    }
}
