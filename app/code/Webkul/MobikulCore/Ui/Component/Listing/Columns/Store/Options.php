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

namespace Webkul\MobikulCore\Ui\Component\Listing\Columns\Store;

use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreOptions;

class Options extends StoreOptions
{
    const ALL_STORE_VIEWS = "0";

    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $this->currentOptions["All Store Views"]["label"] = __("All Store Views");
        $this->currentOptions["All Store Views"]["value"] = self::ALL_STORE_VIEWS;
        $this->generateCurrentOptions();
        $this->options = array_values($this->currentOptions);
        return $this->options;
    }
}
