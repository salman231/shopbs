<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MagentoChatSystem\Model\Agent;

use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData = [];

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
         
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }
        
        $data = $this->dataPersistor->get('agent_data');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data->getData());
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('agent_data');
        }
        return $this->loadedData;
    }
}
