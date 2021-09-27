<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Observer;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\Image\GenerateQueue;
use Amasty\PageSpeedOptimizer\Model\OptionSource\GifOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\JpegOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PngOptimization;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Queue checking if `Generate Queue` clicked
 *
 * @package Amasty\PageSpeedOptimizer
 */
class Queue implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var GenerateQueue
     */
    private $generateQueue;

    public function __construct(
        ManagerInterface $messageManager,
        RequestInterface $request,
        GenerateQueue $generateQueue
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->generateQueue = $generateQueue;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->request->getParam('am-gen-queue')) {
            $this->generateQueue->generateQueue();
            $this->messageManager->addSuccessMessage(__('Image Queue was generated.'));
        }
    }
}
