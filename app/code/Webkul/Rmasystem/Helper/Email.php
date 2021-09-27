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
namespace Webkul\Rmasystem\Helper;

use Magento\Sales\Model\OrderRepository;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
    * Recipient email config path
    */
    const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    /**
     * [$_rmaItemCollectionFactory description]
     * @var [type]
     */
    protected $_rmaItemCollectionFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $adminHelper;

    /**
     * @var \Webkul\Rmasystem\Api\ReasonRepositoryInterface
     */
    protected $reasonRepository;

    /**
     * @var \Magento\Sales\Model\Order\ItemRepository
     */
    protected $orderItemRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Webkul\Rmasystem\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory $rmaItemCollectionFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param OrderRepository $orderRepository
     * @param \Magento\Sales\Model\Order\ItemRepository $orderItemRepository
     * @param \Webkul\Rmasystem\Api\ReasonRepositoryInterface $reasonRepository
     * @param \Magento\Backend\Helper\Data $adminHelper
     * @param \Webkul\Rmasystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory $rmaItemCollectionFactory,
        \Magento\Framework\Escaper $escaper,
        OrderRepository $orderRepository,
        \Magento\Sales\Model\Order\ItemRepository $orderItemRepository,
        \Webkul\Rmasystem\Api\ReasonRepositoryInterface $reasonRepository,
        \Magento\Backend\Helper\Data $adminHelper,
        \Webkul\Rmasystem\Helper\Data $helper
    ) {
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_escaper = $escaper;
        $this->_rmaItemCollectionFactory = $rmaItemCollectionFactory;
        $this->reasonRepository = $reasonRepository;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->urlBuilder = $urlBuilder;
        $this->adminHelper = $adminHelper;
        $this->helper = $helper;
    }

    /**
     * Post user question
     *
     * @return void
     * @throws \Exception
     */
    public function sendNewRmaEmail($post, $rma)
    {
        $this->_inlineTranslation->suspend();
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $store = $this->storeManager->getStore();
            $adminName = $this->scopeConfig->getValue("rmasystem/parameter/admin_name", $storeScope);
            $adminEmail = $this->scopeConfig->getValue("rmasystem/parameter/admin_email", $storeScope);
            $customerName = "";
            $customerEmail = "";
            if ($rma->getGroup() == "customer") {
                $customerName = $this->_customerSession->getCustomer()->getName();
                $customerEmail = $this->_customerSession->getCustomer()->getEmail();
            } else {
                $order = $this->orderRepository->get($rma->getOrderId());
                $customerName = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
                $customerEmail = $order->getCustomerEmail();
            }
            $templateVariable = [];
            $templateVariable["rma_id"] = $rma->getIncrementId()."-".$rma->getRmaId();
            $templateVariable["order_id"] = $rma->getIncrementId();
            if ($rma->getCustomerDeliveryStatus() == 1) {
                if ($rma->getPackageCondition() == 0) {
                    $templateVariable["package_condition"] = "Open";
                } else {
                    $templateVariable["package_condition"] = "Packed";
                }
            } else {
                $templateVariable["package_condition"] = "Not Delivered";
            }
            
            //Resolution Type
            if ($rma->getResolutionType() == 0) {
                $templateVariable["resolution_type"] = "Refund";
            } elseif ($rma->getResolutionType() == 1) {
                $templateVariable["resolution_type"] = "Exchange";
            } else {
                $templateVariable["resolution_type"] = "Cancel Items";
            }

            $templateVariable["additional_info"] = nl2br(strip_tags($rma->getAdditionalInfo()));
            if ($rma->getCustomerDeliveryStatus() == 1) {
                $deliveryStatus = "
                <tbody>
                    <tr>
                    <th colspan='2' align='left'
                    bgcolor='#EAEAEA'
                    style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'
                    >".
                    ('Customer Consignment Number').
                        " :
                    </th>
                    </tr>
                    <tr>
                    <td colspan='2'
                        valign='top'
                        style='font-size:12px;
                            padding:7px 9px 9px 9px;
                            border-left:1px solid #EAEAEA;
                            border-bottom:1px solid #EAEAEA;
                            border-right:1px solid #EAEAEA;'>".
                        $rma->getCustomerConsignmentNo().
                    "</td>
                    </tr>
                </tbody>";
                $templateVariable["delivery_status"] = $deliveryStatus;
            }
            //RMA items listing
            $rmaItems = $this->_rmaItemCollectionFactory->create()->addFieldToFilter("rma_id", $rma->getRmaId());

            $count = 1;
            $rmaItemHtml = "";
            foreach ($rmaItems as $item) {
                $mageItem = $this->orderItemRepository->get($item->getItemId());
                $rmaItemHtml .= "<tbody ";
                if ($count % 2 != 0) {
                    $rmaItemHtml .= "bgcolor='#F6F6F6'";
                }
                $rmaItemHtml .= ">
                <tr>
                    <td align='left'
                    valign='top'
                    style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'>
                    <strong style='font-size:11px'>".$mageItem->getName()."</strong>
                    </td>
                    <td align='left'
                    valign='top'
                    style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'>".
                    $mageItem->getSku().
                    "</td>
                    <td align='center'
                    valign='top'
                    style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'>".
                    $item->getQty().
                    "</td>
                    <td align='right'
                    valign='top'
                    style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'><span>".
                    $this->reasonRepository->getById($item->getReasonId())->getReason();
                $count++;
            }
            $templateVariable["items"] = $rmaItemHtml;
            $templateVariable["receiver_name"] = $customerName;
            $templateVariable["title"] = "Thanks for your RMA request, will contact you soon.";
            $templateVariable["rma_link_label"] = "Click here to view RMA :";
            $templateVariable["rma_link"] =$this->storeManager->getStore()
                                        ->getUrl('rmasystem/viewrma/index', ['id' => $rma->getRmaId()]).'';
            $sender = [
                'name' => $adminName,
                'email' => $adminEmail,
            ];

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('new_rma_email_template')
                ->setTemplateOptions(
                    [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($templateVariable)
                ->setFrom($sender)
                ->addTo($customerEmail, $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_inlineTranslation->resume();
        }
    }

    public function sendNewRmaEmailToAdmin($post, $rma)
    {
        $this->_inlineTranslation->suspend();
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $store = $this->storeManager->getStore();
            $adminName = 'Admin';
            $adminEmail = $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $customerName = $this->scopeConfig->getValue("rmasystem/parameter/admin_name", $storeScope);
            $customerEmail = $this->scopeConfig->getValue("rmasystem/parameter/admin_email", $storeScope);
            ;

            $templateVariable = [];
            $templateVariable["rma_id"] = $rma->getIncrementId()."-".$rma->getRmaId();
            $templateVariable["order_id"] = $rma->getIncrementId();
            
            if ($rma->getCustomerDeliveryStatus() == 1) {
                if ($rma->getPackageCondition() == 0) {
                    $templateVariable["package_condition"] = "Open";
                } else {
                    $templateVariable["package_condition"] = "Packed";
                }
            } else {
                $templateVariable["package_condition"] = "Not Delivered";
            }
            
            //Resolution Type
            if ($rma->getResolutionType() == 0) {
                $templateVariable["resolution_type"] = "Refund";
            } elseif ($rma->getResolutionType() == 1) {
                $templateVariable["resolution_type"] = "Exchange";
            } else {
                $templateVariable["resolution_type"] = "Cancel Items";
            }

            $templateVariable["additional_info"] = nl2br(strip_tags($rma->getAdditionalInfo()));
            if ($rma->getCustomerDeliveryStatus() == 1) {
                $deliveryStatus = "
                <tbody>
                    <tr>
                    <th colspan='2' align='left'
                    bgcolor='#EAEAEA'
                    style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'
                    >".
                    (__('Customer Consignment Number')).
                        " :
                    </th>
                    </tr>
                    <tr>
                    <td colspan='2'
                        valign='top'
                        style='font-size:12px;
                            padding:7px 9px 9px 9px;
                            border-left:1px solid #EAEAEA;
                            border-bottom:1px solid #EAEAEA;
                            border-right:1px solid #EAEAEA;'>".
                        $rma->getCustomerConsignmentNo().
                    "</td>
                    </tr>
                </tbody>";
                $templateVariable["delivery_status"] = $deliveryStatus;
            }
            //RMA items listing
            $rmaItems = $this->_rmaItemCollectionFactory->create()->addFieldToFilter("rma_id", $rma->getRmaId());

            $count = 1;
            $rmaItemHtml = "";
            foreach ($rmaItems as $item) {
                $mageItem = $this->orderItemRepository->get($item->getItemId());
                $rmaItemHtml .= "<tbody ";
                if ($count % 2 != 0) {
                    $rmaItemHtml .= "bgcolor='#F6F6F6'";
                }
                $rmaItemHtml .= ">
                <tr>
                    <td align='left'
                    valign='top'
                    style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'>
                    <strong style='font-size:11px'>".$mageItem->getName()."</strong>
                    </td>
                    <td align='left'
                    valign='top'
                    style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'>".
                    $mageItem->getSku().
                    "</td>
                    <td align='center'
                    valign='top'
                    style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'>".
                    $item->getQty().
                    "</td>
                    <td align='right'
                    valign='top'
                    style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'><span>".
                    $this->reasonRepository->getById($item->getReasonId())->getReason();
                $count++;
            }
            $templateVariable["items"] = $rmaItemHtml;
            $templateVariable["receiver_name"] = $customerName;
            $templateVariable["title"] = __("New Rma has been generated");
            $templateVariable["rma_link_label"] = __("Click here to view RMA :");
            $templateVariable["rma_link"] =$this->adminHelper->getUrl('rmasystem/allrma/index');
            $sender = [
                'name' => $adminName,
                'email' => $adminEmail,
            ];

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('new_rma_admin_email_template')
                ->setTemplateOptions(
                    [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($templateVariable)
                ->setFrom($sender)
                ->addTo($customerEmail, $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_inlineTranslation->resume();
        }
    }

    public function cancelRmaEmail($rma)
    {
        $this->_inlineTranslation->suspend();
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $adminName = $this->scopeConfig->getValue("rmasystem/parameter/admin_name", $storeScope);
            $adminEmail = $this->scopeConfig->getValue("rmasystem/parameter/admin_email", $storeScope);
            $customerName = "";
            $customerEmail = "";
            $order = $this->orderRepository->get($rma->getOrderId());
            $customerName = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
            $customerEmail = $order->getCustomerEmail();
            $templateVariable = [];
            $templateVariable["rma_id"] = $rma->getIncrementId()."-".$rma->getRmaId();
            $templateVariable["order_id"] = $rma->getIncrementId();
            $templateVariable["status"] = __("Cancelled");
            //For Customer
            $templateVariable["receiver_name"] = $customerName;
            $templateVariable["title"] = __("You have just cancelled your RMA request, Details are as follows.");
            $sender = [
                'name' => $adminName,
                'email' => $adminEmail,
            ];
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('cancel_rma_email_template')
                ->setTemplateOptions(
                    [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($templateVariable)
                ->setFrom($sender)
                ->addTo($customerEmail, $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->_inlineTranslation->resume();

            // For Admin
            $templateVariable["receiver_name"] = $adminName;
            $templateVariable["title"] = $_helper->__("One RMA request, Details are as follows");
            $sender = [
                'name' => $customerName,
                'email' => $customerEmail,
            ];
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('cancel_rma_email_template')
                ->setTemplateOptions(
                    [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($templateVariable)
                ->setFrom($sender)
                ->addTo($adminEmail, $adminName)
                ->getTransport();

            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_inlineTranslation->resume();
        }
    }
    public function newMessageEmail($post, $rma, $selfEmail, $fileName)
    {
        $filePath = $this->helper->getConversationDir($rma->getId());
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $adminName = $this->scopeConfig->getValue("rmasystem/parameter/admin_name", $storeScope);
        $adminEmail = $this->scopeConfig->getValue("rmasystem/parameter/admin_email", $storeScope);
        $order = $this->orderRepository->get($rma->getOrderId());
        $customerName = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
        $customerEmail = $order->getCustomerEmail();
        $templateVariable = [];
        $templateVariable["rma_id"] = $rma->getIncrementId()."-".$post["rma_id"];
        $templateVariable["order_id"] = $rma->getIncrementId();
        $templateVariable["message"] = nl2br(strip_tags($post["message"]));
        $templateVariable["receiver_name"] = $customerName;
        if ($selfEmail['check']) {
            $templateVariable["title"] = __("Your Message has been successfully saved for following RMA.");
        } else {
            $templateVariable["title"] = __("New Message has been appended to following RMA.");
        }
        $sender = [
            'name' => $adminName,
            'email' => $adminEmail,
        ];

        if (($selfEmail['area'] == 'frontend' && $selfEmail['check']) || $selfEmail['area'] == 'backend') {
            try {
                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('message_email_template')
                    ->setTemplateOptions(
                        [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars($templateVariable)
                    ->setFrom($sender)
                    ->addTo($customerEmail, $customerName)
                    ->addAttachment(($filePath.$fileName), $fileName)
                    ->getTransport();

                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->_inlineTranslation->resume();
            }
        }

        $templateVariable["receiver_name"] = $adminName;
        if ($selfEmail) {
            $templateVariable["title"] = "Your Message has been successfully saved for following RMA.";
        } else {
            $templateVariable["title"] = "New Message has been appended to following RMA.";
        }
        // For Admin
        $templateVariable["receiver_name"] = $adminName;
        $templateVariable["title"] = "RMA Updated details are as follows.";
        $sender = [
            'name' => $customerName,
            'email' => $customerEmail,
        ];
        if (($selfEmail['area'] == 'backend' && $selfEmail['check']) || $selfEmail['area'] == 'frontend') {
            try {
                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('message_email_template')
                    ->setTemplateOptions(
                        [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars($templateVariable)
                    ->setFrom($sender)
                    ->addTo($adminEmail, $adminName)
                    ->addAttachment(($filePath.$fileName), $fileName)
                    ->getTransport();

                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->_inlineTranslation->resume();
            }
        }
    }

    public function updateRmaEmail($post, $rma, $statusFlag, $deliveryFlag, $selfEmail, $fileName)
    {
        $filePath = $this->helper->getConversationDir($rma->getId());
        $temp_path = '';
        if (isset($attachment['tmp_name'])) {
            $temp_path = $attachment['tmp_name'];
        }
        $this->_inlineTranslation->suspend();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $adminName = $this->scopeConfig->getValue("rmasystem/parameter/admin_name", $storeScope);
        $adminEmail = $this->scopeConfig->getValue("rmasystem/parameter/admin_email", $storeScope);
        $customerName = "";
        $customerEmail = "";
        if ($rma->getGroup() == "customer") {
            $customerName = $this->_customerSession->getCustomer()->getName();
            $customerEmail = $this->_customerSession->getCustomer()->getEmail();
        } else {
            $order = $this->orderRepository->get($rma->getOrderId());
            $customerName = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
            $customerEmail = $order->getCustomerEmail();
        }
        $statusData = "";
        if ($statusFlag == true && $deliveryFlag == true) {
            $rmaStatus = "";
            $rmaStatus = $this->helper->getAdminStatusTitle($rma->getAdminStatus(), $rma->getResolutionType());

            $statusData .= "
            <tbody>
                <tr>
                    <th align='left'
                    bgcolor='#EAEAEA'
                    style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".
                    __('Customer Consignment Number').
                    " :
                    </th>
                    <th align='left'
                    bgcolor='#EAEAEA'
                    style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".
                    __('Status').
                    " :
                    </th>
                </tr>
                <tr>
                <td
                valign='top'
                style='font-size:12px;padding:7px 9px 9px 9px;
                    border-left:1px solid #EAEAEA;
                    border-bottom:1px solid #EAEAEA;
                    border-right:1px solid #EAEAEA;'>".
                $rma->getCustomerConsignmentNo().
                "</td>
                <td valign='top'
                style='font-size:12px;padding:7px 9px 9px 9px;
                    border-left:1px solid #EAEAEA;
                    border-bottom:1px solid #EAEAEA;
                    border-right:1px solid #EAEAEA;'>".
                    $rmaStatus.
                "</td>
                </tr>
            </tbody>";
        } else {
            if ($statusFlag == true) {
                $rmaStatus = "";
                $rmaStatus = $this->helper->getAdminStatusTitle($rma->getAdminStatus(), $rma->getResolutionType());
                $statusData .= "
                <tbody>
                    <tr>
                        <th colspan='2'
                        align='left'
                        bgcolor='#EAEAEA'
                        style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".
                        __('Status').
                        " :</th>
                    </tr>
                    <tr>
                        <td colspan='2'
                        valign='top'
                        style='font-size:12px;
                            padding:7px 9px 9px 9px;
                            border-left:1px solid #EAEAEA;
                            border-bottom:1px solid #EAEAEA;
                            border-right:1px solid #EAEAEA;'>".
                            $rmaStatus.
                        "</td>
                    </tr>
                </tbody>";
            } else {
                if ($deliveryFlag == true) {
                    $statusData .=
                    "<tbody>
                        <tr>
                            <th
                            colspan='2'
                            align='left'
                            bgcolor='#EAEAEA'
                            style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".
                            __('Customer Consignment Number').
                            " :
                            </th>
                        </tr>
                        <tr>
                            <td colspan='2'
                            valign='top'
                            style='font-size:12px;
                                padding:7px 9px 9px 9px;
                                border-left:1px solid #EAEAEA;
                                border-bottom:1px solid #EAEAEA;
                                border-right:1px solid #EAEAEA;'>".
                                $rma->getCustomerConsignmentNo().
                            "</td>
                        </tr>
                    </tbody>";
                }
            }
        }
        $templateVariable = [];
        $templateVariable["rma_id"] = $rma->getIncrementId()."-".$post["rma_id"];
        $templateVariable["order_id"] = $rma->getIncrementId();
        $templateVariable["message"] = nl2br(strip_tags($post["message"]));
        $templateVariable["status_data"] = $statusData;

        $templateVariable["receiver_name"] = $customerName;
        $templateVariable["title"] = "Your RMA Updated successfully details are as follows.";

        $sender = [
            'name' => $adminName,
            'email' => $adminEmail,
        ];
        if (($selfEmail['area'] == 'frontend' && $selfEmail['check']) || $selfEmail['area'] == 'backend') {
            try {
                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('update_rma_email_template')
                    ->setTemplateOptions(
                        [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars($templateVariable)
                    ->setFrom($sender)
                    ->addTo($customerEmail, $customerName)
                    ->addAttachment(($filePath.$fileName), $fileName)
                    ->getTransport();

                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->_inlineTranslation->resume();
            }
        }

        if (($selfEmail['area'] == 'backend' && $selfEmail['check']) || $selfEmail['area'] == 'frontend') {
            // For Admin
            $templateVariable["receiver_name"] = $adminName;
            $templateVariable["title"] = "RMA Updated details are as follows.";
            $sender = [
                'name' => $customerName,
                'email' => $customerEmail,
            ];
            try {
                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('update_rma_email_template')
                    ->setTemplateOptions(
                        [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars($templateVariable)
                    ->setFrom($sender)
                    ->addAttachment(($filePath.$fileName), $fileName)
                    ->getTransport();

                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->_inlineTranslation->resume();
            }
        }
    }
}
