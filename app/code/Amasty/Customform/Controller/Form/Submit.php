<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Form;

use Amasty\Customform\Model\Answer;
use Amasty\Customform\Model\Form;
use Amasty\Customform\Model\ResourceModel\Form\Element\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\MediaStorage\Model\File\Uploader;
use Amasty\Customform\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class Submit extends \Magento\Framework\App\Action\Action
{
    const SUCCESS_RESULT = 'success';

    const ERROR_RESULT = 'error';

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Customform\Model\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    private $formRepository;

    /**
     * @var \Amasty\Customform\Model\AnswerFactory
     */
    private $answerFactory;

    /**
     * @var \Amasty\Customform\Model\AnswerRepository
     */
    private $answerRepository;

    /**
     * @var \Amasty\Customform\Model\ResourceModel\Form\Element\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var OptionCollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Amasty\Customform\Model\FormRepository $formRepository,
        \Amasty\Customform\Model\AnswerRepository $answerRepository,
        \Amasty\Customform\Model\AnswerFactory $answerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Customform\Model\Template\TransportBuilder $transportBuilder,
        \Amasty\Customform\Model\ResourceModel\Form\Element\CollectionFactory $collectionFactory,
        OptionCollectionFactory $optionCollectionFactory,
        Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        File $fileDriver,
        Escaper $escaper
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->formRepository = $formRepository;
        $this->answerFactory = $answerFactory;
        $this->answerRepository = $answerRepository;
        $this->collectionFactory = $collectionFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->escaper = $escaper;
    }

    public function execute()
    {
        $formId = (int)$this->getRequest()->getParam('form_id');
        $url = Data::REDIRECT_PREVIOUS_PAGE;
        $type = self::ERROR_RESULT;
        if ($this->getRequest()->isPost() && $formId) {
            try {
                $this->validateData();

                /** @var Form $formModel */
                $formModel = $this->formRepository->get($formId);

                /** @var  Answer $model */
                $model = $this->answerFactory->create();
                $answerData = $this->generateAnswerData($formModel);
                $model->addData($answerData);
                $model->setAdminResponseEmail($model->getRecipientEmail());
                $this->answerRepository->save($model);

                $type = self::SUCCESS_RESULT;
                $url = $formModel->getSuccessUrl();
                if ($url && $url != '/') {
                    $url = trim($url, '/');
                }

                $this->sendAdminNotification($formModel, $model);
                $this->sendAutoReply($formModel, $model);

                $message = $formModel->getSuccessMessage();
                if ($message) {
                    $this->messageManager->addSuccessMessage(
                        $message
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(
                    $e->getMessage()
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->messageManager->addErrorMessage(
                    __('Sorry. There is a problem with Your Form Request. Please try again or use Contact Us link in the menu.')
                );
            }
        }

        if ($this->getRequest()->isAjax()) {
            $response = $this->getResponse()->representJson(
                $this->helper->encode(
                    [
                        'result' => $type
                    ]
                )
            );
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            if ($url === Data::REDIRECT_PREVIOUS_PAGE) {
                $url = $this->_redirect->getRefererUrl();
            }
            $resultRedirect->setPath($url);

            $response = $resultRedirect;
        }

        return $response;
    }

    /**
     * @throws LocalizedException
     */
    private function validateData()
    {
        $data = $this->getRequest()->getParams();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Form key is not valid. Please try to reload the page.')
            );
        }

        if ($this->helper->isGDPREnabled() && (!isset($data['gdpr']) || !$data['gdpr'])) {
            throw new LocalizedException(__('Please agree to the Privacy Policy'));
        }
    }

    private function generateAnswerData($formModel)
    {
        $json = $this->generateJson($formModel);
        $data = [
            'form_id'  => $formModel->getId(),
            'store_id' => $this->storeManager->getStore()->getId(),
            'ip'       => $this->helper->getCurrentIp(),
            'customer_id' => (int)$this->helper->getCurrentCustomerId(),
            'response_json' => $json
        ];
        $this->addRefererUrlIfNeed($data, $formModel);

        return $data;
    }

    /**
     * @param array $data
     * @param $formModel
     */
    private function addRefererUrlIfNeed(array &$data, $formModel)
    {
        if ($formModel->getSaveRefererUrl()) {
            $data['referer_url'] = $this->escaper->escapeUrl($this->_redirect->getRefererUrl());
        }
    }

    /**
     * @param $formModel
     * @return string
     * @throws LocalizedException
     */
    private function generateJson($formModel)
    {
        $formJson = $formModel->getFormJson();
        $pages = $this->helper->decode($formJson);
        $data = [];

        foreach ($pages as $page) {
            if (isset($page['type'])) {
                // support for old versions of forms
                $data = $this->dataProcessing($page, $data);
            } else {
                foreach ($page as $field) {
                    $data = $this->dataProcessing($field, $data);
                }
            }
        }
        if ($productId = $this->getRequest()->getParam('hide_product_id', null)) {
            $data['hide_product_id'] = [
                'value' => $productId,
                'label' => __('Requested Product'),
                'type' => 'textinput'
            ];
        }

        return $this->helper->encode($data);
    }

    /**
     * @return array
     */
    private function dataProcessing($data, $record)
    {
        $name = $data['name'];
        $value = $this->getValidValue($data, $name);
        if ($value) {
            $type = $data['type'];
            switch ($type) {
                case 'googlemap':
                    $record[$name]['value'] = $value;
                    break;
                case 'checkbox':
                case 'checkboxtwo':
                case 'dropdown':
                case 'listbox':
                case 'radio':
                case 'radiotwo':
                    $tmpValue = [];

                    foreach ($data['values'] as $option) {
                        if (is_array($value) && in_array($option['value'], $value)) {
                            $tmpValue[] = $option['label'];
                        } elseif ($value == $option['value']) {
                            $tmpValue[] = $option['label'];
                            break;
                        }
                    }

                    $record[$name]['value'] = $tmpValue ? implode(', ', $tmpValue) : $value;
                    break;
                default:
                    $value = $this->helper->escapeHtml($value);
                    $record[$name]['value'] = $value;
            }

            $record[$name]['label'] = $data['label'];
            $record[$name]['type'] = $type;
        }

        return $record;
    }

    /**
     * @param $field
     * @param $name
     * @return array|mixed
     * @throws LocalizedException
     */
    private function getValidValue($field, $name)
    {
        $result = $this->getRequest()->getParam($name, '');
        $fileValidation = [];
        $validation = $this->getRow($field, 'validation');
        $fieldType = $this->getRow($field, 'type');
        $isFile = strcmp($fieldType, 'file') === 0;

        if (trim($validation) && $validation !== 'None') {
            $validation = $this->helper->decode(str_replace('&quot;', '"', $validation));
            $valueNotExist = (!$isFile && !$result)
                || ($isFile && !array_key_exists($name, $this->getRequest()->getFiles()->toArray()));

            if (!array_key_exists('required', $validation)
                && $valueNotExist
            ) {
                return $result;
            }

            foreach ($validation as $key => $item) {
                switch ($key) {
                    case 'required':
                        if ($result === '' && ($fieldType != 'file')) {
                            if ($this->isHiddenField($field)) {
                                continue;
                            }
                            $name = isset($field['title']) ? $field['title'] : $field['label'];
                            throw new LocalizedException(__('Please enter a %1.', $name));
                        }
                        break;
                    case 'validation':
                        if ($item == 'validate-email' && !$this->isHiddenField($field)) {
                            $result = filter_var($result, FILTER_SANITIZE_EMAIL);
                            if (!\Zend_Validate::is($result, 'EmailAddress')) {
                                throw new LocalizedException(__('Please enter a valid email address.'));
                            }
                        }
                        break;
                    case 'allowed_extension':
                    case 'max_file_size':
                        $fileValidation[$key] = $item;
                        break;
                }
            }
        }

        if ($fieldType == 'googlemap' && $result) {
            $coordinates = explode(', ', trim($result, '()'));
            if (!isset($coordinates[0]) || !isset($coordinates[1])) {
                $coordinates = [0, 0];
            }
            $result = $this->helper->encode(
                [
                    'position' => [
                        'lat' => (float)$coordinates[0],
                        'lng' => (float)$coordinates[1]
                    ],
                    'zoom' => (int)$field['zoom']
                ]
            );
        }

        if ($isFile && !$this->getRequest()->isAjax() && !$this->isHiddenField($field)) {
            $result = $this->helper->saveFileField($name, $fileValidation);
        }

        return $result;
    }

    /**
     * @param Form $formModel
     * @param Answer $model
     */
    private function sendAdminNotification(Form $formModel, Answer $model)
    {
        $emailTo = trim($formModel->getSendTo());
        if (!$emailTo) {
            $emailTo = trim($this->helper->getModuleConfig('email/recipient_email'));
        }

        if ($emailTo && $this->helper->getModuleConfig('email/enabled') && $formModel->getSendNotification()) {
            $sender = $this->helper->getModuleConfig('email/sender_email_identity');
            $template = $formModel->getEmailTemplate();
            if (!$template) {
                $template = $this->helper->getModuleConfig('email/template');
            }

            $model->setFormTitle($formModel->getTitle());
            $customerData = $this->helper->getCustomerName($model->getCustomerId());

            try {
                $store = $this->storeManager->getStore();
                $data =  [
                    'website_name'  => $store->getWebsite()->getName(),
                    'group_name'    => $store->getGroup()->getName(),
                    'store_name'    => $store->getName(),
                    'response'      => $model,
                    'link'          => $this->helper->getAnswerViewUrl($model->getAnswerId()),
                    'submit_fields' => $this->getSubmitFields($model),
                    'customer_name' => $customerData['customer_name'],
                    'customer_link' => $customerData['customer_link'],
                ];

                if (strpos($emailTo, ',') !== false) {
                    $emailTo = array_map('trim', explode(',', $emailTo));
                }

                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $template
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
                )->setTemplateVars(
                    $data
                )->setFrom(
                    $sender
                )->addTo(
                    $emailTo
                );

                $replyTo = $model->getRecipientEmail();
                if ($replyTo) {
                    $transport->setReplyTo($replyTo);
                }

                $transport->getTransport()->sendMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Unable to send the email.'));
            }
        }
    }

    /**
     * @param Form $formModel
     * @param Answer $model
     * @throws LocalizedException
     */
    private function sendAutoReply(Form $formModel, Answer $model)
    {
        if (!$this->helper->isAutoReplyEnabled()) {
            return;
        }

        $emailTo = $model->getRecipientEmail();
        if ($emailTo) {
            $sender = $this->helper->getAutoReplySender();
            $template =  $this->helper->getAutoReplyTemplate();

            $model->setFormTitle($formModel->getTitle());
            $customerData = $this->helper->getCustomerName($model->getCustomerId());

            try {
                $store = $this->storeManager->getStore();
                $data =  [
                    'website_name'  => $store->getWebsite()->getName(),
                    'group_name'    => $store->getGroup()->getName(),
                    'store_name'    => $store->getName(),
                    'response'      => $model,
                    'customer_name' => $customerData['customer_name'],
                    'form_name'     => $formModel->getTitle()
                ];

                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $template
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
                )->setTemplateVars(
                    $data
                )->setFrom(
                    $sender
                )->addTo(
                    $emailTo
                )->getTransport();

                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Unable to send the email.'));
            }
        }
    }

    private function getSubmitFields(Answer &$model)
    {
        $html = '<table cellpadding="7">';
        $formData = $model->getResponseJson();

        if ($formData) {
            $fields = $this->helper->decode($formData);

            foreach ($fields as $field) {
                $value = $this->getRow($field, 'value');
                $fieldType = $this->getRow($field, 'type');

                if ($fieldType == 'file') {
                    $filePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
                        . Data::MEDIA_PATH . Uploader::getCorrectFileName($value);
                    $this->transportBuilder->addAttachment(
                        $this->fileDriver->fileGetContents($filePath),
                        $value
                    );
                }

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $html .= '<tr>'
                    . '<td style="width: 50%;">' . $field['label'] . '</td>'
                    . '<td>' . $value . '</td>'
                    . '</tr>';
            }
        }

        $html .= '</table>';

        return $html;
    }

    private function getRow($field, $type)
    {
        return isset($field[$type]) ? $field[$type] : null;
    }

    /**
     * field hidden by dependency
     *
     * @param array $field
     *
     * @return bool
     */
    private function isHiddenField($field)
    {
        $isHidden = false;
        if (isset($field['dependency']) && $field['dependency']) {
            foreach ($field['dependency'] as $dependency) {
                if (isset($dependency['field'])
                    && isset($dependency['value'])
                    && $this->getRequest()->getParam($dependency['field']) != $dependency['value']
                ) {
                    $isHidden = true;
                }
            }
        }
        if (!$isHidden) {
            $emailField = $this->formRepository->get(
                (int)$this->getRequest()->getParam('form_id')
            )->getEmailField();
            if ($emailField == $field['name'] && $this->helper->getCurrentCustomerId()) {
                $isHidden = true;
            }
        }

        return $isHidden;
    }
}
