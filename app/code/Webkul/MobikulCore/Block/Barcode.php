<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @Copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Block;

class Barcode extends \Magento\Backend\Block\Template
{
    public $_filter;
    public $_helper;
    public $_ioFile;
    public $_storeManager;
    public $_directoryList;
    public $_helperBarcode;
    public $_collectionFactory;

    /**
     * constructor function
     *
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Webkul\MobikulCore\Helper\Barcode $helperBarcode
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\MobikulCore\Helper\Barcode $helperBarcode,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->_filter = $filter;
        $this->_ioFile = $ioFile;
        $this->_helper = $helper;
        $this->_storeManager  = $storeManager;
        $this->_directoryList = $directoryList;
        $this->_helperBarcode = $helperBarcode;
        $this->_collectionFactory = $collectionFactory;
        $this->fileDriver = $fileDriver;
        parent::__construct($context);
    }
}
