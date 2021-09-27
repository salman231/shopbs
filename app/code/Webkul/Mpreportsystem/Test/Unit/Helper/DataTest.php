<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Test\Unit\Helper;

use Webkul\Mpreportsystem\Helper\Data;

class DataTest extends \PHPUnit_Framework_TestCase
{
    const CUSTOMER_ID = 3;
    protected $_contextMock;
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSessionMock;
    /**
     * @var Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactoryMock;
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagerMock;
    /**
     * @var Magento\Directory\Model\Currency
     */
    protected $_directoryModelMock;
    /**
     * @var Magento\Framework\Locale\CurrencyInterface
     */
    protected $_currencyInterfaceMock;
    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_mpproductcollection;
    /**
     * @var Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $_orderstatucCollectionMock;
    /**
     * @var Magento\Catalog\Model\CategoryFactory
     */
    protected $_catalogCategoryModelMock;
    /**
     * @var Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_catalogProductCollectionMock;
    /**
     * @var Magento\Sales\Model\OrderFactory
     */
    protected $_orderModelMock;
    /**
     * @var Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $_marketplacesaleslistMock;
    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    protected $_mpsaleslistCollectionMock;
    /**
     * @var Magento\Framework\Locale\ListsInterface
     */
    protected $_listinterfaceMock;
    /**
     * @var Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactoryMock;
    /**
     * @var Magento\Framework\App\DeploymentConfig
     */
    protected $_deploymentConfigMock;
    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterfaceMock;
    /**
     * @var \Magento\Framework\App\Config
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config', ['getValue'], [], '', false, false);
        $this->_contextMock = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->setMethods(
                ['getScopeConfig']
            )
            ->disableOriginalConstructor()
            ->disableArgumentCloning()
            ->getMock();
        $this->_contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->_scopeConfig);
        $this->_customerSessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->_customerSessionMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));

        $this->_productFactoryMock = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_directoryModelMock = $this->getMockBuilder('Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_currencyInterfaceMock = $this->getMockBuilder('Magento\Framework\Locale\CurrencyInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mpproductcollection = $this->getMockBuilder(
            'Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory'
        )
            ->setMethods(['create','addFieldToFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_orderstatucCollectionMock = $this->getMockBuilder(
            'Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory'
        )
            ->setMethods(['create','addFieldToFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_catalogCategoryModelMock = $this->getMockBuilder('Magento\Catalog\Model\CategoryFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_catalogProductCollectionMock = $this->getMockBuilder(
            'Magento\Catalog\Model\ResourceModel\Product\CollectionFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_orderModelMock = $this->getMockBuilder('Magento\Sales\Model\OrderFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_marketplacesaleslistMock = $this->getMockBuilder('Webkul\Marketplace\Model\SaleslistFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_mpsaleslistCollectionMock = $this->getMockBuilder(
            'Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_listinterfaceMock = $this->getMockBuilder('Magento\Framework\Locale\ListsInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_regionFactoryMock = $this->getMockBuilder('Magento\Directory\Model\RegionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_deploymentConfigMock = $this->getMockBuilder('Magento\Framework\App\DeploymentConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_timezoneInterfaceMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->data = new Data(
            $this->_contextMock,
            $this->_customerSessionMock,
            $this->_productFactoryMock,
            $this->_storeManagerMock,
            $this->_directoryModelMock,
            $this->_currencyInterfaceMock,
            $this->_mpproductcollection,
            $this->_orderstatucCollectionMock,
            $this->_catalogCategoryModelMock,
            $this->_catalogProductCollectionMock,
            $this->_orderModelMock,
            $this->_marketplacesaleslistMock,
            $this->_mpsaleslistCollectionMock,
            $this->_listinterfaceMock,
            $this->_regionFactoryMock,
            $this->_deploymentConfigMock,
            $this->_timezoneInterfaceMock
        );
    }
    public function getSalesListMock($itemsArray)
    {
        $mpsaleslistCollectionMock = $this->getMockBuilder(
            '\Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection'
        )
            ->setMethods(['getItems', 'getIterator'])
            ->disableOriginalConstructor()
            ->getMock();

        $mpsaleslistCollectionMock
            ->expects($this->any())
            ->method('getItems')
            ->willReturn($itemsArray);

        $mpsaleslistCollectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($itemsArray));
    }
    public function getProductCollectionMock($itemsArray)
    {
        $mpCollectionMock = $this->getMockBuilder(
            '\Webkul\Marketplace\Model\ResourceModel\Product\Collection'
        )
            ->setMethods(['getItems', 'getIterator','getMageproductId'])
            ->disableOriginalConstructor()
            ->getMock();

        $mpCollectionMock
            ->expects($this->any())
            ->method('getItems')
            ->willReturn($itemsArray);

        $mpCollectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($itemsArray));
    }
    public function testGetSellerProductIds()
    {
        $sellerId = 0;
        $mpproductArray = [];
        $productIds = [];
        $productCollection = $this->getProductCollectionMock($mpproductArray);
        if (!empty($productCollection)) {
            foreach ($productCollection as $value) {
                $productIds[] = $value->getMageproductId();
            }
        }
        $result = $this->data->getSellerProductIds();
        $this->assertEquals($productIds, $result);
    }
    public function testGetSellerProductIdsWithCustomCustomerId()
    {
        $sellerId = 0;
        $mpproductArray = [];
        $productIds = [];
        $productCollection = $this->getProductCollectionMock($mpproductArray);
        if (!empty($productCollection)) {
            foreach ($productCollection as $value) {
                $productIds[] = $value->getMageproductId();
            }
        }
        $result = $this->data->getSellerProductIds();
        $this->assertEquals($productIds, $result);
    }
    /**
     * @dataProvider filterDataProvider
     */
    public function testGetProductCollectionByFilter($param)
    {
        $salesListModel = $this->getMock(
            'Webkul\Marketplace\Model\Saleslist',
            [
                'getMageproductId',
                'getQty'
            ],
            [],
            '',
            false
        );
        $fields = [];

        $sellerId = '';
        if (array_key_exists('seller_id', $param)) {
            $sellerId = $param['seller_id'];
        }
        $sellerIdFlag = 0;
        if ($sellerId!='') {
            $sellerIdFlag = 1;
        }
        if (!array_key_exists('filter', $param)) {
            $param['filter'] = 'year';
        }
        $collectionMock = $this->getMock(
            '\Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection',
            [],
            [],
            '',
            false
        );
        $this->_mpsaleslistCollectionMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($collectionMock);

        $result = $this->data->getProductCollectionByFilter($param);
        $this->assertEquals($collectionMock, $result);
    }

    public function filterDataProvider()
    {
        $data['scenario_1'] = [
            'data'=>[
                "seller_id" =>  1,
                "filter"    =>  ''
            ]
        ];
        $data['scenario_1'] = [
            'data'=>[
                "seller_id" =>  60,
                "filter"    =>  'day'
            ]
        ];
        $data['scenario_1'] = [
            'data'=>[
                "seller_id" =>  1,
                "filter"    =>  'month'
            ]
        ];
        $data['scenario_1'] = [
            'data'=>[
                "seller_id" =>  1,
                "filter"    =>  'year'
            ]
        ];
        $data['scenario_1'] = [
            'data'=>[
                "seller_id" =>  1,
                "filter"    =>  ''
            ]
        ];
        $data['scenario_1'] = [
            'data'=>[
                "seller_id" =>  100,
                "filter"    =>  'week'
            ]
        ];
        $data['scenario_1'] = [
            'data'=>[
                "seller_id" =>  '',
                "filter"    =>  'year'
            ]
        ];
        $data['scenario_1'] = [
            'data'=>[
                "seller_id" =>  ''
            ]
        ];
        $data['scenario_1'] = [
            'data'=>[
                "filter"    =>  'year'
            ]
        ];
        return $data;
    }
}
