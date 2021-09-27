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

namespace Webkul\Mpreportsystem\Block\Adminhtml\Mpreport\Grid;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Renderrow extends AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];

    protected $_index = 0;

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->_actions = [];
        $rowData = $row->getData();
        if (array_key_exists('order_date', $rowData)) {
            $this->_index = $this->_index + 1;
            $actions[] = $this->_index;
        } else {
            $actions[] = __('Totals');
        }
        $this->addToActions($actions);
        return $this->_actionsToHtml();
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        if (empty($actions)) {
            $actions = $this->_actions;
        }
        foreach ($actions[0] as $action) {
            $html[] = '<span>' . $action . '</span>';
        }
        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
