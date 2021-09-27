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
namespace Webkul\Rmasystem\Api;

interface ApplyFilterInterface
{
    /**
     * Returns selected order detail
     *
     * @api
     * @param string $orderId
     * @param string $price
     * @param string $date
     * @return string.
     */
    public function applyFilter($orderId = 0, $price = null, $date = null);
}
