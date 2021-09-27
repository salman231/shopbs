<?php
/**
 * Dart Productkeys Record Save Controller.
 * @package    Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Controller\Adminhtml\Productkeys;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\ProductFactory;
use Dart\Productkeys\Model\ProductkeysFactory;
use Dart\Productkeys\Helper\Data;
use Dart\Productkeys\Controller\Adminhtml\Productkeys\Generatekeys;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\ScopeInterface; 
use Magento\Framework\App\Config\ScopeConfigInterface;

class SaveCsv extends Action
{
    /**
     * @var \Dart\Productkeys\Model\ProductkeysFactory
     */
    private $productkeysFactory;
	protected $fileSystem;
    protected $uploaderFactory;

    protected $request;

  	   public function __construct(
        Context $context,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
		ProductFactory $productFactory,
		DateTime $date,
        ScopeConfigInterface $scopeConfig,
		ProductkeysFactory $productkeysFactory,
        Generatekeys $generateKeys

    ) {
        parent::__construct($context);
        $this->fileSystem = $fileSystem;
        $this->request = $context->getRequest();
        $this->scopeConfig = $scopeConfig;
        $this->uploaderFactory = $uploaderFactory;
		$this->_date = $date;
        $this->productFactory = $productFactory;
        $this->productkeysFactory = $productkeysFactory;
        $this->generateKeys = $generateKeys;
    }
	

    public function execute()
    { 
		$files = $this->request->getFiles()->toArray();

        if ( (isset($files['browse-csv']['name'])) && ($files['browse-csv']['name'] != '') ) 
         {
            try 
           {    

			
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'browse-csv']);
                $uploaderFactory->setAllowedExtensions(['csv', 'xls']);
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);

                $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
				
                $destinationPath = $mediaDirectory->getAbsolutePath('Dart_Productkeys');
                $result = $uploaderFactory->save($destinationPath);

                if (!$result) 
                   {
                     throw new LocalizedException
                     (
                        __('File cannot be saved to path: $1', $destinationPath)
                     );

                   }
                else
                    {   
                       
					    $imagePath = 'Dart_Productkeys'.$result['file'];

                        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);

                        $destinationfilePath = $mediaDirectory->getAbsolutePath($imagePath);

                        /* file read operation */

                        $f_object = fopen($destinationfilePath, "r");

                        $column = fgetcsv($f_object);
						$thisTime = $this->_date->gmtDate();
						$traceVar="";
                        // column name must be same as the Sample file name 

                        if($f_object)
                        {
                            if( ($column[0] == 'type') && ($column[1] == 'sku') && ($column[2] == 'product_key') )
                            {   

                                $count = 0;

                                while (($columns = fgetcsv($f_object)) !== FALSE) 
                                {

                               
								   $rowData = $this->productkeysFactory->create();

                                    if($columns[0] != 'type')
                                    {   
                                        $count++;

                                    /// here this are all the Getter Setter Method which are call to set value 
                                    // the auto increment column name not used to set value 

                                        $rowData->setType($columns[0]);

                                        $rowData->setSku($columns[1]);
										$rowData->setProductKey($columns[2]);
										$rowData->setCreatedAt($thisTime);
										$rowData->setUpdatedAt($thisTime);									
										$rowData->save();   

                                    }

                                } 

                            $this->messageManager->addSuccess(__('A total of %1 record(s) have been Added.'.$traceVar, $count));                           
							$this->_redirect('productkeys/productkeys/index');
                            }
                            else
                            {
                                $this->messageManager->addError(__("invalid Formated File"));								
                                $this->_redirect('productkeys/productkeys/importcsv');
                            }

                        } 
                        else
                        {
                            $this->messageManager->addError(__("File hase been empty"));
					        $this->_redirect('productkeys/productkeys/importcsv');
                        }

                    }                   

           } 
           catch (\Exception $e) 
          {   
               $this->messageManager->addError(__($e->getMessage().$trace));
			  $this->_redirect('productkeys/productkeys/importcsv');
			 
          }

         }
         else
         {
            $this->messageManager->addError(__("Please try again..."));
		     $this->_redirect('productkeys/productkeys/importcsv');
         }
    }
	
}