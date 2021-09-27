<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\OptionSource;

/**
 * Class WebpOptimization
 *
 * @package Amasty\PageSpeedOptimizer
 */
class WebpOptimization
{
    const WEBP = [
        'name' =>  'cwebp',
        'command' => 'cwebp %f -o %o',
        'check' => [
            'command' => 'cwebp -help',
            'result' => 'cwebp [options]'
        ]
    ];
}
