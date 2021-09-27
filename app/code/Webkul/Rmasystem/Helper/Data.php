<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Helper;

use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Http\Context as HttpContext;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * media sub folder
     * @var string
     */
    protected $subDir = 'webkul/rmasystem/RMA';

    protected $labelSubDir = 'webkul/rmasystem/shippinglabel';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $_customFieldFactory;
    
    protected $_fieldvalue;

    protected $_objectManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Rmasystem\Model\CustomfieldFactory $customfield
     * @param \Webkul\Rmasystem\Model\FieldvalueFactory $fieldvalue
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Escaper $_escaper
     * @param Filesystem $fileSystem
     * @param HttpContext $httpContext
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Rmasystem\Model\CustomfieldFactory $customfield,
        \Webkul\Rmasystem\Model\FieldvalueFactory $fieldvalue,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Escaper $_escaper,
        Filesystem $fileSystem,
        HttpContext $httpContext,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->_escaper = $_escaper;
        $this->storeManager = $storeManager;
        $this->fileSystem = $fileSystem;
        $this->_customFieldFactory = $customfield;
        $this->_fieldvalue = $fieldvalue;
        $this->_objectManager = $objectManager;
        $this->httpContext = $httpContext;
        $this->categoryCollectionFactory = $categoryFactory;
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'rmasystem/parameter/'.$field;

        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()
        );
    }
    
    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseDirRead()
    {
        return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/');
    }
    
    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->subDir.'/';
    }

    /**
     * get images base url
     *
     * @return string
     */
    public function getLabelBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->labelSubDir.'/image/';
    }

    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir($lastRmaId)
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath(
                $this->subDir.'/'.$lastRmaId.'/image/'
            );
    }

    /**
     * get base image dir
     *
     * @return string
     */
    public function getConversationDir($lastRmaId)
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath(
                $this->subDir.'/conversation/'.$lastRmaId.'/'
            );
    }

    public function getBarcodeDir()
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/Barcodes/');
    }
    /**
     * get images base url
     *
     * @return string
     */
    public function getBarcodeBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->subDir.'/Barcodes/';
    }

    /**
     * get images base url
     *
     * @return string
     */
    public function getConversationUrl($rmaId)
    {
        return $this->_urlBuilder->getBaseUrl(
            ['_type' => UrlInterface::URL_TYPE_MEDIA]
        ).$this->subDir.'/conversation/'.$rmaId.'/';
    }

    /**
     * return getResolutionTypes array
     *
     * @param integer $status //invoice status
     * @return array
     */
    public function getResolutionTypes($status = 0)
    {
        if ($status==1) {
            return [
                ['value' => '1', 'label' =>  __('Exchange')],
                ['value' => '3', 'label' => __('Cancel Items')]
            ];
        } elseif ($status == 2) {
            return [
                ['value'=>'0', 'label' =>  __('Refund')],
                ['value' => '1', 'label' =>  __('Exchange')]
            ];
        } else {
            return [
                    ['value'=>'0', 'label' =>  __('Refund')],
                    ['value' => '1', 'label' =>  __('Exchange')],
                    ['value' => '3', 'label' => __('Cancel Items')]
                ];
        }
    }

    public function getDeliveryStatus($type)
    {
        $resoulution = [
          ['value'=>'0', 'label' =>  __('Not Delivered')],['value' => '1', 'label' =>  __('Delivered')]
        ];
        return $resoulution;
    }

    /**
     * Get Order Status Title
     *
     * @param int $status
     *
     * @return string
     */
    public function getRmaOrderStatusTitle($status)
    {
        if ($status ==  1) {
            $orderStatus =  __("Delivered");
        } else {
            $orderStatus =  __("Not Delivered");
        }
        return $orderStatus;
    }

    /**
     * Get RMA Status Title
     *
     * @param int $status
     * @param int $finalStatus
     *
     * @return string
     */
    public function getRmaStatusTitle($status, $finalStatus = 0)
    {
        if ($finalStatus == 0) {
            if ($status == 1) {
                $rmaStatus =  __("Processing");
            } elseif ($status == 2) {
                $rmaStatus =  __("Solved");
            } elseif ($status == 3) {
                $rmaStatus =  __("Declined");
            } elseif ($status == 4) {
                $rmaStatus =  __("Cancelled");
            } else {
                $rmaStatus =  __("Pending");
            }
        } else {
            if ($finalStatus == 1) {
                $rmaStatus =  __("Cancelled");
            } elseif ($finalStatus == 2) {
                $rmaStatus =  __("Declined");
            } elseif ($finalStatus == 3 || $finalStatus == 4) {
                $rmaStatus =  __("Solved");
            } else {
                $rmaStatus =  __("Pending");
            }
        }
        return $rmaStatus;
    }

    /**
     * Get Seller's All Status
     *
     * @param int $status
     *
     * @return array
     */
    public function getAllStatus($resolutionType = 0)
    {
        if ($resolutionType == 3) {
            $allStatus = [
                0 => __('Pending'),
                5 => __('Declined'),
                6 => __('Item Cancelled')
            ];
        } elseif ($resolutionType == 0) {
            $allStatus = [
                0 => __('Pending'),
                1 => __('RMA Approved'),
                5 => __('Declined'),
                6 => __('Refund Initiated')
            ];
        } elseif ($resolutionType == 1) {
            $allStatus = [
                0 => __('Pending'),
                1 => __('RMA Approved'),
                2 => __('Received Package'),
                3 => __('Dispatched Package'),
                5 => __('Declined'),
                6 => __('Solved')
            ];
        } else {
            $allStatus = [
                0 => __('Pending'),
                1 => __('RMA Approved'),
                5 => __('Declined'),
                6 => __('Solved')
            ];
        }
        return $allStatus;
    }

    public function getAdminStatusTitle($status, $resolutionType)
    {
        $allStatus = $this->getAllStatus($resolutionType);
        return $allStatus[$status];
    }

    /**
     * get custom form fields
     *
     * @return collection
     */
    public function getFields()
    {
        $collection = $this->_customFieldFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('status', 1);
        $collection->setOrder('sort', 'ASC');
        return $collection;
    }

    public function getFieldData($rma_id)
    {
        $collection = $this->_fieldvalue->create()->getCollection()
                    ->addFieldToFilter('rma_id', $rma_id);
        $joinTable = $this->_objectManager->create(\Magento\Framework\App\ResourceConnection::class)
                    ->getTableName("wk_rma_customfield");
        $sql = "cf.id = main_table.field_id";
        $collection->getSelect()->join($joinTable.' as cf', $sql, ['input_type', 'label', 'select_option']);
        return $collection;
    }

    public function getText($data)
    {
        if ($data->getRequired()==1) {
            $required ='required clear-section';
        } else {
            $required ='';
        }
        $field = "<div class='field ".$required."'><label class='label'><span>".
                $this->escapeHtml($data->getLabel()).":</span></label>";
        $field = $field."<div class='control'><input type ='text' value='' class='".$required."' name='".
                $this->escapeHtml($data->getInputname())."'></div></div>";
        return $field;
    }

    public function getTextarea($data)
    {
        if ($data->getRequired()==1) {
            $required ='required clear-section';
        } else {
            $required ='';
        }
        $field = "<div class='field ".$required."'><label class='label'><span>".
                $this->escapeHtml($data->getLabel()).":</span></label>";
        $field = $field."<div class='control'><textarea name='".
                $this->escapeHtml($data->getInputname())."' class='".$required."'></textarea></div></div>";
        return $field;
    }

    public function getSelect($data)
    {
        if ($data->getRequired()==1) {
            $required ='required clear-section';
        } else {
            $required ='';
        }
        $options = explode(",", $data->getSelectOption());
        $value ='<option value="">'.__('Select').'</option>';
        foreach ($options as $key) {
            $tmp = explode("=>", $key);
            if (count($tmp)==2) {
                $value = $value."<option value=".$this->escapeHtml($tmp[0]).">".
                        $this->escapeHtml($tmp[1])."</option>";
            }
        }
        $field = "<div class='field ".$required."'><label class='label'><span>".
                $this->escapeHtml($data->getLabel())."</span></label>";
        $field = $field."<div class='control'><select name='".
                $this->escapeHtml($data->getInputname())."' class='select ".$required."' >".
                $value."</select></div></div>";
        return $field;
    }

    public function getMultiselect($data)
    {
        if ($data->getRequired()==1) {
            $required ='required clear-section ';
        } else {
            $required ='';
        }
        $options = explode(",", $data->getSelectOption());
        $value ='';
        foreach ($options as $key) {
            $tmp = explode("=>", $key);
            if (count($tmp)==2) {
                $value = $value."<option value=".$this->escapeHtml($tmp[0]).">".
                        $this->escapeHtml($tmp[1])."</option>";
            }
        }
        $field = "<div class='field ".$required."'><label class='label'><span>".
                $this->escapeHtml($data->getLabel())."</span></label>";
        $field = $field."<div class='control'><select multiple name='".
                $this->escapeHtml($data->getInputname())."[]' class='".$required."wk-rma-multiple' size='".
                count($options)."'>".$value."</select></div></div>";
        return $field;
    }

    public function getRadio($data)
    {
        if ($data->getRequired()==1) {
            $required ='required clear-section';
        } else {
            $required ='';
        }
        $options = explode(",", $data->getSelectOption());
        $value ='<p></p>';
        foreach ($options as $key) {
            $tmp = explode("=>", $key);
            if (count($tmp)==2) {
                $value = $value."<input type='radio' class='".$required."' name='".
                    $this->escapeHtml($data->getInputname())."' value=".$this->escapeHtml($tmp[0])."><span>".
                    $this->escapeHtml($tmp[1])."</span></br>";
            }
        }
        $field = "<div class='field ".$required."'><label class='label'><span>".
                $this->escapeHtml($data->getLabel())."</span></label>";
        $field = $field.$value."</div>";
        return $field;
    }

    public function getCheckbox($data)
    {
        if ($data->getRequired()==1) {
            $required ='required clear-section';
        } else {
            $required ='';
        }
        $options = explode(",", $data->getSelectOption());
        $value ='<p></p>';
        foreach ($options as $key) {
            $tmp = explode("=>", $key);
            if (count($tmp)==2) {
                $value = $value."<input type='checkbox' class='".$required."' name='".
                        $this->escapeHtml($data->getInputname())."[]' value=".
                        $this->escapeHtml($tmp[0])."><span>".$this->escapeHtml($tmp[1])."</span></br>";
            }
        }
        $field = "<div class='field ".$required."'><label class='label'><span>".
                $this->escapeHtml($data->getLabel())."</span></label>";
        $field = $field.$value."</div>";
        return $field;
    }

    public function escapeHtml($string)
    {
        return $this->_escaper->escapeHtml($string);
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * function to check is category allowed for rma
     *
     * @param [array] $catIds
     * @return boolean
     */
    public function isCategoryAllowed($catIds)
    {
        $flag = false;
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in'=>$catIds]);
        $collection->addAttributeToSelect('rma_allowed');
        foreach ($collection as $key) {
            if (($key->getRmaAllowed() === null) || $key->getRmaAllowed()) {
                $flag = true;
                break;
            }
        }
        return $flag;
    }
}
