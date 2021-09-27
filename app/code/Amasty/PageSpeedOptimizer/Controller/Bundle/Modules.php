<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Bundle;

use Amasty\PageSpeedOptimizer\Model\Bundle\BundleFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Bundle as BundleResource;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Theme\Model\Theme\Resolver;

/**
 * Class Modules saves static files for future bundling
 *
 * @package Amasty\PageSpeedOptimizer
 */
class Modules extends \Magento\Framework\App\Action\Action
{
    /**
     * @var BundleFactory
     */
    private $bundleFactory;

    /**
     * @var BundleResource
     */
    private $bundleResource;

    /**
     * @var Resolver
     */
    private $themeResolver;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    public function __construct(
        BundleFactory $bundleFactory,
        BundleResource $bundleResource,
        Resolver $themeResolver,
        LocaleResolver $localeResolver,
        Context $context
    ) {
        parent::__construct($context);
        $this->bundleFactory = $bundleFactory;
        $this->bundleResource = $bundleResource;
        $this->themeResolver = $themeResolver;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        if ($data = $this->getRequest()->getParam('data')) {
            $data = json_decode($data, true);
            $theme = $this->themeResolver->get();
            foreach ($data as $item) {
                /** @var \Amasty\PageSpeedOptimizer\Model\Bundle\Bundle $file */
                $file = $this->bundleFactory->create();
                $file->setFilename(preg_replace('/.*?\/[a-z]{2}_[A-Z]{2}\/(.*?)$/is', '$1', $item))
                    ->setLocale($this->localeResolver->getLocale())
                    ->setArea($theme->getArea())
                    ->setTheme($theme->getCode());

                try {
                    $this->bundleResource->save($file);
                } catch (\Exception $exception) {
                    null;
                }
            }
        }

        return $result->setContents('OK');
    }
}
