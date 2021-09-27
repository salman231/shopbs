<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Test\Unit\Helper;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewsColFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class DataTest
 *
 * @package Mageplaza\BetterProductReviews\Test\Unit\Helper
 */
class DataTest extends TestCase
{
    /**
     *
     *
     * @var CustomerSession|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSessionMock;

    /**
     *
     *
     * @var OrderFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderFactoryMock;

    /**
     *
     *
     * @var HttpContext|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_httpContext;

    /**
     *
     *
     * @var CustomerRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerRepoMock;

    /**
     *
     *
     * @var Configurable|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configureProdMock;

    /**
     *
     *
     * @var DateTime|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dateTimeMock;

    /**
     *
     *
     * @var TimezoneInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_localeDateMock;

    /**
     *
     *
     * @var RatingFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ratingFactoryMock;

    /**
     *
     *
     * @var ReviewFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_reviewFactoryMock;

    /**
     *
     *
     * @var ReviewsColFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_reviewsColFactMock;

    /**
     *
     *
     * @var HelperData
     */
    protected $model;

    protected function setUp()
    {
        $this->_customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->_orderFactoryMock = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->_httpContext = $this->getMockBuilder(HttpContext::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->_customerRepoMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->_configureProdMock = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->_dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->_localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->_ratingFactoryMock = $this->getMockBuilder(RatingFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->_reviewFactoryMock = $this->getMockBuilder(ReviewFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->_reviewsColFactMock = $this->getMockBuilder(ReviewsColFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $helper = new ObjectManager($this);

        $this->model = $helper->getObject(
            HelperData::class,
            [
                '_customerSession' => $this->_customerSessionMock,
                '_customerRepository' => $this->_customerRepoMock,
                '_orderFactory' => $this->_orderFactoryMock,
                '_httpContext' => $this->_httpContext,
                '_configureProduct' => $this->_configureProdMock,
                '_dateTime' => $this->_dateTimeMock,
                '_localeDate' => $this->_localeDateMock,
                '_ratingFactory' => $this->_ratingFactoryMock,
                '_reviewFactory' => $this->_reviewFactoryMock,
                '_reviewsColFactory' => $this->_reviewsColFactMock
            ]
        );
    }

    /**
     * test calculator review rating value
     */
    public function testGetReviewRatingValue()
    {
        $reviewMock = $this->getMockBuilder(Review::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getReviewSummary', 'getCount', 'getSum'])
            ->getMock();

        $this->_ratingFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($reviewMock);
        $reviewMock->expects($this->atLeastOnce())->method('getId')->willReturn(10);
        $reviewMock->expects($this->any())->method('getReviewSummary')->willReturn($reviewMock);
        $reviewMock->expects($this->atLeastOnce())->method('getCount')->willReturn(3);
        $reviewMock->expects($this->atLeastOnce())->method('getSum')->willReturn(260);

        $actualResult = $this->model->getReviewRatingValue($reviewMock);
        $expectResult = 4.4;
        $this->assertEquals($expectResult, $actualResult);
    }
}
