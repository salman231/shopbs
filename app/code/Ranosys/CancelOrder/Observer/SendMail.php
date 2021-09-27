<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ranosys\CancelOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Send email to admin when user cancel an order
 */
class SendMail implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'cancel_order_setting/general/custom_email_template';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder,
     * @param \Ranosys\CancelOrder\Helper\Data                   $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Ranosys\CancelOrder\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }
    
    /**
     * customer register event handler
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
        'store' => $this->storeManager->getStore()->getId()];
        $store = $this->storeManager->getStore();
        $templateVars = [
        'store' => $store,
        'order' => $order
        ];

        $from = $this->helper->getSender();
        $to = $this->helper->getReceiver();
        $template = $this->helper->getTemplate();
        $value = trim($to, " ");
        $to = explode(",", $to);
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder->setTemplateIdentifier($template)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();

        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
