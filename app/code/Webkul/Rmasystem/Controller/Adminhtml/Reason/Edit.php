<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Controller\Adminhtml\Reason;

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
     * @var \Webkul\Rmasystem\Api\Data\ReasonRepositoryInterface
     */
    protected $reasonRepository;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ReasonRepositoryInterface
     */
    protected $reasonDataFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Rmasystem\Api\ReasonRepositoryInterface $reasonRepository,
        \Webkul\Rmasystem\Api\Data\ReasonInterfaceFactory $reasonDataFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->reasonRepository = $reasonRepository;
        $this->reasonDataFactory = $reasonDataFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Rmasystem::reason');
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
        $resultPage->setActiveMenu('Webkul_Rmasystem::reason')
            ->addBreadcrumb(__('Reasons'), __('Reasons'))
            ->addBreadcrumb(__('Manage Reasons'), __('Manage Reasons'));
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
        $model = $this->reasonDataFactory->create();
        if ($id) {
            $model = $this->reasonRepository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This reason no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('rmasystem_reason', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Reason') : __('New Reason'),
            $id ? __('Edit Reason') : __('New Reason')
        );
        
        $resultPage->getConfig()->getTitle()->prepend(__('Reasons'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __('Edit Reason') : __('New Reason'));

        return $resultPage;
    }
}
