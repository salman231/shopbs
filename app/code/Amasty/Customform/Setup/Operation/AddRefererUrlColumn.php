<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Customform\Api\Data\FormInterface;
use Amasty\Customform\Api\Data\AnswerInterface;

class AddRefererUrlColumn
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('am_customform_answer');
        $setup->getConnection()->addColumn(
            $tableName,
            AnswerInterface::REFERER_URL,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'comment' => 'Referer url of response'
            ]
        );

        $tableName = $setup->getTable('am_customform_form');
        $setup->getConnection()->addColumn(
            $tableName,
            FormInterface::SAVE_REFERER_URL,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Save referer url'
            ]
        );
    }
}
