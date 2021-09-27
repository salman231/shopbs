<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Store;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\Customform\Helper\Data;

class Init extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'init.phtml';

    /**
     * @var \Amasty\Customform\Model\Form
     */
    protected $currentForm;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    private $formRepository;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var bool
     */
    private $useGoogleMap = false;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var array
     */
    private $additionalClasses = [];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        \Amasty\Customform\Model\FormRepository $formRepository,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->request = $request;
        $this->formRepository = $formRepository;
        $this->sessionFactory = $sessionFactory;

        parent::__construct($context, $data);
        $this->formKey = $formKey;
    }

    public function _construct()
    {
        $id = $this->getFormId();
        if ($id) {
            try {
                $this->currentForm = $this->formRepository->get($id);
                $this->updateFormInfo();
            } catch (NoSuchEntityException $e) {
                $this->currentForm = false;
            }
        }
        parent::_construct();
    }

    /**
     * @return \Amasty\Customform\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->validate()) {
            return parent::toHtml();
        }

        return '';
    }

    protected function validate()
    {
        if (!$this->currentForm || !$this->currentForm->isEnabled()) {
            return false;
        }
        /* check for store ids*/
        $stores = $this->currentForm->getStoreId();
        $stores = explode(',', $stores);
        $currentStoreId = $this->_storeManager->getStore()->getId();
        if (!in_array(Store::ALL_STORE_VIEWS, $stores) && !in_array($currentStoreId, $stores)) {
            return false;
        }

        /* check for customer groups*/
        $availableGroups = $this->currentForm->getCustomerGroup();
        $availableGroups = explode(',', $availableGroups);
        $currentGroup = $this->sessionFactory->create()->getCustomerGroupId();
        if (!in_array($currentGroup, $availableGroups)) {
            return false;
        }

        return true;
    }

    /**
     * @return \Amasty\Customform\Model\Form
     */
    public function getCurrentForm()
    {
        return $this->currentForm;
    }

    /**
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function getButtonTitle()
    {
        $title = $this->currentForm->getSubmitButton();
        if (!$title) {
            $title = __('Submit');
        }

        return $title;
    }

    /**
     * @return string
     */
    public function getFormDataJson()
    {
        $formData =  $this->getCurrentForm()->getFormJson();
        $formTitles =  $this->getCurrentForm()->getFormTitle();

        $result = [
            'dataType' => 'json',
            'formData' => $formData,
            'src_image_progress' => $this->getViewFileUrl('Amasty_Customform::images/loading.gif'),
            'ajax_submit' => $this->getCurrentForm()->getSuccessUrl() == Data::REDIRECT_PREVIOUS_PAGE ? 1 : 0,
            'pageTitles' => $formTitles,
            'submitButtonTitle' => $this->escapeHtml($this->getButtonTitle()),
            'dateFormat' => $this->helper->getDateFormat()
        ];

        return $this->helper->encode($result);
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->helper->getSubmitUrl();
    }

    /**
     * Check if GDPR consent enabled
     *
     * @return bool
     */
    public function isGDPREnabled()
    {
        return $this->helper->isGDPREnabled();
    }

    /**
     * Get text for GDPR
     *
     * @return string
     */
    public function getGDPRText()
    {
        return $this->helper->getGDPRText();
    }

    public function updateFormInfo()
    {
        $formData = $this->getCurrentForm()->getFormJson();
        $formData = $this->helper->decode($formData);

        foreach ($formData as $index => &$page) {
            if (isset($page['type'])) {
                // support for old versions of forms
                $this->dataProcessing($page, $index, $formData);
            } else {
                foreach ($page as $key => $value) {
                    $this->dataProcessing($value, $key, $page);
                }
            }
        }

        $this->getCurrentForm()->setFormJson(
            $this->helper->encode(array_values($formData))
        );
    }

    /**
     * @return array
     */
    private function dataProcessing($data, $key, &$formData)
    {
        $hideEmail = $this->getCurrentForm()->isHideEmailField()
            && !empty($this->sessionFactory->create()->getCustomer()->getEmail());

        if ($hideEmail && $data['name'] === $this->getCurrentForm()->getEmailField()) {
            unset($formData[$key]);
            $formData = array_values($formData);
        }

        if ($data['type'] == 'googlemap') {
            $this->setUseGoogleMap(true);
        }
    }

    /**
     * @return bool
     */
    public function isUseGoogleMap()
    {
        return $this->useGoogleMap;
    }

    /**
     * @param bool $useGoogleMap
     */
    public function setUseGoogleMap($useGoogleMap)
    {
        $this->useGoogleMap = $useGoogleMap;
    }

    /**
     * @return mixed
     */
    public function getGoogleKey()
    {
        return $this->helper->getGoogleKey();
    }

    /**
     * @return bool
     */
    public function isPopupUsed()
    {
        return $this->currentForm->isPopupShow();
    }

    /**
     * @return string
     */
    public function getTriggerPopup()
    {
        return strip_tags($this->currentForm->getPopupButton());
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return string
     */
    public function getAdditionalClasses()
    {
        return implode(' ', $this->additionalClasses);
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function addAdditionalClass($class)
    {
        $this->additionalClasses[] = $class;

        return $this;
    }
}
