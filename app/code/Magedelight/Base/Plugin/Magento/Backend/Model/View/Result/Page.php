<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Base
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Base\Plugin\Magento\Backend\Model\View\Result;

class Page
{
    public function beforeSetActiveMenu($subject, $menu)
    {
        if (strpos($menu, 'Magedelight_') !== false) {
            $menu = 'Magedelight_Base::md_base_root';
        }

        return [$menu];
    }
}
