<?php

namespace Webkul\Rmasystem\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';

    const ALT_FIELD = 'name';

    const LABEL_URL_PATH_EDIT = 'rmasystem/shippinglabel/edit';

    private $_getModel;
    /**
     * @var string
     */
    private $editUrl;

    private $_objectManager = null;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Webkul\Rmasystem\Model\Shippinglabel $model,
        \Webkul\Rmasystem\Model\Shippinglabel\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $components = [],
        array $data = [],
        $editUrl = self::LABEL_URL_PATH_EDIT
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->_getModel = $model;
        $this->editUrl = $editUrl;
        $this->_objectManager = $objectManager;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $filename = $this->_objectManager->get(\Webkul\Rmasystem\Model\Shippinglabel::class)
                            ->load($item['id'])->getFilename();
                $item[$fieldName . '_src'] = $this->imageHelper->getBaseUrl().$filename;
                $item[$fieldName . '_alt'] = $item['title'];
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['id']]);
                $item[$fieldName . '_orig_src'] = $this->imageHelper->getBaseUrl().$filename;
            }
        }

        return $dataSource;
    }
}
