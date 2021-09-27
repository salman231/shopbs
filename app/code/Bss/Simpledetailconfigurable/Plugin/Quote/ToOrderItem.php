<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Plugin\Quote;

use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteToOrderItem;
use Magento\Framework\Serialize\Serializer\Json;

class ToOrderItem
{
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * ToOrderItem constructor.
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Add child option to order
     *
     * @param QuoteToOrderItem $subject
     * @param $result
     * @param $item
     * @param array $data
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(QuoteToOrderItem $subject, $result, $item, $data = [])
    {
        if (!$item->getParentItem()) {
            $additionalOptions = $item->getOptionByCode('additional_options');
            // Get Order Item's other options
            $options = $result->getProductOptions();
            // Set additional options to Order Item
            if ($additionalOptions && $additionalOptions->getValue()) {
                $options['additional_options'] = $this->serializer->unserialize($additionalOptions->getValue());
            }
            $result->setProductOptions($options);
        }

        return $result;
    }
}