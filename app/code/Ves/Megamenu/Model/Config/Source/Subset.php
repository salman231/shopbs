<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Model\Config\Source;

class Subset implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'cyrillic',	'label' => ('Cyrillic')],
			['value' => 'cyrillic-ext','label' => ('Cyrillic Extended')],
			['value' => 'greek','label' => ('Greek')],
			['value' => 'greek-ext',	'label' => ('Greek Extended')],
			['value' => 'khmer','label' => ('Khmer')],
			['value' => 'latin','label' => ('Latin')],
			['value' => 'latin-ext',	'label' => ('Latin Extended')],
			['value' => 'vietnamese',	'label' => ('Vietnamese')],
		];
	}
}