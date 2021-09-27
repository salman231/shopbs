<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

/**
 * Class ForceOptimization
 *
 * @package Amasty\PageSpeedOptimizer
 */
class ForceOptimization
{
    /**
     * @var Process
     */
    private $imageProcess;

    /**
     * @var \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface $queueRepository,
        \Amasty\PageSpeedOptimizer\Model\Image\Process $imageProcess
    ) {
        $this->imageProcess = $imageProcess;
        $this->queueRepository = $queueRepository;
    }

    /**
     * @param int $limit
     *
     * @return void
     */
    public function execute($limit)
    {
        foreach ($this->queueRepository->shuffleQueues($limit) as $queue) {
            $this->imageProcess->execute($queue);
        }
    }
}
