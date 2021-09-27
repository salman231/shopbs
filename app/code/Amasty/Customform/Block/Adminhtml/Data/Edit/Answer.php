<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Adminhtml\Data\Edit;

use Amasty\Customform\Helper\Data;
use Amasty\Customform\Model\Config\Source\Status;
use Magento\MediaStorage\Model\File\Uploader;

class Answer extends \Magento\Backend\Block\Template
{
    protected $currentResponse;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    private $formRepository;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Amasty\Customform\Model\ResourceModel\Form\Element\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Amasty\Customform\Model\FormRepository $formRepository,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        Data $helper,
        \Amasty\Customform\Model\ResourceModel\Form\Element\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $context->getStoreManager();
        $this->formRepository = $formRepository;
        $this->jsonDecoder = $jsonDecoder;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;

        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
    }

    public function _construct()
    {
        $this->currentResponse = $this->coreRegistry->registry(
            \Amasty\Customform\Controller\Adminhtml\Answer::CURRENT_ANSWER_MODEL
        );
        parent::_construct();
    }

    /**
     * Add buttons on request view page
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'back_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Back'),
                'onclick' => 'setLocation("' . $this->getUrl('*/*/index') . '")',
                'class' => 'back'
            ]
        );
        $this->getToolbar()->addChild(
            'delete_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Delete Data'),
                'onclick' => 'setLocation("' . $this->getUrl('*/*/delete', [
                        'id' => $this->currentResponse->getAnswerId()
                    ]) . '")',
                'class' => 'delete'
            ]
        );
        $sendEmailButton = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData(
                [
                    'label' => __('Send Email'),
                    'class' => 'action-save action-secondary',
                    'onclick' => 'this.form.submit();'
                ]
            );
        $this->setChild('submit_button', $sendEmailButton);

        parent::_prepareLayout();
        return $this;
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/send', ['answer_id' => $this->getCurrentResponse()->getAnswerId()]);
    }

    /**
     * @return array
     */
    public function getInformationData()
    {
        /** @var \Amasty\Customform\Model\Answer $model */
        $model = $this->getCurrentResponse();

        try {
            $form = $this->formRepository->get($model->getFormId());
        } catch (\Exception $ex) {
            $form = null;
        }
        $formName = $form ? $form->getCode(): __('This Form #%1 was removed', $model->getFormId());

        $customerName = $this->helper->getCustomerName($model->getCustomerId(), true);
        $customerName = (array_key_exists('customer_link', $customerName) && $customerName['customer_link'])
            ? $customerName['customer_link'] : $customerName['customer_name'];
        $store = $this->storeManager->getStore($model->getStoreId())->getName();

        $result =  [
            ['label' => __('Form'), 'value' => $formName],
            ['label' => __('Submitted'), 'value' => $model->getCreatedAt()],
            ['label' => __('IP'), 'value' => $model->getIp()],
            ['label' => __('Customer'), 'value' => $customerName],
            ['label' => __('Store'), 'value' => $store]
        ];

        if ($form->getSaveRefererUrl()) {
            $result[] = ['label' => __('Referer URL'), 'value' => $this->escapeUrl($model->getRefererUrl())];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getResponseData()
    {
        /** @var \Amasty\Customform\Model\Answer $model */
        $model = $this->getCurrentResponse();
        $result = [];
        $formData = $model->getResponseJson();
        if ($formData) {
            $fields = $this->jsonDecoder->decode($formData);

            foreach ($fields as $name => $field) {
                $value = $field['value'];
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                switch ($field['type']) {
                    case 'googlemap':
                        // dont use escape for json
                        break;
                    case 'file':
                        $value = Uploader::getCorrectFileName($value);
                        $url = $this->_storeManager->getStore()
                                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                            . Data::MEDIA_PATH . $value;
                        $value = '<a download href="' . $url .  '">' . __('Download: ') . $value . '</a>';
                        break;
                    default:
                        $value = $this->escapeHtml($value);
                }

                if ($name == 'hide_product_id') {
                    try {
                        $product = $this->productRepository->getById($value);
                        $value =  sprintf(
                            '<a href="%s">%s</a>',
                            $this->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                            $product->getName()
                        );
                    } catch (\Exception $ex) {
                        $product = null;
                    }
                }

                $result[] = [
                    'label' => $field['label'],
                    'value' => $value,
                    'type'  => $field['type']
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAdminResponseData()
    {
        /** @var \Amasty\Customform\Model\Answer $model */
        $model = $this->getCurrentResponse();
        $responseStatus = $model->getAdminResponseStatus();
        $status =  $responseStatus == Status::ANSWERED ? __('Sent') : __('Pending');
        $result =  [['label' => __('Response Status'), 'value' => $status]];
        if ($responseStatus) {
            $result[] = ['label' => __('Recipient'), 'value' => $model->getAdminResponseEmail()];
            $result[] = ['label' => __('Response Message'), 'value' => $this->escapeHtml($model->getResponseMessage())];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isShowEmailSendingForm()
    {
        return $this->getCurrentResponse()->getAdminResponseStatus() == Status::PENDING;
    }

    /**
     * @return \Amasty\Customform\Model\Answer
     */
    public function getCurrentResponse()
    {
        return $this->currentResponse;
    }

    /**
     * @return null|string
     */
    public function getGoogleKey()
    {
        return $this->helper->getGoogleKey();
    }
}
