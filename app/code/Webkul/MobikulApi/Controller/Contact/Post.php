<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Contact;

/**
 * Class Post api
 */
class Post extends \Webkul\MobikulApi\Controller\ApiController
{
    protected $emulate;
    protected $jsonHelper;
    protected $stateInterface;

    /**
     * Construct Function
     *
     * @param \Webkul\MobikulCore\Helper\Data                    $helper         helper
     * @param \Magento\Store\Model\App\Emulation                 $emulate        emulate
     * @param \Magento\Framework\App\Action\Context              $context        context
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper     jsonHelper
     * @param \Webkul\MobikulCore\Helper\Catalog                 $helperCatalog  helperCatalog
     * @param \Magento\Contact\Model\MailInterface               $contactMail    contactMail
     * @param \Magento\Framework\Translate\Inline\StateInterface $stateInterface stateInterface
     *
     * @return void
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Contact\Model\MailInterface $contactMail,
        \Magento\Framework\Translate\Inline\StateInterface $stateInterface
    ) {
        $this->emulate = $emulate;
        $this->mail = $contactMail;
        $this->jsonHelper = $jsonHelper;
        $this->stateInterface = $stateInterface;
        parent::__construct($helper, $context, $jsonHelper);
    }

    /**
     * Execute function for class Post
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->stateInterface->suspend();
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($this->wholeData);
            $error = false;
            if (!\Zend_Validate::is(trim($this->wholeData["name"]), "NotEmpty")) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($this->wholeData["comment"]), "NotEmpty")) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($this->wholeData["email"]), "EmailAddress")) {
                $error = true;
            }
            if ($error) {
                $this->stateInterface->resume();
                $this->returnArray["message"] = __(
                    "We can't process your request right now. Sorry, that's all we know."
                );
                return $this->getJsonResponse($this->returnArray);
            }
            $this->mail->send(
                $this->wholeData["email"],
                ["data" => $postObject]
            );
            $this->returnArray["message"] = __(
                "Thanks for contacting us with your comments and questions. We'll respond to you very soon."
            );
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->stateInterface->resume();
            $this->returnArray["message"] = __("We can't process your request right now. Sorry, that's all we know.");
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }
    
    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->name = $this->wholeData["name"] ?? "";
            $this->email = $this->wholeData["email"] ?? "";
            $this->comment = $this->wholeData["comment"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->telephone = $this->wholeData["telephone"] ?? "";
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
