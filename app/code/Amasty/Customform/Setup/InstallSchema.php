<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->createFormTable($setup);
        $this->createAnswerTable($setup);
        $setup->endSetup();
    }

    protected function createFormTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('am_customform_form'))
            ->addColumn(
                'form_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Form Title'
            )
            ->addColumn(
                'code',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Form Code'
            )
            ->addColumn(
                'success_url',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Success Url'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Form Status'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Form Creation Time'
            )
            ->addColumn(
                'customer_group',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Customer Group'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Stores'
            )
            ->addColumn(
                'send_notification',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Send Notification'
            )
            ->addColumn(
                'send_to',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Send Notification To'
            )->addColumn(
                'email_template',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Email Template'
            )
            ->addColumn(
                'submit_button',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Submit Button Text'
            )
            ->addColumn(
                'success_message',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Success Message'
            )
            ->addColumn(
                'form_json',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Form json'
            )->addIndex(
                $installer->getIdxName('am_customform_form', ['form_id']),
                ['form_id']
            );
        $installer->getConnection()->createTable($table);
    }

    protected function createAnswerTable(SchemaSetupInterface $installer)
    {
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('am_customform_answer'))
            ->addColumn(
                'answer_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'form_id',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Form Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Store'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Form Creation Time'
            )
            ->addColumn(
                'ip',
                Table::TYPE_TEXT,
                20,
                ['default' => '', 'nullable' => false],
                'IP'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Customer Id'
            )
            ->addColumn(
                'response_json',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Response json'
            )->addIndex(
                $installer->getIdxName('am_customform_answer', ['answer_id']),
                ['form_id']
            );
        $installer->getConnection()->createTable($table);
    }
}
