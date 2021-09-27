<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Export;

use Amasty\Customform\Model\ResourceModel\Answer\Grid\Collection;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\MassAction\Filter;

class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    /**
     * @var array
     */
    protected $columns;

    /**
     * @param Collection $collection
     * @return array|null
     */
    public function getMainTableColumns(Collection $collection)
    {
        if ($this->columns === null) {
            $this->columns = [];
            $schema = $collection->getConnection()->describeTable($collection->getMainTable());
            foreach ($schema as $column) {
                $this->columns[] = $column['COLUMN_NAME'];
            }
        }

        return $this->columns;
    }

    /**
     * @param Collection $collection
     * @return array|null
     */
    public function getMainTableHeaders(Collection $collection)
    {
        $headers = $this->getMainTableColumns($collection);
        foreach ($headers as $key => $header) {
            $headers[$key] = ucwords(str_replace('_', ' ', $header));
        }

        return $headers;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function getRowData(DocumentInterface $document, $fields, $options)
    {
        $row = parent::getRowData($document, $fields, $options);
        $this->convertJsonData($row, $fields);

        return $row;
    }

    /**
     * @param $row
     * @param $fields
     */
    private function convertJsonData(&$row, $fields)
    {
        $position = array_search('response_json', $fields);
        if ($position && isset($row[$position]) && isset($this->data['serializer']) && $row[$position]) {
            $fields = $this->data['serializer']->unserialize($row[$position]);

            if (!$fields) {
                return;
            }

            $result = [];
            foreach ($fields as $field) {
                if (isset($field['label']) && isset($field['value'])) {
                    $result[] = [$field['label'] => $field['value']];
                }
            }

            // use json_encode for adding JSON_UNESCAPED_UNICODE - fix issue with language convertation
            $row[$position] = json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
}
