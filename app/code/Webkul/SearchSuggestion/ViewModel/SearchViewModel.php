<?php
namespace Webkul\SearchSuggestion\ViewModel;

class SearchViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $helperFactory
    ) {
        $this->helperFactory = $helperFactory;
    }
     /* @param String $className
     * @return object
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className .
             ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
