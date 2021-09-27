<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Console\Command;

use Exception;
use Magento\Framework\App\State;
use Mageplaza\AutoRelated\Model\Indexer\RuleIndexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Reindex
 * @package Mageplaza\AutoRelated\Console\Command
 */
class Reindex extends Command
{
    /**
     * @var State
     */
    protected $appState;

    /**
     * @var RuleIndexer
     */
    protected $ruleIndexer;

    /**
     * Reindex constructor.
     *
     * @param State $appState
     * @param RuleIndexer $ruleIndexer
     * @param string|null $name
     */
    public function __construct(
        State $appState,
        RuleIndexer $ruleIndexer,
        string $name = null
    ) {
        $this->appState    = $appState;
        $this->ruleIndexer = $ruleIndexer;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mpautorelated:reindex')
            ->setDescription('Reindex Auto Related Product');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('frontend');
            $this->ruleIndexer->reindexFull();
            $output->writeln('<info>Reindex Successfully!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
