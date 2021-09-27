<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\MobikulApi\Controller\Framework\Result;

class Json extends \Magento\Framework\Controller\Result\Json
{
    /**
     * Function to getRawData
     * overwritten function of Magento\Framework\Controller\Result\Json
     *
     * @return string jSon
     */
    public function getRawData()
    {
        return $this->json;
    }
}
