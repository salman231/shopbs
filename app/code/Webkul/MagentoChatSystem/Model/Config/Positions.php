<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Model\Config;

/**
 * Generic source
 */
class Positions
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value'=>'', 'label'=>'Select Positions'],
            ['value'=>'left', 'label'=>'Left'],
            ['value'=>'right', 'label'=>'Right'],
        ];
        
        return $options;
    }
}
