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
class BackgroundType
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value'=>'', 'label'=>'Select Background Type'],
            ['value'=>'color', 'label'=>'Solid Color'],
            ['value'=>'image', 'label'=>'Image'],
        ];
        
        return $options;
    }
}
