<?php
namespace Webkul\Rmasystem\Ui\Component\Listing\Column\Allrma;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Customer extends \Magento\Ui\Component\Listing\Columns\Column
{

    const ALT_FIELD = 'name';
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
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->_getModel = $model;
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
            foreach ($dataSource['data']['items'] as & $item) {
                $fieldName = $this->getData('name');
                $custometId = $this->_objectManager->get(\Webkul\Rmasystem\Model\Allrma::class)
                    ->load($item['rma_id'])->getCustomerId();
                $custometModel = $this->_objectManager->get(\Magento\Customer\Model\Customer::class)->load($custometId);
                $name = $custometModel->getFirstname().' '.$custometModel->getLastname();
                if ($name == '' || $name == ' ') {
                    $name = 'Guest User';
                }
                $item['name'] = $name;
            }
        }
        return $dataSource;
    }
}
