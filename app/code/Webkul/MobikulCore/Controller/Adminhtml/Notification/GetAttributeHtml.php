<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Notification;

/**
 * GetAttributeHtml Class controller
 */
class GetAttributeHtml extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $attributeCode = $this->getRequest()->getParam("attributeCode");
        $attribute   = $this->entityAttribute->loadByCode("catalog_product", $attributeCode);
        $returnArray = [];
        $response = new \Magento\Framework\DataObject();
        if ($attributeCode == "type_id") {
            $response->setType("type_id");
            $response->setOptions($this->productType->getOptionArray());
        } elseif ($attributeCode == "category_ids") {
            $response->setType("category_ids");
        } elseif ($attributeCode == "attribute_set_id") {
            $response->setType("attribute_set_id");
            $entityTypeId = $this->entityType->getTypeId();
            $attributeSetCollection = $this->attributeSet->setEntityTypeFilter($entityTypeId);
            $response->setOptions($attributeSetCollection->getData());
        } elseif (in_array($attribute->getFrontendInput(), ["textarea", "text", "price"])) {
            $response->setType("text");
        } elseif (in_array($attribute->getFrontendInput(), ["select", "multiselect"])) {
            $response->setType("multiselect");
            $allOptions = $attribute->getSource()->getAllOptions(true, true);
            $tempArr = [];
            foreach ($allOptions as $value) {
                if ($value["value"] != "") {
                    $tempArr[] = $value;
                }
            }
            $response->setOptions($tempArr);
        } elseif ($attribute->getFrontendInput() == "boolean") {
            $response->setType("multiselect");
            $tempArr   = [];
            $tempArr[] = ["value"=>1, "label"=>__("Yes")];
            $tempArr[] = ["value"=>0, "label"=>__("No")];
            $response->setOptions($tempArr);
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
