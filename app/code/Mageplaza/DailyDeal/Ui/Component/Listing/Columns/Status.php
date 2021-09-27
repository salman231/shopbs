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
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Status
 * @package Mageplaza\DailyDeal\Ui\Component\Listing\Columns
 */
class Status extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $name = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $currentDate = time();
                    $dateFrom    = strtotime($item['date_from']);
                    $dateTo      = strtotime($item['date_to']);
                    $dealQty     = (int) $item['deal_qty'];
                    $saleQty     = (int) $item['sale_qty'];

                    if ($dealQty > $saleQty && $item[$name] === '1') {
                        if ($dateTo >= $currentDate && $dateFrom <= $currentDate) {
                            $item[$name] = 'running';
                        } elseif ($currentDate < $dateFrom) {
                            $item[$name] = 'upcoming';
                        } elseif ($currentDate > $dateTo) {
                            $item[$name] = 'ended';
                        }
                    } elseif ($saleQty >= $dealQty && $item[$this->getData('name')] === '1') {
                        $item[$this->getData('name')] = 'ended';
                    } else {
                        $item[$this->getData('name')] = 'disable';
                    }
                }
            }
        }

        return $dataSource;
    }
}
