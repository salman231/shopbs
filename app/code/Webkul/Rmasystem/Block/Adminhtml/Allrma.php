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
namespace Webkul\Rmasystem\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Allrma extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_allrma';
        $this->_blockGroup = 'Webkul_Rmasystem';
        $this->_headerText = __('Manage RMA');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
