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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;

abstract class ApiController extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var \Webkul\DeliveryBoy\Helper\Authentication
     */
    protected $authHelper;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Webkul\DeliveryBoy\Helper\Authentication $authHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\DeliveryBoy\Helper\Authentication $authHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->authHelper = $authHelper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
        $this->returnArray["success"] = false;
        $this->returnArray["message"] = "";
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->headers = $this->getRequest()->getHeaders();
        $this->wholeData = $this->getRequest()->getParams();
        $returnArray = [];
        $returnArray["success"] = false;
        $returnArray["message"] = __("Unauthorized access.");
        $authKey = $request->getHeader("authKey");
        $authData = $this->authHelper->isAuthorized($authKey);
        if ($authData["code"] !== 1) {
            return $this->getJsonResponse(
                $returnArray,
                401,
                $authData["token"]
            );
        }
        return parent::dispatch($request);
    }

    /**
     * @param array $responseContent
     * @param int $responseCode
     * @param string $token
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function getJsonResponse(
        array $responseContent = [],
        int $responseCode = \Magento\Framework\Webapi\Response::HTTP_OK,
        string $token = null
    ): \Magento\Framework\Controller\ResultInterface {
        $resultJson = $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setHttpResponseCode($responseCode)
            ->setData($responseContent);
        if ($token) {
            $resultJson->setHeader("token", $token, true);
        }
        return $resultJson;
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
