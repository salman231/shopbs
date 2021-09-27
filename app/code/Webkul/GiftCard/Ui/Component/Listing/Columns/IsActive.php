<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\GiftCard\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class IsActive extends Column
{
   
    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['is_active'] == 'no') {
                    $item['is_active'] = '<div style="background:#f9d4d4;border:1px solid;
                    border-color:#e22626;padding: 0 7px;text-align:center;
                    text-transform: uppercase;color:#e22626;font-weight:bold;" 
                    title="Gift Card is disable">Disable</div>';
                } else {
                    $item['is_active'] = '<div style="background:#d0e5a9;border:1px solid;
                    border-color:#5b8116;padding: 0 7px;text-align:center;text-transform: uppercase;
                    color:#185b00;font-weight:bold;" title="Gift Card is enable">Enable</div>';
                }
            }
        }
        return $dataSource;
    }
}
