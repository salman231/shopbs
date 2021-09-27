<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Model\Plugin\Product;

use Closure;

class ValidationRules
{

    /**
     * @param \Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules $rulesObject
     * @param callable $proceed
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(
        \Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules $rulesObject,
        Closure $proceed,
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
        array $data
    ) {
        $rules = $proceed($attribute, $data);
        if ($attribute->getAttributeCode() == 'commission_for_product') { //custom filter
            $validationClasses = explode(' ', $attribute->getFrontendClass());
            foreach ($validationClasses as $class) {
                $rules[$class] = true;
            }
        }
        return $rules;
    }
}
