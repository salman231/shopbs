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

namespace Webkul\MobikulCore\Controller\Adminhtml\Categoryimages;

/**
 * Class Validate for Categoryimages
 */
class Validate extends \Webkul\MobikulCore\Controller\Adminhtml\Categoryimages
{
    /**
     * Function to validate category Images
     *
     * @return array
     */
    protected function _validateCategoryimages($response)
    {
        $categoryimages = null;
        $errors = [];
        try {
            $categoryimages = $this->categoryimagesDataFactory->create();
            $data = $this->getRequest()->getParams();
            $dataResult = $data["mobikul_categoryimages"];
            $errors = [];
            if (!isset($dataResult["icon"][0]["name"])) {
                $errors[] = __("Please upload category icon image.");
            }
            if (isset($dataResult["category_id"])) {
                if ($dataResult["category_id"]) {
                    try {
                        $this->categoryRepository->get($dataResult["category_id"]);
                    } catch (\Exception $exception) {
                        $errors[] = __("Requested category doesn't exist");
                    }
                }
            } else {
                $errors[] = __("Category id should be set.");
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
        return $categoryimages;
    }

    /**
     * Execute function for class Validate
     *
     * @return resultFactory
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $categoryimages = $this->_validateCategoryimages($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
