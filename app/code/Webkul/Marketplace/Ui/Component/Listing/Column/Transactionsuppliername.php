<?php 
namespace Webkul\Marketplace\Ui\Component\Listing\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\Marketplace\Model\ResourceModel\Sellertransaction\CollectionFactory;
class Transactionsuppliername extends Column
{
    /**
     * 
     * @param ContextInterface   $context           
     * @param UiComponentFactory $uiComponentFactory   
     * @param array              $components        
     * @param array              $data              
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $transactionCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        array $components = [],
        array $data = []
    ) {
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
         $this->customerModel = $customerModel;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        // echo "<pre>";
        
        if (isset($dataSource['data']['items'])) {
            $i=0;
            foreach ($dataSource['data']['items'] as & $item) {

                $transactioncoll = $this->_transactionCollectionFactory->create()
                ->addFieldToSelect(
                    '*'
                );
                // print_r($collection->getData()); exit();
                // ->addFieldToFilter(
                //     'seller_id',
                //     ['eq' => $customerId]
                // );
                
                $suppliername = array();
                foreach ($transactioncoll as $key => $transvalue) {
                    // print_r($transvalue->getData()); exit();
                    $customer = $this->customerModel->create()->load($transvalue->getSellerId());
                    $suppliername[] = $customer->getName();
                    // $suppliername = $customer->getName();
                    // $item[$this->getData('name')] = $suppliername[$i];
                    // echo "innerloop:".$i;
                    
                }
                
                // echo "outloop:".$i;
                // exit();
                // $customer = $this->customerModel->create()->load($sellerId);
                $item[$this->getData('name')] = $suppliername[$i]; //Value which you want to display
                $i++;
            }
            
        }
        return $dataSource;
    }
}
?>