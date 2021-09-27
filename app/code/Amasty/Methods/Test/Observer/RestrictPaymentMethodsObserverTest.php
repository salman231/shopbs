<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Test\Observer;


class RestrictPaymentMethodsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function paymentDataProvider()
    {
        $backend = $this->getMock(
            'Amasty\Methods\Model\Structure\Payment',
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
     * @dataProvider paymentDataProvider
     */
    public function testBackendPayment($backend)
    {
        $websiteId = 1;
        $adminWebsiteId = 0;

        $observer = $this->getMock(
            'Amasty\Methods\Observer\RestrictPaymentMethodsObserver',
            ['isBackend', 'getMethodsStructure'],
            [],
            '',
            false
        );

        $observer->expects($this->any())->method('getMethodsStructure')->willReturn($backend);
        $observer->expects($this->any())->method('isBackend')->willReturn(true);

        $this->assertEquals($adminWebsiteId, $observer->getWebsiteId($websiteId));
    }


    /**
     * @param mixed $backend
     * @dataProvider paymentDataProvider
     */
    public function testFrontendPayment($backend)
    {
        $websiteId = 1;
        $adminWebsiteId = 0;

        $observer = $this->getMock(
            'Amasty\Methods\Observer\RestrictPaymentMethodsObserver',
            ['isBackend', 'getMethodsStructure'],
            [],
            '',
            false
        );

        $observer->expects($this->any())->method('getMethodsStructure')->willReturn($backend);
        $observer->expects($this->any())->method('isBackend')->willReturn(false);

        $this->assertEquals($websiteId, $observer->getWebsiteId($websiteId));
    }

    public function testEmptyBackendPayment()
    {
        $websiteId = 1;

        $observer = $this->getMock(
            'Amasty\Methods\Observer\RestrictPaymentMethodsObserver',
            ['isBackend', 'getMethodsStructure'],
            [],
            '',
            false
        );

        $backend = $this->getMock(
            'Amasty\Methods\Model\Structure\Payment',
            ['getSize'],
            [],
            '',
            false
        );

        $backend->expects($this->any())->method('getSize')->willReturn(0);

        $observer->expects($this->any())->method('getMethodsStructure')->willReturn($backend);
        $observer->expects($this->any())->method('isBackend')->willReturn(true);

        $this->assertEquals($websiteId, $observer->getWebsiteId($websiteId));
    }
}