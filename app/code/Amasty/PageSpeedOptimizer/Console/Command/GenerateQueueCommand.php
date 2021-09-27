<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

/**
 * Class GenerateQueueCommand for Generate Images Queue via console
 *
 * @package Amasty\PageSpeedOptimizer
 */
class GenerateQueueCommand extends ConsoleCommand
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\GenerateQueue
     */
    private $generateQueue;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\Image\GenerateQueue $generateQueue,
        $name = null
    ) {
        parent::__construct($name);
        $this->generateQueue = $generateQueue;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('amasty:optimizer:generate-queue')
            ->setDescription('Generate Images Queue for Optimization.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->generateQueue->generateQueue();
        $output->writeln(
            $count . ' images added to queue. Run `php bin/magento amasty:optimizer:optimize`'
                . ' to optimize images'
        );
    }
}
