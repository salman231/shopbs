<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Block\Adminhtml\Shipping\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,

        array $data = []
    ) {
        $this->setId('shippinh_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Shipping Methods View'));

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

}