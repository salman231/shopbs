<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Api\Data;

/**
 * @api
 */
interface FormInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const FORM_ID = 'form_id';
    const CODE = 'code';
    const TITLE = 'title';
    const SUCCESS_URL = 'success_url';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const CUSTOMER_GROUP = 'customer_group';
    const STORE_ID = 'store_id';
    const SEND_NOTIFICATION = 'send_notification';
    const SEND_TO = 'send_to';
    const EMAIL_TEMPLATE = 'email_template';
    const SUBMIT_BUTTON = 'submit_button';
    const SUCCESS_MESSAGE = 'success_message';
    const FORM_JSON = 'form_json';
    const EMAIL_FIELD = 'email_field';
    const EMAIL_FIELD_HIDE = 'email_field_hide';
    const POPUP_SHOW = 'popup_show';
    const POPUP_BUTTON = 'popup_button';
    const FORM_TITLE = 'form_title';
    const SAVE_REFERER_URL = 'save_referer_url';

    /**
     * @return int
     */
    public function getFormId();

    /**
     * @param int $formId
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setFormId($formId);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getSuccessUrl();

    /**
     * @param string $successUrl
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setSuccessUrl($successUrl);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getCustomerGroup();

    /**
     * @param string $customerGroup
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setCustomerGroup($customerGroup);

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * @param string $storeId
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getSendNotification();

    /**
     * @param int $sendNotification
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setSendNotification($sendNotification);

    /**
     * @return string
     */
    public function getSendTo();

    /**
     * @param string $sendTo
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setSendTo($sendTo);

    /**
     * @return string
     */
    public function getEmailTemplate();

    /**
     * @param string $emailTemplate
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setEmailTemplate($emailTemplate);

    /**
     * @return string
     */
    public function getSubmitButton();

    /**
     * @param string $submitButton
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setSubmitButton($submitButton);

    /**
     * @return string
     */
    public function getSuccessMessage();

    /**
     * @param string $successMessage
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setSuccessMessage($successMessage);

    /**
     * @return string
     */
    public function getFormJson();

    /**
     * @param string $json
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setFormJson($json);

    /**
     * @return bool
     */
    public function isHideEmailField();

    /**
     * @param bool $hide
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setEmailFieldHide($hide);

    /**
     * @return string
     */
    public function getEmailField();

    /**
     * @param string $emailField
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setEmailField($emailField);

    /**
     * @return bool
     */
    public function isPopupShow();

    /**
     * @param bool $popupShow
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setPopupShow($popupShow);

    /**
     * @return string
     */
    public function getPopupButton();

    /**
     * @param string $popupButton
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setPopupButton($popupButton);

    /**
     * @return string
     */
    public function getFormTitle();

    /**
     * @param string $json
     *
     * @return \Amasty\Customform\Api\Data\FormInterface
     */
    public function setFormTitle($json);
}
