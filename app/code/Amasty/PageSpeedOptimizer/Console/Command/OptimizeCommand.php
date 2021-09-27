<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Console\Command;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OptimizeCommand for Images Queue Optimization via console
 *
 * @package Amasty\PageSpeedOptimizer
 */
class OptimizeCommand extends ConsoleCommand
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\ForceOptimization
     */
    private $forceOptimization;

    /**
     * @var \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\Image\ForceOptimization $forceOptimization,
        \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface $queueRepository,
        $name = null
    ) {
        parent::__construct($name);
        $this->forceOptimization = $forceOptimization;
        $this->queueRepository = $queueRepository;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('amasty:optimizer:optimize')->setDescription('Run image optimization script.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueSize = $this->queueRepository->getQueueSize();
        $counter = 0;

        /** @var \Symfony\Component\Console\Helper\ProgressBar $progressBar */
        $progressBar = ObjectManager::getInstance()->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            [
                'output' => $output,
                'max' => ceil($queueSize/100)
            ]
        );
        $progressBar->setFormat(
            '<info>%message%</info> %current%/%max% [%bar%]'
        );
        $output->writeln('<info>Optimization Process Started.</info>');
        $progressBar->start();
        $progressBar->display();

        while (!$this->queueRepository->isQueueEmpty()) {
            $progressBar->setMessage('Process Images ' . (($counter++) * 100) . ' from ' . $queueSize . '...');
            $progressBar->display();
            $this->forceOptimization->execute(100);
            $progressBar->advance();
        }
        $progressBar->setMessage('Process Images ' . $queueSize . ' from ' . $queueSize . '...');
        $progressBar->display();
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Images were optimized successfully.</info>');
    }
}
