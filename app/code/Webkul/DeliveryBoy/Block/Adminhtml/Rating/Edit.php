<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Block\Adminhtml\Rating;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->formKey = $formKey;
        $this->coreRegistry = $registry;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = "id";
        $this->_blockGroup = "Webkul_DeliveryBoy";
        $this->_controller = "adminhtml";
        $this->buttonList->update("save", "id", "save_button");
        $this->buttonList->update("save", "label", __("Update Review"));
        $this->buttonList->update("delete", "label", __("Delete Review"));
        $this->buttonList->update("delete", "onclick", 'deleteConfirm(' .
            $this->jsonHelper->jsonEncode(__('Are you sure you want to do this?')) . ',' .
            $this->jsonHelper->jsonEncode($this->getDeleteUrl()) . ')');
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        $url = $this->getUrl(
            "*/*/delete",
            [
                "id"=>$this->getRequest()->getParam("id"),
                "form_key"=>$this->formKey->getFormKey()
            ]
        );
        return $url;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $reviewData = $this->coreRegistry->registry("review_data");
        if ($reviewData && $reviewData->getId()) {
            return __("Edit Review '%1'", $this->escapeHtml($reviewData->getTitle()));
        } else {
            return __("New Review");
        }
    }
}
