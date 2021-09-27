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

namespace Webkul\MobikulApi\Controller\Sales;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\Layout as ResultLayout;

/**
 * Class ShareWishlist api
 */
class ShareWishlist extends \Webkul\MobikulApi\Controller\ApiController
{
    /**
     * Emulate
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * JsonHelper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * StoreManagerInterface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * WishlistConfig
     *
     * @var \Magento\Wishlist\Model\Config
     */
    protected $wishlistConfig;

    /**
     * CustomerFactory
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * WishlistProvider
     *
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * TransportBuilder
     *
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * InlineTranslation
     *
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * Customer Helper view
     *
     * @var \Magento\Customer\Helper\View
     */
    protected $customerHelperView;

    /**
     * Function Construct for Guest view class
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\View $customerHelperView
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\View $customerHelperView,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->emulate = $emulate;
        $this->wishlist = $wishlist;
        $this->jsonHelper = $jsonHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->wishlistConfig = $wishlistConfig;
        $this->wishlistFactory = $wishlistFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->customerHelperView = $customerHelperView;
        $this->escaper = $escaper;
        parent::__construct($helper, $context, $jsonHelper);
    }

    /**
     * Execute function for class Guest view
     *
     * @return Set value to return array
     */
    public function execute()
    {
        $this->verifyRequest();
        $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
        $errors = false;
        $emails = [];
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($this->customerId);
        if (!$wishlist) {
            $this->returnArray["message"] = __("Page Not Found.");
            return $this->getJsonResponse($this->returnArray);
        }
        // validate the message and emails wishlist with configuration settings//////
        $sharingLimit = $this->wishlistConfig->getSharingEmailLimit();
        $textLimit = $this->wishlistConfig->getSharingTextLimit();
        $wishlist = $this->wishlistProvider->getWishlist();
        $emailsLeft = $sharingLimit - $wishlist->getShared();
        $emails = empty($this->emails) ? $this->emails : explode(",", $this->emails);
        $message = $this->message;
        if (strlen($this->message) > $textLimit) {
            $this->returnArray["message"] = __("Message length must not exceed %1 symbols", $textLimit);
            return $this->getJsonResponse($this->returnArray);
        } else {
            $message = nl2br($this->escaper->escapeHtml($message));
            if (empty($emails)) {
                $this->returnArray["message"] = __("Please enter an email address.");
                return $this->getJsonResponse($this->returnArray);
            } else {
                if (count($emails) > $emailsLeft) {
                    $this->returnArray["message"] = __(
                        "Sharing Limit over.This wish list can be shared %1 more times.",
                        $emailsLeft
                    );
                    return $this->getJsonResponse($this->returnArray);
                } else {
                    foreach ($emails as $index => $email) {
                        $email = trim($email);
                        if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                            $this->returnArray["message"] = __("Please enter a valid email.");
                            return $this->getJsonResponse($this->returnArray);
                        }
                        $emails[$index] = $email;
                    }
                }
            }
        }
        // sending email ////////////////////////////////////////////////////////////
        $this->sendEmails($emails, $wishlist);
        $this->returnArray["success"] = true;
        $this->returnArray["message"] = __("Wishlist shared successfully.");
        $this->emulate->stopEnvironmentEmulation($environment);
        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        $this->returnArray["success"] = false;
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->emails = $this->wholeData["emails"] ?? "";
            $this->message = $this->wholeData["message"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->websiteId = $this->wholeData["websiteId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            } elseif ($this->customerId != 0) {
                $this->customer = $this->customerFactory->create()->load($this->customerId);
                $this->customerSession->setCustomerId($this->customerId);
            }
            if ($this->emails == "" || $this->message == "") {
                $this->returnArray["message"] = __("Invalid Data.");
                $this->returnArray["otherError"] = __("Missing Required Information");
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to send email
     *
     * @param array  $emails   array of emails
     * @param object $wishlist wishlist
     *
     * @return bool
     */
    public function sendEmails($emails, $wishlist)
    {
        $sent = 0;
        $customer = $this->customerSession->getCustomerDataObject();
        $customerName = $this->customerHelperView->getCustomerName($customer);
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $this->addLayoutHandles($resultLayout);
        $this->inlineTranslation->suspend();
        // $message .= $this->getRssLink($wishlist->getId(), $resultLayout);
        $message = $this->message;
        $emails = array_unique($emails);
        $sharingCode = $wishlist->getSharingCode();
        try {
            foreach ($emails as $email) {
                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $this->scopeConfig->getValue(
                        "wishlist/email/email_template",
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setTemplateOptions(
                    [
                        "area" => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $this->storeManager->getStore()->getStoreId(),
                    ]
                )->setTemplateVars(
                    [
                        "store" => $this->storeManager->getStore(),
                        "items" => $this->getWishlistItems($resultLayout),
                        "salable" => $wishlist->isSalable() ? "yes" : "",
                        "message" => $message,
                        "customer" => $customer,
                        "customerName" => $customerName,
                        "viewOnSiteLink" => $this->_url->getUrl("wishlist/shared/index", ["code"=>$sharingCode])
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        "wishlist/email/email_identity",
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->addTo(
                    $email
                )->getTransport();
                $transport->sendMessage();
                $sent++;
            }
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->getJsonResponse($this->returnArray);
        }
        $wishlist->setShared($wishlist->getShared() + $sent);
        $wishlist->save();
        $this->returnArray["sentTo"] = $sent;
        $this->inlineTranslation->resume();
    }

    /**
     * Retrieve wishlist items content (html)
     *
     * @param \Magento\Framework\View\Result\Layout $resultLayout resultLayout
     *
     * @return string
     */
    protected function getWishlistItems($resultLayout)
    {
        return $resultLayout->getLayout()
            ->getBlock("wishlist.email.items")
            ->toHtml();
    }

    /**
     * Retrieve RSS link content (html)
     *
     * @param int                                   $wishlistId   wishlistId
     * @param \Magento\Framework\View\Result\Layout $resultLayout resultLayout
     *
     * @return mixed
     */
    protected function getRssLink($wishlistId, ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam("rss_url")) {
            return $resultLayout->getLayout()
                ->getBlock("wishlist.email.rss")
                ->setWishlistId($wishlistId)
                ->toHtml();
        }
    }

    /**
     * Prepare to load additional email blocks
     *
     * Add "wishlist_email_rss" layout handle.
     * Add "wishlist_email_items" layout handle.
     *
     * @param \Magento\Framework\View\Result\Layout $resultLayout
     *
     * @return void
     */
    protected function addLayoutHandles(ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam("rss_url")) {
            $resultLayout->addHandle("wishlist_email_rss");
        }
        $resultLayout->addHandle("wishlist_email_items");
    }
}
