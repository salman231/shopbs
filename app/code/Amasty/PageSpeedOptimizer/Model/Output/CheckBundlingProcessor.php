<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

/**
 * Class CheckBundlingProcessor checks if bundling process in progress
 *
 * @package Amasty\PageSpeedOptimizer
 */
class CheckBundlingProcessor implements OutputProcessorInterface
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        if ($hash = $this->request->getParam('amoptimizer_bundle_check')) {
            if ($hash === $this->configProvider->getBundleHash()) {
                $bundleUrl = $this->storeManager->getStore()->getBaseUrl() . 'amoptimizer/bundle/modules';

                $output .= '<script>'
                    . 'require(["jquery", "underscore", "domReady!"], function($, _) {'
                    . '    window.setTimeout(function() {'
                    . '        var dat = _.keys(require.s.contexts._.urlFetched);'
                    . '        _.each(require.s.contexts._.defined, function (val, key) {'
                    . '            if (key.substr(0, 5) === "text!") {'
                    . '                dat.push(require.toUrl(key.substr(5)));'
                    . '            }'
                    . '        });'
                    . '        $.post("' . $bundleUrl . '", {data: JSON.stringify(dat)}, function () {'
                    . '            alert("' . __('You can close window') . '");'
                    . '        });'
                    . '    }, 2000);'
                    . '});'
                    . '</script>';
            }
        }
    }
}
