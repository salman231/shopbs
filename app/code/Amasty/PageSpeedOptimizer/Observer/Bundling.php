<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Observer;

use Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Bundle;
use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Bundling for step switching in settings section
 *
 * @package Amasty\PageSpeedOptimizer
 */
class Bundling implements ObserverInterface
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
     * @var TypeListInterface
     */
    private $cache;

    /**
     * @var Bundle
     */
    private $bundleResource;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    public function __construct(
        ManagerInterface $messageManager,
        RequestInterface $request,
        TypeListInterface $cache,
        Bundle $bundleResource,
        WriterInterface $configWriter
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->cache = $cache;
        $this->bundleResource = $bundleResource;
        $this->configWriter = $configWriter;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->request->getParam('am-start-bundling')) {
            $this->configWriter->save('amoptimizer/' . ConfigProvider::BUNDLE_STEP, 1);
            $this->configWriter->save('amoptimizer/' . ConfigProvider::BUNDLE_HASH, $this->getRandHash());
            $this->cache->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
            $this->bundleResource->clear();
        }

        if ($this->request->getParam('am-bundling-step2')) {
            $this->configWriter->save('amoptimizer/' . ConfigProvider::BUNDLE_STEP, 2);
            $this->cache->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        }

        if ($this->request->getParam('am-bundling-step3')) {
            $this->configWriter->save('amoptimizer/' . ConfigProvider::BUNDLE_STEP, 3);
            $this->cache->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        }

        if ($this->request->getParam('am-bundling-step4')) {
            $this->configWriter->save('amoptimizer/' . ConfigProvider::BUNDLE_STEP, null);
            $this->configWriter->save('amoptimizer/' . ConfigProvider::BUNDLE_HASH, null);
            $this->cache->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        }
    }

    /**
     * @return string
     */
    private function getRandHash()
    {
        /** @codingStandardsIgnoreStart */
        mt_srand();
        $hash = md5(mt_rand());
        /** @codingStandardsIgnoreEnd */
        return $hash;
    }
}
