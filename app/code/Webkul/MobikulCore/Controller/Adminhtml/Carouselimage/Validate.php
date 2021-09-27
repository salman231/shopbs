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

namespace Webkul\MobikulCore\Controller\Adminhtml\Carouselimage;

/**
 * Class Validate for Carouselimage
 */
class Validate extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Function to validate Images
     *
     * @return array
     */
    protected function _validateImage($response)
    {
        $carouselimage = null;
        $errors = [];
        try {
            $carouselimage = $this->carouselimageDataFactory->create();
            $data = $this->getRequest()->getParams();
            $dataResult = $data["mobikul_carouselimage"];
            $errors = [];
            if (!isset($dataResult["filename"][0]["name"])) {
                $errors[] = __("Please upload carousel image.");
            }
            if ($dataResult["title"] == "") {
                $errors[] = __("Title can not be blank.");
            }
            if (isset($dataResult["type"]) && isset($dataResult["pro_cat_id"])) {
                if ($dataResult["type"] == "product") {
                    try {
                        $this->productRepositoryInterface->getById($dataResult["pro_cat_id"]);
                    } catch (\Exception $exception) {
                        $errors[] = __("Requested product doesn't exist");
                    }
                }
                if ($dataResult["type"] == "category") {
                    try {
                        $this->categoryRepositoryInterface->get($dataResult["pro_cat_id"]);
                    } catch (\Exception $exception) {
                        $errors[] = __("Requested category doesn't exist");
                    }
                }
            } else {
                $errors[] = __("Carousel image type or id should be set.");
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
        return $carouselimage;
    }

    /**
     * Execute Function for Class Validate
     *
     * @return jSon
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $this->_validateImage($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
