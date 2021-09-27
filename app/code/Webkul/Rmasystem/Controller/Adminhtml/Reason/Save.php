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
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{

   /**
    * @var \Webkul\Rmasystem\Api\ReasonInterfaceFactory
    */
    protected $reasonDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ReasonRepositoryInterface
     */
    protected $reasonRepository;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Webkul\Rmasystem\Api\ReasonRepositoryInterface $reasonRepository,
        \Webkul\Rmasystem\Api\Data\ReasonInterfaceFactory $reasonDataFactory
    ) {
        $this->reasonDataFactory = $reasonDataFactory;
        $this->reasonRepository = $reasonRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Rmasystem::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('id');
            if ($id !== null) {
                $reasonModel = $this->reasonRepository->getById((int)$id);
            } else {
                $reasonModel = $this->reasonDataFactory->create();
            }
            $reasonModel->setData($data);

            try {
                $model = $this->reasonRepository->save($reasonModel);
                $this->messageManager->addSuccess(__('You saved this Reason.'));
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the reason.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
