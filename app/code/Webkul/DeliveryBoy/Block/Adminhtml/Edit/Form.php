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
namespace Webkul\DeliveryBoy\Block\Adminhtml\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Review\Helper\Data
     */
    protected $reviewData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\DeliveryboyRepositoryInterface
     */
    protected $deliveryBoy;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @param \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryBoy
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryBoy,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->store = $store;
        $this->reviewData = $reviewData;
        $this->deliveryBoy = $deliveryBoy;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return self
     */
    protected function _prepareForm()
    {
        $review = $this->_coreRegistry->registry("review_data");
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                "data" => [
                    "id" => "edit_form",
                    "action" => $this->getUrl(
                        "*/*/save",
                        [
                            "id" => $this->getRequest()->getParam("id")
                        ]
                    ),
                    "method" => "post",
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            "review_details",
            ["legend" => __("Review Details"), "class" => "fieldset-wide"]
        );

        $customer = $this->customerRepository->getById($review->getCustomerId());
        $customerText = __(
            '<a href="%1" onclick="this.target=\"blank\"">%2 %3</a> <a href="mailto:%4">(%4)</a>',
            $this->getUrl("customer/index/edit", ["id" => $customer->getId(), "active_tab" => "review"]),
            $this->escapeHtml($customer->getFirstname()),
            $this->escapeHtml($customer->getLastname()),
            $this->escapeHtml($customer->getEmail())
        );

        $fieldset->addField(
            "customer",
            "note",
            [
                "label" => __("Author"),
                "text" => $customerText
            ]
        );

        $deliveryboy = $this->deliveryBoy->getById($review->getDeliveryboyId());
        $profileUrl = $this->store->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $deliveryboy->getImage();
        $deliveryBoyText = __(
            '<a href="%1" onclick="this.target=\"blank\""><img src="%4"/>%2 </a> <a href="mailto:%3">(%3)</a>',
            $this->getUrl("expressdelivery/deliveryboy/edit", ["id"=>$deliveryboy->getId()]),
            $this->escapeHtml($deliveryboy->getName()),
            $this->escapeHtml($deliveryboy->getEmail()),
            $this->escapeHtml($profileUrl)
        );

        $fieldset->addField(
            "deliveryboy",
            "note",
            [
                "label" => __("Delivery Boy"),
                "text" => $deliveryBoyText
            ]
        );

        $fieldset->addField(
            "summary-rating",
            "note",
            [
                "label" => __("Summary Rating"),
                "text" => $this->getLayout()->createBlock(
                    \Webkul\DeliveryBoy\Block\Adminhtml\Rating\Summary::class
                )->toHtml()
            ]
        );

        $fieldset->addField(
            "status",
            "select",
            [
                "name" => "status",
                "required" => true,
                "label" => __("Status"),
                "values" => $this->reviewData->getReviewStatusesOptionArray()
            ]
        );

        $fieldset->addField(
            "title",
            "text",
            [
                "name" => "title",
                "required" => true,
                "label" => __("Summary of Review")
            ]
        );

        $fieldset->addField(
            "comment",
            "textarea",
            [
                "required" => true,
                "name" => "comment",
                "label" => __("Review"),
                "style" => "height:24em;"
            ]
        );

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
