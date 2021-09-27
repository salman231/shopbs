/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
var config = {
    shim: {
        'Webkul_MobikulCore/js/extjs/ext-tree-radioCategory': {
            deps:[
                'extjs/ext-tree',
                'extjs/defaults'
            ]
        },
        'Webkul_MobikulCore/js/extjs/ext-tree-checkboxCategory': {
            deps:[
                'extjs/ext-tree',
                'extjs/defaults'
            ]
        }
    },
    map: {
        "*": {
            extCheckbox: "Webkul_MobikulCore/js/extjs/ext-tree-checkboxCategory",
            radioCategory : "Webkul_MobikulCore/js/extjs/ext-tree-radioCategory",
            textureimage: 'Webkul_MobikulCore/js/textureimage',
            categoryBannerImage: "Webkul_MobikulCore/js/categorybannerimage",
            appCreator: "Webkul_MobikulCore/js/appcreator"
        }
    }
};