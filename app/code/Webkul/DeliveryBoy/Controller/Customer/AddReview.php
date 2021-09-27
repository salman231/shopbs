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
namespace Webkul\DeliveryBoy\Controller\Customer;

use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;
use Psr\Log\LoggerInterface;
use Webkul\DeliveryBoy\Helper\Data as DeliveryboyDataHelper;

class AddReview extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $resultJsonFactory;
    
    /**
     * @var \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface
     */
    protected $deliveryBoyRepository;
    
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;
        
    /**
     * @var \Webkul\DeliveryBoy\Api\RatingRepositoryInterface
     */
    protected $ratingRepository;
        
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
        
    /**
     * @var \Webkul\DeliveryBoy\Model\RatingFactory
     */
    protected $ratingFactory;
        
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DeliveryboyDataHelper
     */
    protected $deliveryboyDataHelper;
 
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Webkul\DeliveryBoy\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryBoyRepository
     * @param \Webkul\DeliveryBoy\Api\RatingRepositoryInterface $ratingRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Webkul\DeliveryBoy\Model\RatingFactory $ratingFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param DeliveryboyDataHelper $deliveryboyDataHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\DeliveryBoy\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryBoyRepository,
        \Webkul\DeliveryBoy\Api\RatingRepositoryInterface $ratingRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\DeliveryBoy\Model\RatingFactory $ratingFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        DeliveryboyDataHelper $deliveryboyDataHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->deliveryBoyRepository = $deliveryBoyRepository;
        $this->ratingRepository = $ratingRepository;
        $this->customerRepository = $customerRepository;
        $this->ratingFactory = $ratingFactory;
        $this->dateTime = $dateTime;
        $this->jsonHelper = $jsonHelper;
        $this->deliveryboyDataHelper = $deliveryboyDataHelper;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $request = $this->getRequest();
        $response = ['error' => true];
        try {
            if ($request->getMethod() === "POST" && $request->isXmlHttpRequest() &&
                $this->formKeyValidator->validate($request)
            ) {
                $deliveryBoyId = $request->getParam('deliveryBoyId') ?? null;
                $customerId = $request->getParam('customerId') ?? null;
                $title = $request->getParam('title') ?? null;
                $rating = $request->getParam('rating') ?? null;
                $comment = $request->getParam('comment') ?? null;

                $deliveryboy = $this->deliveryBoyRepository->getById($deliveryBoyId);
                if (!$deliveryBoyId || ($deliveryBoyId && $deliveryboy->getId() != $deliveryBoyId)) {
                    throw new LocalizedException(__("Invalid Delivery boy."));
                }
                try {
                    $customer = $this->customerRepository->getById($customerId);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $customer = null;
                    $this->logger->info("Invalid customer Id: " . $customerId);
                }
                if ($rating > 5 || $rating < 1) {
                    throw new LocalizedException(__("Invalid rating."));
                }
                if (!$title) {
                    throw new LocalizedException(__("Title is required field."));
                }
                if (!$comment) {
                    throw new LocalizedException(__("Comment is required field."));
                }
                try {
                    $review = $this->ratingFactory->create()
                        ->setTitle($title)
                        ->setComment($comment)
                        ->setDeliveryboyId($deliveryBoyId)
                        ->setCustomerId($customerId)
                        ->setRating($rating)
                        ->setStatus(\Magento\Review\Model\Review::STATUS_PENDING)
                        ->setCreatedAt($this->dateTime->gmtDate());
                    $this->ratingRepository->save($review);
                    
                    $storeId = $customer ? $customer->getStoreId() : null;
                    $customerName = $customer ? $customer->getFirstname() : __("Guest");
                    $ratingMaxLimit = ModuleGlobalConstants::RATING_MAX_VALUE;
                    $ratingManagerName = ModuleGlobalConstants::DEFAULT_RATING_MANAGER_NAME;
                    $ratingMaxLimit = ModuleGlobalConstants::RATING_MAX_VALUE;
                    $ratingManagerName = ModuleGlobalConstants::DEFAULT_RATING_MANAGER_NAME;
                    $senderInfo = [
                        'name' => ModuleGlobalConstants::DEFAULT_ADMIN_NAME,
                        'email' => $this->deliveryboyDataHelper->getConfigData(
                            ModuleGlobalConstants::DEFAULT_ADMIN_EMAIL_XML_PATH
                        ),
                    ];
                    $receiversInfo = [
                        'name' => $deliveryboy->getName(),
                        'email' => $deliveryboy->getEmail(),
                    ];
                    $ratingStatusLabel = $this->deliveryboyDataHelper->getRatingStatuses()[$review->getStatus()];
                    $review->setStatus($ratingStatusLabel);
                    $this->_eventManager->dispatch(
                        'inform_deliveryboy_new_review_event',
                        [
                            'store_id' => $storeId,
                            'customer_name' => $customerName,
                            'rating_max_limit' => $ratingMaxLimit,
                            'rating_manager_name' => $ratingManagerName,
                            'deliveryboy' => $deliveryboy,
                            'sender_info' => $senderInfo,
                            'receivers_info' => $receiversInfo,
                            'review' => $review,
                        ]
                    );
                } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
                    throw new LocalizedException(__("Unable to save review. Please try again later."));
                }
                $response['error'] = false;
                $response['message'] = __("Thanks for your review, it is submitted for moderation.");
            } else {
                throw new LocalizedException(__("Invalid Request."));
            }
        } catch (\Throwable $e) {
            $response['message'] = $e->getMessage();
        }
        return $this->resultJsonFactory->create()->setData($response);
    }
}
