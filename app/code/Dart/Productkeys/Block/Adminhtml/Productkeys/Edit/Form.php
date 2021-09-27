<?php
/**
 * Dart_Productkeys Add New Row Form Admin Block.
 * @package    Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Block\Adminhtml\Productkeys\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Dart\Productkeys\Model\Status;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;


class Form extends Generic
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Status $options,
        array $data = []
		
    ) {
        $this->_options = $options;
		$this->_request = $context->getRequest();
        parent::__construct($context, $registry, $formFactory, $data);
		
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
	   {
		   $whichPage=$this->_coreRegistry->registry('whichPage');
		   $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
		   $vendorName = "Dart";
		   $moduleName = "Productkeys";
		   $path = $this->_assetRepo->getUrl($vendorName."_".$moduleName."::images/Sample_File.csv");
		   $model = $this->_coreRegistry->registry('row_data');
		   	$form = $this->_formFactory->create([
				'data' => [
					'id' => 'edit_form',
					'enctype' => 'multipart/form-data',
					'action' => $this->getData('action'),
					'method' => 'post'
				]
			]);
		   $form->setHtmlIdPrefix('dartproductkeys_');
			if($whichPage=="importCSV"){
				//Form For Keys Import CSV File      

            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Import CSV'), 'class' => 'fieldset-wide']
            );
        
      

       $importdata_script = $fieldset->addField(
            'browse-csv',
            'file',
            [
                'name' => 'browse-csv',
                'label' => 'Browse File To Import',
                'title' => __('Browse CSV'),
                'class' => 'browse-csv',
				'note' => 'Allowed File types: .csv and .xls',
				'required' => true
                
            ]
        );	

		
		$importdata_script->setAfterElementHtml("   

			<div><br/><span id='sample-file-span'><a id='sample-file-link' href='".$path."'  >Download Sample File</a></span></div>

            <script type=\"text/javascript\">
			
            document.getElementById('".strtolower($vendorName).strtolower($moduleName)."_browse-csv').onchange = function () { 

                var fileInput = document.getElementById('".strtolower($vendorName).strtolower($moduleName)."_browse-csv');

                var filePath = fileInput.value;

                var allowedExtensions = /(\.csv|\.xls)$/i; 

                if(!allowedExtensions.exec(filePath))
                {
                    alert('Please upload file having extensions .csv or .xls only.');
                    fileInput.value = '';
                }

            };

            </script>"
			
        );
       

	//Import CSV Form Ends
		   }else{
    //Form for Add Key    

       
        $productkey_filedType = "textarea";
        $multiplekey_ins = 'To save multiple Keys place one Key per line';
		
        if ($model->getId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Key'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
            $fieldset->addField('created_at', 'hidden', ['name' => 'created_at']);
            $productkey_filedType = "text";
            $multiplekey_ins = '';
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Key(s)'), 'class' => 'fieldset-wide']
            );
        }

        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('SKU / Key Pool Name'),
                'title' => __('Sku'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField(
            'type',
            'text',
            [
                'name' => 'type',
                'label' => __('User Data'),
                'title' => __('User Data'),
                'class' => 'user-data',
                'note' => 'Use however you like (Ex: License Key, Product Key, Voucher Code etc)'
            ]
        );

        $fieldset->addField(
            'product_key',
            $productkey_filedType,
            [
                'name' => 'product_key',
                'label' => __('Product Key'),
                'title' => __('Product Key'),
                'class' => 'required-entry',
                'required' => true,
                'note' => $multiplekey_ins
            ]
        );   
		 
		

        if ($model->getId()) {
            $fieldset->addField(
                'status',
                'select',
                [
                    'name' => 'status',
                    'label' => __('Status'),
                    'title' => __('Status'),
                    'values' => $this->_options->getOptionArray(),
                    'class' => 'status',
                    'required' => true
                ]
            );

            $fieldset->addField(
                'orderinc_id',
                'text',
                [
                    'name' => 'orderinc_id',
                    'label' => 'Order',
                    'title' => __('Order'),
                    'class' => 'orderinc_id'
                ]
            );
        }

		}
		$form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
