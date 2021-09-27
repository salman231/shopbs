<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Customform\Model\AnswerRepository;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Amasty\Customform\Model\Config\Source\Status;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AnswerRepository $answerRepository,
        ConfigInterface $resourceConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->answerRepository = $answerRepository;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.7.0', '<')) {
            $this->upgradeResponseStatus($setup);
            $this->clearBookmarks($setup);
        }
    }

    /**
     * @param $setup
     */
    private function upgradeResponseStatus($setup)
    {
        $answers = $this->answerRepository->getList();
        foreach ($answers as $answer) {
            if (!$answer->getAdminResponseEmail()) {
                $answer->setAdminResponseStatus(Status::PENDING);
            } else {
                $answer->setAdminResponseStatus(Status::ANSWERED);
            }
            $this->answerRepository->save($answer);
        }
    }

    /**
     * @param $setup
     */
    private function clearBookmarks($setup)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resourceConfig->getConnection();

        $connection->delete(
            $this->resourceConfig->getTable('ui_bookmark'),
            'namespace = "amasty_customform_forms_listing"'
        );
    }
}
