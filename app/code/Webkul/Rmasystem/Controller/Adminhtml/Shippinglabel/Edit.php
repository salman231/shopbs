<?php
namespace Webkul\Rmasystem\Controller\Adminhtml\Shippinglabel;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ShippinglabelInterfaceFactory
     */
    protected $shippingLabelDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\ShippingLabelRepositoryInterface
     */
    protected $shippingLabelRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Rmasystem\Api\Data\ShippinglabelInterfaceFactory $shippingLabelDataFactory,
        \Webkul\Rmasystem\Api\ShippingLabelRepositoryInterface $shippingLabelRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->shippingLabelDataFactory = $shippingLabelDataFactory;
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Rmasystem::saveshippinglabel');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Rmasystem::shippinglabel')
            ->addBreadcrumb(__('Shipping Label'), __('Shipping Label'))
            ->addBreadcrumb(__('Shipping Label'), __('Shipping Label'));
        return $resultPage;
    }

    /**
     * Edit Blog post
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->shippingLabelDataFactory->create();

        if ($id) {
            $model = $this->shippingLabelRepository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This field no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('rmasystem_shippinglabel', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Shipping Label') : __('New Shipping Label'),
            $id ? __('Edit Shipping Label') : __('New Shipping Label')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Shipping Labels'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Shipping Label'));

        return $resultPage;
    }
}
