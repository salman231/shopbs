<?php
namespace Magemonkey\Chargeanywhere\Plugin;

class SetbuttonBefore
{

	public function __construct(
    \Magento\Framework\UrlInterface $urlBuilder
	) {
	    $this->urlBuilder = $urlBuilder;
	}

    public function beforeGetOrderId(\Magento\Sales\Block\Adminhtml\Order\View $subject){
		$captureurl = $this->urlBuilder->getUrl('chargeanywhere/order/capture/', ['order_id' => $subject->getOrder()->getId()]);
        if($subject->getOrder()->canInvoice()){
        $subject->addButton(
                'capture_amt',
                [
                	'label' => __('Capture Amt.'), 
                	'onclick' => 'setLocation(\'' . $captureurl.'\')',
                	'class' => 'capture_amt'
                ]
            );
        }
        return null;
    }
}