<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories;

/**
 * Class Validate controller
 */
class Validate extends \Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories
{
    protected function _validateFeaturedcategories($response)
    {
        $featuredcategories = null;
        $errors = [];
        try {
            $data = $this->getRequest()->getParams();
            $errors = [];
            $dataResult = $data["mobikul_featuredcategories"];
            $featuredcategories = $this->featuredcategoriesDataFactory->create();
            if (!isset($dataResult["filename"][0]["name"])) {
                $errors[] = __("Please upload featuredcategories image.");
            }
            if (isset($dataResult["sort_order"])) {
                if (!is_numeric($dataResult["sort_order"])) {
                    $errors[] = __("Sort order should be a number.");
                }
            } else {
                $errors[] = __("Sort order field can not be blank.");
            }
            if (isset($dataResult["category_id"]) && $dataResult["category_id"]) {
                if ($dataResult["category_id"]) {
                    try {
                        $this->categoryRepository->get($dataResult["category_id"]);
                    } catch (\Exception $exception) {
                        $errors[] = __("Requested category doesn't exist");
                    }
                }
            } else {
                $errors[] = __("Category not selected.");
            }
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $exceptionMsg = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            foreach ($exceptionMsg as $error) {
                $errors[] = $error->getText();
            }
        }
        if ($errors) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(1);
        }
        return $featuredcategories;
    }

    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $featuredcategories = $this->_validateFeaturedcategories($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
