<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Setup\Operation;

use Amasty\Customform\Api\Data\AnswerInterface;

class AddResponseStatus
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
            AnswerInterface::ADMIN_RESPONSE_STATUS,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Response Status',
            ]
        );
    }
}
