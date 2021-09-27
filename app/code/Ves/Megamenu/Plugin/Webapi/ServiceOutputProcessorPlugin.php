<?php
namespace Ves\Megamenu\Plugin\Webapi;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Webapi\ServiceOutputProcessor;

class ServiceOutputProcessorPlugin
{
    protected $filterProvider;

    public function __construct(FilterProvider $filterProvider)
    {
        $this->filterProvider = $filterProvider;
    }

    public function beforeProcess(
        ServiceOutputProcessor $subject,
        $data,
        $serviceClassName,
        $serviceMethodName
    ) {
        if ($serviceClassName == 'Magento\Cms\Api\PageRepositoryInterface' && $serviceMethodName == 'getById') {
            $content = $data->getContent();
            $parsedContent = $this->filterProvider->getPageFilter()->filter($content);
            //$data->setContent($parsedContent);
            return [$data, $serviceClassName, $serviceMethodName];
        }
    }
}