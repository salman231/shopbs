<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Model\Import;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Bss\Simpledetailconfigurable\Model\PreselectKeyFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Preselect extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    /**
     * Error code missing column
     */
    const ERROR_CODE_MISSING_COLUMNS = 'missingColumns';

    /**
     * Error code invalid attribute
     */
    const ERROR_INVALID_ATTRIBUTE= 'invalidAttribute';

    /**
     * @var array
     */
    protected $errorMessageTemplates = [
        self::ERROR_CODE_SYSTEM_EXCEPTION => 'General system exception happened',
        self::ERROR_CODE_COLUMN_NOT_FOUND => 'We can\'t find required columns: %s.',
        self::ERROR_CODE_COLUMN_EMPTY_HEADER => 'Columns number: "%s" have empty headers',
        self::ERROR_CODE_COLUMN_NAME_INVALID => 'Column names: "%s" are invalid',
        self::ERROR_CODE_ATTRIBUTE_NOT_VALID => "Please correct the value for '%s'.",
        self::ERROR_CODE_DUPLICATE_UNIQUE_ATTRIBUTE => "Duplicate Unique Attribute for '%s'",
        self::ERROR_CODE_ILLEGAL_CHARACTERS => "Illegal character used for attribute %s",
        self::ERROR_CODE_INVALID_ATTRIBUTE => 'Header contains invalid attribute(s): "%s"',
        self::ERROR_CODE_WRONG_QUOTES => "Curly quotes used instead of straight quotes",
        self::ERROR_CODE_COLUMNS_NUMBER => "Number of columns does not correspond to the number of rows in the header",
        self::ERROR_CODE_MISSING_COLUMNS => "Missing Column(s): %s",
        self::ERROR_INVALID_ATTRIBUTE => 'Invalid preselect attribute',
    ];

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::ERROR_INVALID_ATTRIBUTE => 'Invalid preselect attribute',
    ];

    /**
     * @var array
     */
    protected $validColumnNames = [
        'sku',
        'preselect'
    ];

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;

    /**
     * @var PreselectKeyFactory
     */
    private $preselectKeyFactory;

    /**
     * @var Preselect\Validator\Attribute
     */
    private $attributeValidator;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * @var string
     */
    private $entityTypeCode = 'catalog_product';

    /**
     * SdcpPreselect constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param Preselect\Validator\Attribute $attributeValidator
     * @param PreselectKeyFactory $preselectKeyFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param string $entityTypeCode
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        Preselect\Validator\Attribute $attributeValidator,
        PreselectKeyFactory $preselectKeyFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        $entityTypeCode = 'sdcp_preselect'
    ) {
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator
        );

        $this->entityTypeCode = $entityTypeCode;
        $this->productMetadata = $productMetadata;
        $this->preselectKeyFactory = $preselectKeyFactory;
        $this->attributeValidator = $attributeValidator;
        $this->serialize = $serialize;
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->entityTypeCode;
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE !== $this->getBehavior()) {
            if (!isset($rowData['sku']) || $rowData['sku'] === '') {
                $this->addRowError(static::ERROR_CODE_MISSING_COLUMNS, $rowNum, 'sku');
                return false;
            }
        }
        if (!isset($rowData['preselect']) || $rowData['preselect'] === '') {
            $this->addRowError(static::ERROR_CODE_MISSING_COLUMNS, $rowNum, 'preselect');
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteSdcpPreselect();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceSdcpPreselect();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveSdcpPreselect();
        }

        return true;
    }

    /**
     * Validate data rows and save bunches to DB
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();
        $countBunchRow = 0;
        $source->rewind();
        $this->_dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen($this->serialize->serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;

                if ($this->validateRow($rowData, $source->key())) {
                    // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = $bunchSize > 0 && $countBunchRow >= $bunchSize;

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $countBunchRow ++;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }
        return $this;
    }

    /**
     * Save product attribute
     * @return void
     */
    protected function saveSdcpPreselect()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $validatedData = $this->attributeValidator->validatePreselect($rowData);
                if (!$validatedData) {
                    $this->addRowError(
                        self::ERROR_INVALID_ATTRIBUTE,
                        $rowNum,
                        'preselect',
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $this->processData($rowData['sku'], $validatedData);
            }
        }
    }

    /**
     * @param string $sku
     * @param array $validatedData
     */
    protected function processData($sku, $validatedData)
    {
        $items = $this->getPreselectCollection(true)
        ->addFieldToFilter('sku', $sku);

        if (isset($items)) {
            $items->walk('delete');
        }
        foreach ($validatedData['value'] as $attribute => $value) {
            $preselectKeyResource = $this->preselectKeyFactory->create()->getResource();
            $preselectKeyResource->savePreselectKey($validatedData['product_id'], $attribute, $value);
        }
    }

    /**
     * Replace product attributes
     * @return void
     */
    protected function replaceSdcpPreselect()
    {
        $this->deleteForReplace();
        $this->saveSdcpPreselect();
    }

    /**
     * Delete product attributes
     * @return $this|bool
     */
    protected function deleteSdcpPreselect()
    {
        $listSku = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowSku = $rowData['sku'];
                    $listSku[$rowNum] = $rowSku;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated() &&
                    version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        $this->getPreselectCollection(true)->addFieldToFilter('sku', ['in' => $listSku])->walk('delete');
        return $this;
    }

    /**
     * Delete product attributes for replace
     * @return $this|bool
     */
    protected function deleteForReplace()
    {
        $this->preselectKeyFactory->create()->getResource()->truncate();
        return $this;
    }

    /**
     * @param bool $joinCatalog
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    private function getPreselectCollection($joinCatalog = false)
    {
        $collection = $this->preselectKeyFactory->create()->getCollection();
        if ($joinCatalog) {
            $collection->joinCatalog();
        }
        return $collection;
    }

    /**
     * Get multiple value separator for import
     * @return string
     */
    public function getMultipleValueSeparator()
    {
        if (!empty($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR])) {
            return $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR];
        }
        return Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
    }
}
