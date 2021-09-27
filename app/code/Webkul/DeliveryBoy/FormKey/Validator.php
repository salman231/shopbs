<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\FormKey;

use Magento\Framework\Encryption\Helper\Security;

class Validator
{
    const FORM_KEY_FIELD = 'form_key';

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var \Magento\Fraemwork\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framwork\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->formKey = $formKey;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return boolean
     */
    public function validate(\Magento\Framework\App\RequestInterface $request)
    {
        $formKey = $request->getParam(self::FORM_KEY_FIELD, null);
        if (!$formKey) {
            if ($request instanceof \Magento\Framework\App\PlainTextRequestInterface &&
                method_exists($request, 'getContent')
            ) {
                $jsonArray = $this->jsonHelper->jsonDecode($request->getContent());
                $formKey = $this->extractFormKeyFromJsonArray($jsonArray);
            }
        }
        return $formKey && Security::compareStrings($formKey, $this->formKey->getFormKey());
    }

    /**
     * Extract Form Key from json data
     *
     * @param array $jsonArray
     * @return string|null
     */
    private function extractFormKeyFromJsonArray($jsonArray)
    {
        if (isset($jsonArray[self::FORM_KEY_FIELD])) {
            return $jsonArray[self::FORM_KEY_FIELD];
        }
        foreach ($jsonArray as $item) {
            if (isset($item['name'], $item['value']) && $item['name'] === self::FORM_KEY_FIELD) {
                return $item['value'];
            }
        }
        return null;
    }
}
