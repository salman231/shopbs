<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Setup\Operation;

use Amasty\Customform\Api\Data\AnswerInterface;
use Amasty\Customform\Api\Data\FormInterface;

class AddEmailResponse
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('am_customform_answer');
        $setup->getConnection()->addColumn(
            $tableName,
            AnswerInterface::ADMIN_RESPONSE_EMAIL,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'comment' => 'Response Email',
            ]
        );
        $setup->getConnection()->addColumn(
            $tableName,
            AnswerInterface::ADMIN_RESPONSE_MESSAGE,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'comment' => 'Response Message',
            ]
        );

        $tableName = $setup->getTable('am_customform_form');
        $setup->getConnection()->addColumn(
            $tableName,
            FormInterface::EMAIL_FIELD,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'comment' => 'Email Field Code',
            ]
        );
        $setup->getConnection()->addColumn(
            $tableName,
            FormInterface::EMAIL_FIELD_HIDE,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Hide Email Field for Registered Customers',
            ]
        );
    }
}
