<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller;

if (!defined('DS')) {
    define("DS", DIRECTORY_SEPARATOR);
}
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\InvalidRequestException;

abstract class ApiController extends \Magento\Framework\App\Action\Action implements
    \Magento\Framework\App\CsrfAwareActionInterface
{
    protected $os;
    protected $url;
    protected $dob;
    protected $qty;
    protected $eTag;
    protected $hash;
    protected $width;
    protected $token;
    protected $email;
    protected $title;
    protected $height;
    protected $mobile;
    protected $prefix;
    protected $suffix;
    protected $taxvat;
    protected $itemId;
    protected $gender;
    protected $detail;
    protected $helper;
    protected $quoteId;
    protected $mFactor;
    protected $storeId;
    protected $ratings;
    protected $headers;
    protected $product;
    protected $message;
    protected $customer;
    protected $username;
    protected $password;
    protected $isSocial;
    protected $lastName;
    protected $reviewId;
    protected $nickname;
    protected $itemData;
    protected $iconWidth;
    protected $isFromUrl;
    protected $websiteId;
    protected $wholeData;
    protected $addressId;
    protected $firstName;
    protected $itemBlock;
    protected $productId;
    protected $carouselId;
    protected $jsonHelper;
    protected $collection;
    protected $customerId;
    protected $iconHeight;
    protected $pageNumber;
    protected $priceBlock;
    protected $pictureURL;
    protected $middleName;
    protected $addressData;
    protected $newPassword;
    protected $incrementId;
    protected $loadedOrder;
    protected $bannerWidth;
    protected $customerName;
    protected $profileWidth;
    protected $recipientName;
    protected $customerEmail;
    protected $customerToken;
    protected $profileHeight;
    protected $doChangeEmail;
    protected $recipientEmail;
    protected $collectionType;
    protected $confirmPassword;
    protected $currentPassword;
    protected $productCarousel;
    protected $returnArray = [];
    protected $doChangePassword;
    protected $otherError = false;

    /**
     * Constructer method.
     *
     * @param \Webkul\MobikulCore\Helper\Data       $helper     helper
     * @param \Magento\Framework\App\Action\Context $context    context
     * @param \Magento\Framework\Json\Helper\Data   $jsonHelper jsonHelper
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
        $this->returnArray["success"] = false;
        $this->returnArray["message"] = "";
    }

    /**
     * Get config value by key.
     *
     * @param RequestInterface $request request
     *
     * @return $request
     */
    public function dispatch(RequestInterface $request)
    {
        $this->headers = $this->getRequest()->getHeaders();
        $this->wholeData = $this->getRequest()->getParams();
        $this->helper->log(__CLASS__, "logClass", $this->wholeData);
        $this->helper->log($this->headers, "logHeaders", $this->wholeData);
        $this->helper->log($this->wholeData, "logParams", $this->wholeData);
        $returnArray = [];
        $returnArray["success"] = false;
        $returnArray["message"] = "";
        $authKey = $request->getHeader("authKey");
        $authData = $this->helper->isAuthorized($authKey);
        if ($authData["code"] != 1) {
            return $this->getJsonResponse($returnArray, 401, $authData["token"]);
        }
        return parent::dispatch($request);
    }

    /**
     * Return json response.
     *
     * @param array  $responseContent response content
     * @param string $responseCode    response code
     * @param string $token           token
     *
     * @return ResultFactory $resultJson
     */
    protected function getJsonResponse($responseContent = [], $responseCode = "", $token = "")
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($responseCode == 304) {
            $resultJson->setHttpResponseCode(304);
        } elseif ($responseCode == 401) {
            $resultJson->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED);
        } else {
            // $resultJson->setData($responseContent);
        }
        $resultJson->setData($responseContent);
        if ($token != "") {
            $resultJson->setHeader("token", $token, true);
        }
        $this->helper->log($responseContent, "logResponse", $this->wholeData);
        return $resultJson;
    }


    /**
     * Check wheather order can be reordered or not.
     *
     * @param \Magento\Sales\Model\Order $order order
     *
     * @return integer
     */
    public function canReorder(\Magento\Sales\Model\Order $order)
    {
        if (!$this->helper->getConfigData("sales/reorder/allow")) {
            return 0;
        } else {
            return $order->canReorder();
        }
    }

    /**
     * Check wheather order can be reordered or not.
     *
     * @param string $cacheString cache string
     *
     * @return null
     */
    public function checkNGenerateEtag($cacheString)
    {
        $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        if (hash("sha256", $encodedData) == $this->eTag) {
            return $this->getJsonResponse($this->returnArray, 304);
        }
        $this->helper->updateCache($cacheString, $encodedData);
        $this->returnArray["eTag"] = hash("sha256", $encodedData);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
            return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
