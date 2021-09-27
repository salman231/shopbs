<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml\Answer;

use Magento\Store\Model\ScopeInterface;
use Amasty\Customform\Model\Grid\Bookmark;
use Amasty\Customform\Model\Config\Source\Status;

class Send extends \Amasty\Customform\Controller\Adminhtml\Answer
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Amasty\CustomForm\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    private $formRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Customform\Model\AnswerRepository $answerRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Amasty\Customform\Helper\Data $helper,
        \Amasty\Customform\Model\FormRepository $formRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Bookmark $bookmark
    ) {
        parent::__construct(
            $context,
            $answerRepository,
            $coreRegistry,
            $resultPageFactory,
            $logger,
            $bookmark
        );

        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->formRepository = $formRepository;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $answerId = $this->getRequest()->getParam('answer_id');
        $message = $this->getRequest()->getParam('email_text');
        try {
            if (!\Zend_Validate::is(trim($message), 'NotEmpty')) {
                $this->messageManager->addErrorMessage(__('Please enter a Email Text.'));
                $this->_redirect('amasty_customform/answer/edit', ['id' => $answerId]);
                return;
            }

            if ($answerId) {
                $model = $this->answerRepository->get($answerId);
                if ($model->getAdminResponseStatus() == Status::ANSWERED) {
                    $this->messageManager->addNoticeMessage(__('Email response is already sent.'));
                } else {
                    $emailTo = $model->getRecipientEmail();
                    if ($this->sendEmail($model, $emailTo, $message)) {
                        $model->setResponseMessage($message);
                        $model->setAdminResponseEmail($emailTo);
                        $model->setAdminResponseStatus(Status::ANSWERED);
                        $this->answerRepository->save($model);
                        $this->messageManager->addSuccessMessage(__('Email response is sent.'));
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('Submitted data id is not specified.'));
                return $this->_redirect('amasty_customform/answer');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } finally {
            $this->_redirect('amasty_customform/answer/edit', ['id' => $answerId]);
        }
    }

    /**
     * @param \Amasty\Customform\Model\Answer $model
     * @param string $emailTo
     * @param string $message
     * @return bool
     */
    private function sendEmail(\Amasty\Customform\Model\Answer $model, $emailTo, $message)
    {
        try {
            $storeId = $model->getStoreId();
            $sender = $this->helper->getModuleConfig('response/sender', $storeId);
            $template = $this->helper->getModuleConfig('response/template', $storeId);
            $bcc = $this->helper->getModuleConfig('response/bcc', $storeId);
            $store = $this->storeManager->getStore($model->getStoreId());
            $data =  [
                'website_name'  => $store->getWebsite()->getName(),
                'group_name'    => $store->getGroup()->getName(),
                'store_name'    => $store->getName(),
                'form_name'     => $this->formRepository->get($model->getFormId())->getTitle(),
                'answer'        => $model,
                'message'       => $message,
                'customer_name' => $model->getCustomerName()
            ];

            if (!empty($bcc)) {
                $bcc = explode(',', $bcc);
                $bcc = array_map('trim', $bcc);
                $this->transportBuilder->addBcc($bcc);
            }

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
            )
                ->setTemplateVars($data)
                ->setFrom($sender)
                ->addTo($emailTo)
                ->getTransport();
            $transport->sendMessage();
            return true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        }
    }
}
