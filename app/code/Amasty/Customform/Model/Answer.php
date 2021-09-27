<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model;

use Amasty\Customform\Api\Data\AnswerInterface;
use Magento\Framework\Exception\LocalizedException;

class Answer extends \Magento\Framework\Model\AbstractModel implements AnswerInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Customform\Api\FormRepositoryInterface
     */
    private $formRepository;

    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Customform\Model\ResourceModel\Answer::class);
        $this->setIdFieldName('answer_id');
        $this->customerRepository = $this->getData('customer_repository');
        $this->formRepository = $this->getData('form_repository');
        $this->helper = $this->getData('helper');
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswerId()
    {
        return $this->_getData(AnswerInterface::ANSWER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAnswerId($answerId)
    {
        $this->setData(AnswerInterface::ANSWER_ID, $answerId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return $this->_getData(AnswerInterface::FORM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormId($formId)
    {
        $this->setData(AnswerInterface::FORM_ID, $formId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->_getData(AnswerInterface::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        $this->setData(AnswerInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->_getData(AnswerInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(AnswerInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIp()
    {
        return $this->_getData(AnswerInterface::IP);
    }

    /**
     * {@inheritdoc}
     */
    public function setIp($ip)
    {
        $this->setData(AnswerInterface::IP, $ip);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseJson()
    {
        return $this->_getData(AnswerInterface::RESPONSE_JSON);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseJson($json)
    {
        $this->setData(AnswerInterface::RESPONSE_JSON, $json);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->_getData(AnswerInterface::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        $this->setData(AnswerInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminResponseEmail()
    {
        return $this->_getData(AnswerInterface::ADMIN_RESPONSE_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdminResponseEmail($responseEmail)
    {
        $this->setData(AnswerInterface::ADMIN_RESPONSE_EMAIL, $responseEmail);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseMessage()
    {
        return $this->_getData(AnswerInterface::ADMIN_RESPONSE_MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseMessage($responseMessage)
    {
        $this->setData(AnswerInterface::ADMIN_RESPONSE_MESSAGE, $responseMessage);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRecipientEmail()
    {
        $email = '';
        if ($this->getAdminResponseEmail()) {
            $email = $this->getAdminResponseEmail();
        }

        if (empty($email)) {
            /** @var \Amasty\Customform\Api\Data\FormInterface $form */
            $form = $this->formRepository->get($this->getFormId());
            $emailField = $form->getEmailField();
            if ($emailField) {
                $data = $this->helper->decode($this->getResponseJson());
                if (!empty($data[$emailField])) {
                    $email = $data[$emailField]['value'];
                }

                if (empty($email) && $this->getCustomerId()) {
                    $email = $this->customerRepository->getById($this->getCustomerId())->getEmail();
                    if (!$email) {
                        throw new LocalizedException(
                            __('Email is not specified for customer with id#%1.', $this->getCustomerId())
                        );
                    }
                }

                if (empty($email)) {
                    throw new LocalizedException(__('Email is not specified.'));
                }
            }
        }

        return $email;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerName()
    {
        $name = '';
        if ($this->getCustomerId()) {
            $name = $this->customerRepository->getById($this->getCustomerId())->getFirstname();
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminResponseStatus()
    {
        return $this->_getData(AnswerInterface::ADMIN_RESPONSE_STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdminResponseStatus($responseStatus)
    {
        $this->setData(AnswerInterface::ADMIN_RESPONSE_STATUS, $responseStatus);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefererUrl()
    {
        return $this->_getData(AnswerInterface::REFERER_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function setRefererUrl($url)
    {
        $this->setData(AnswerInterface::REFERER_URL, $url);
        return $this;
    }
}
