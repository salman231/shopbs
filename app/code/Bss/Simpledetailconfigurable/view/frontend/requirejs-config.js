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
var config = {
    map: {
        '*': {
            bss_configurable_control: 'Bss_Simpledetailconfigurable/js/configurable_control',
            bssPriceOptions: 'Bss_Simpledetailconfigurable/js/bss-price-option',
            "Magento_Review/js/process-reviews": 'Bss_Simpledetailconfigurable/js/process-reviews',
            'priceBox' : 'Bss_Simpledetailconfigurable/js/price-box'
        }
    },
    config: {
        mixins: {
            "Magento_Swatches/js/swatch-renderer" : {
                "Bss_Simpledetailconfigurable/js/swatch-renderer": true
            },
            "Magento_Catalog/js/price-options" : {
                "Bss_Simpledetailconfigurable/js/price-options-mixin": true
            },
            "Magento_Catalog/js/price-option-date" : {
                "Bss_Simpledetailconfigurable/js/price-option-date-mixin": true
            }
        },

    },
};
