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

namespace Webkul\MobikulCore\Model;

class Express extends \Magento\Paypal\Model\Express
{

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $om->get(\Magento\Framework\App\RequestInterface::class);
        if ($request->getHeader("authKey")) {
            return true;
        }
        parent::authorize($payment, $amount);
    }
}
