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

namespace Webkul\MobikulCore\Controller\Adminhtml\Carousel;

/**
 * Class Validate for Carousel
 */
class Validate extends \Webkul\MobikulCore\Controller\Adminhtml\Carousel
{
    /**
     * Function to validate carousel
     *
     * $param object
     *
     * @return jSon
     */
    protected function _validateCaroousel($response)
    {
        $carousel = null;
        $errors = [];
        try {
            $carousel = $this->carouselDataFactory->create();
            $data = $this->getRequest()->getParams();
            $dataResult = $data["mobikul_carousel"];
            $errors = [];
            if ($dataResult["title"] == "") {
                $errors[] = __("Title can not be blank.");
            }
            if (isset($dataResult["sort_order"])) {
                if (!is_numeric($dataResult["sort_order"])) {
                    $errors[] = __("Sort order should be a number.");
                }
            } else {
                $errors[] = __("Sort order field can not be blank.");
            }
            if ($dataResult["type"] == 1) {
                if (!isset($dataResult["image_ids"]) || $dataResult["image_ids"] == "") {
                    $errors[] = __("Please select atleast one image.");
                }
            }
            if ($dataResult["type"] == 2) {
                if (!isset($dataResult["product_ids"]) || $dataResult["product_ids"] == "") {
                    $errors[] = __("Please select atleast one product.");
                }
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
        return $carousel;
    }

    /**
     * Execute Function
     *
     * @return bool
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $this->_validateCaroousel($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
