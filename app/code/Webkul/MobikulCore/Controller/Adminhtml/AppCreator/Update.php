<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\AppCreator;

/**
 * Class Update for AppCreator
 */
class Update extends \Magento\Backend\App\Action
{
    protected $_pageFactory;
    protected $_collectionFactory;
    protected $_appcreatorFactory;
    protected $resultJsonFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Appcreator\CollectionFactory $collectionFactory,
        \Webkul\MobikulCore\Model\AppcreatorFactory $appcreatorFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_appcreatorFactory = $appcreatorFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    /**
     * Function to get the image url.
     *
     * @return string
     */
    public function getImageName($id = "dynamic", $type = "category")
    {
        switch (true) {
            case ($id == 'featuredcategories' && $type == 'category'):
                return 'category.jpg';
            case ($id == 'hotDeals' && $type == 'product'):
                return 'slide.jpg';
            case ($id == 'newDeals' && $type == 'product'):
                return 'grid.jpg';
            case ($id == 'newProduct' && $type == 'product'):
                return 'grid.jpg';
            case ($id == 'bannerimage' && $type == 'banner'):
                return 'banner.jpg';
            case ($type == 'product'):
                return 'grid.jpg';
            case ($type == 'image'):
                return 'slide.jpg';
            default:
                return 'grid.jpg';
        }
    }

    /**
     * Execute Fucntion
     *
     * @return jSon
     */
    public function execute()
    {
        try {
            $resultJson = $this->resultJsonFactory->create();
            $_data = [];
            $mobileViewData = [];
            $layoutSortingData = [];
            if (!empty($this->getRequest()->getParams()['data'])) {
                $layoutSortingData = explode(',', $this->getRequest()->getParams()['data']);
            }
            foreach ($layoutSortingData as $key => $data) {
                $data1 = explode('_', $data);
                $data = trim($data1[0], " ");
                $mobileViewData[] = [
                    'imagePath' => $this->getImageName($data, trim($data1[1], " ")),
                    'label' => rawurldecode($data1[2]) // @codingStandardsIgnoreLine
                ];
                if (array_key_exists($data, $_data)) {
                    $_data[$data]['position'] .= ($key+1).',';
                    continue;
                }
                $posi = ($key+1).',';
                $_data[$data] = [
                    'layout_id'=>$data,
                    'label'=> rawurldecode($data1[2]), // @codingStandardsIgnoreLine
                    'position'=>$posi,
                    'type' => $data1[1]
                ];
            }
            $post = $this->_appcreatorFactory->create();
            $connection = $post->getResource()->getConnection();
            $tableName = $post->getResource()->getMainTable();
            $connection->truncateTable($tableName);
            foreach ($_data as $dataTosave) {
                $model = $this->_appcreatorFactory->create();
                $model->setData($dataTosave);
                $model->save();
            }
            $response = ['success' => true, 'data' => $mobileViewData];
            return $resultJson->setData($response);
        } catch (\Exception $e) {
            $response = ['success' => false, 'data' => null];
        }
    }

    /**
     * Fucntion to check if this controller can be accessed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::appcreator");
    }
}
