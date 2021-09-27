<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Test\Plugin;


class QuoteAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function shippingDataProvider()
    {
        $backend = $this->getMock(
            'Amasty\Methods\Model\Structure\Shipping',
            ['getSize'],
            [],
            '',
            false
        );

        $backend->expects($this->any())->method('getSize')->willReturn(1);

        return [
            [
                'backend' => $backend
            ]
        ];
    }

    /**
     * @param mixed $backend
     * @dataProvider shippingDataProvider
     */
    public function testBackendShipping($backend)
    {
        $websiteId = 1;
        $adminWebsiteId = 0;

        $quoteAddress = $this->getMock(
            'Amasty\Methods\Plugin\QuoteAddress',
            ['isBackend', 'getMethodsStructure'],
            [],
            '',
            false
        );

        $quoteAddress->expects($this->any())->method('getMethodsStructure')->willReturn($backend);
        $quoteAddress->expects($this->any())->method('isBackend')->willReturn(true);

        $this->assertEquals($adminWebsiteId, $quoteAddress->getWebsiteId($websiteId));
    }


    /**
     * @param mixed $backend
     * @dataProvider shippingDataProvider
     */
    public function testFrontendShipping($backend)
    {
        $websiteId = 1;
        $adminWebsiteId = 0;

        $quoteAddress = $this->getMock(
            'Amasty\Methods\Plugin\QuoteAddress',
            ['isBackend', 'getMethodsStructure'],
            [],
            '',
            false
        );

        $quoteAddress->expects($this->any())->method('getMethodsStructure')->willReturn($backend);
        $quoteAddress->expects($this->any())->method('isBackend')->willReturn(false);

        $this->assertEquals($websiteId, $quoteAddress->getWebsiteId($websiteId));
    }

    public function testEmptyBackendShipping()
    {
        $websiteId = 1;

        $quoteAddress = $this->getMock(
            'Amasty\Methods\Plugin\QuoteAddress',
            ['isBackend', 'getMethodsStructure'],
            [],
            '',
            false
        );

        $backend = $this->getMock(
            'Amasty\Methods\Model\Structure\Shipping',
            ['getSize'],
            [],
            '',
            false
        );

        $backend->expects($this->any())->method('getSize')->willReturn(0);

        $quoteAddress->expects($this->any())->method('getMethodsStructure')->willReturn($backend);
        $quoteAddress->expects($this->any())->method('isBackend')->willReturn(true);

        $this->assertEquals($websiteId, $quoteAddress->getWebsiteId($websiteId));
    }
}