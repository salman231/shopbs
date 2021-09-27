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
namespace Webkul\DeliveryBoy\Controller\Api\Deliveryboy;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Exception\LocalizedException;

class ForgotPassword extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if (!\Zend_Validate::is($this->email, "EmailAddress")) {
                throw new LocalizedException(__("Invalid Username."));
            }
            $deliveryboy = $this->deliveryboyResourceCollection->create()
                ->addFieldToFilter("email", $this->email)
                ->getFirstItem();
            if (!$deliveryboy->getId()) {
                throw new LocalizedException(__("No deliveryboy is associated with provided username."));
            }
            $passwordToken = $this->mathRandom->getUniqueHash();
            $deliveryboyName = $deliveryboy->getName();
            $deliveryboyEmail = $deliveryboy->getEmail();
            $deliveryboy->setRpToken($passwordToken);
            $deliveryboy->setRpTokenCreatedAt(
                $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
            );
            $deliveryboy->setId($deliveryboy->getId());
            $deliveryboy->save();
            $templateVariables = [];
            $templateVariables["token"] = $passwordToken;
            $templateVariables["deliveryboyName"] = $deliveryboyName;
            $template = $this->deliveryboyHelper->getForgotPasswordTemplate();
            $this->inlineTranslation->suspend();
            $senderInfo = [
                "name" => "Admin",
                "email" => $this->deliveryboyHelper->getGeneralEmail()
            ];
            $receiverInfo = [
                "name" => $deliveryboyName,
                "email" => 'piyush.goel190@webkul.com'// $deliveryboyEmail
            ];
            $template = $this->transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions(
                    [
                        "area" => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($templateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo["email"], $receiverInfo["name"]);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $this->returnArray["message"] = __("Password reset request received, please check mail.");
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "PUT" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->email = trim($this->wholeData["email"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid request."));
        }
    }
}
