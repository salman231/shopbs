<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Controller\Ajax;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Review controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Product extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Generic session
     *
     * @var \Magento\Framework\Session\Generic
     */
    protected $reviewSession;

    /**
     * Catalog category model
     *
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Catalog product model
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $ratingFactory;

    /**
     * Catalog design model
     *
     * @var \Magento\Catalog\Model\Design
     */
    protected $catalogDesign;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core form key validator
     *
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Framework\Session\Generic $reviewSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Review\Helper\Data $reviewHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Framework\Session\Generic $reviewSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Review\Helper\Data $reviewHelper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->reviewSession = $reviewSession;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->catalogDesign = $catalogDesign;
        $this->formKeyValidator = $formKeyValidator;
        $this->reviewHelper = $reviewHelper;
        $this->customerUrl = $customerUrl;

        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $allowGuest = $this->reviewHelper->getIsGuestAllowToWrite();
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }

        if (!$allowGuest && $request->getActionName() == 'post' && $request->isPost()) {
            if (!$this->customerSession->isLoggedIn()) {
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                $this->customerSession->setBeforeAuthUrl($this->_url->getUrl('*/*/*', ['_current' => true]));
                $this->reviewSession->setFormData(
                    $request->getPostValue()
                )->setRedirectUrl(
                    $this->_redirect->getRefererUrl()
                );
                $this->getResponse()->setRedirect(
                    $this->customerUrl->getLoginUrl()
                );
            }
        }

        return parent::dispatch($request);
    }

    /**
     * Initialize and check product
     *
     * @return \Magento\Catalog\Model\Product|bool
     */
    protected function initProduct()
    {
        $this->_eventManager->dispatch('review_controller_product_init_before', ['controller_action' => $this]);
        $categoryId = (int)$this->getRequest()->getParam('category', false);
        $productId = (int)$this->getRequest()->getParam('id');

        $product = $this->loadProduct($productId);
        if (!$product) {
            return false;
        }

        if ($categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            $this->coreRegistry->register('current_category', $category);
        }

        try {
            $this->_eventManager->dispatch('review_controller_product_init', ['product' => $product]);
            $this->_eventManager->dispatch(
                'review_controller_product_init_after',
                ['product' => $product, 'controller_action' => $this]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            return false;
        }

        return $product;
    }

    /**
     * Load product model with data by passed id.
     * Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    protected function loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        try {
            $product = $this->productRepository->getById($productId);

            if (!in_array($this->storeManager->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
                throw new NoSuchEntityException();
            }
        } catch (\Exception $e) {
            return false;
        }

        $this->coreRegistry->register('current_product', $product);
        $this->coreRegistry->register('product', $product);

        return $product;
    }
}
