<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Block;

class Detail extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\GiftCard\Model\GiftUserFactory $giftUserFactory,
        \Webkul\GiftCard\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->giftUserFactory = $giftUserFactory;
        $this->dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
        
        $collection = $this->giftUserFactory->create()
                                    ->getCollection();
        $collection->addFieldToFilter('email', ['eq'=>$this->customerSession->getCustomer()->getEmail()]);
        $param = $this->getRequest()->getParams();
        if (isset($param['ge']) && !empty($param['ge'])) {
            $collection->addFieldToFilter('from', ['eq'=>$param['ge']]);
        }
        if (isset($param['gc']) && !empty($param['gc'])) {
            $collection->addFieldToFilter('code', ['eq'=>$param['gc']]);
        }
        if (isset($param['gp']) && !empty($param['gp'])) {
            $collection->addFieldToFilter('amount', ['eq'=>$param['gp']]);
        }
        if (isset($param['gl']) && !empty($param['gl'])) {
            $collection->addFieldToFilter('remaining_amt', ['eq'=>$param['gl']]);
        }
        $this->setCollection($collection);
    }
 
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'giftcard.giftuser.list.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
        }
        return $this;
    }
 
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * @return string
     */
    public function getHelper()
    {
        return $this->dataHelper;
    }
}
