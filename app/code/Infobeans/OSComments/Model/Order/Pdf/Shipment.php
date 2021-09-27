<?php

/**
 * InfoBeans Sales Shipment Comment Extension
 *
 * @category   Infobeans
 * @package    Infobeans_OSComments
 * @version    2.0.0
 *
 * Release with version 2.0.0
 *
 * @author     InfoBeans Technologies Limited http://www.infobeans.com/
 * @copyright  Copyright (c) 2017 InfoBeans Technologies Limited
 */

namespace Infobeans\OSComments\Model\Order\Pdf;

class Shipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    /**
     * Insert order to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    // @codingStandardsIgnoreLine
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;
        // @codingStandardsIgnoreStart
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        // @codingStandardsIgnoreEnd
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
        }
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top -= 15,
            'UTF-8'
        );

        $top -= 10;
        // @codingStandardsIgnoreStart
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        // @codingStandardsIgnoreEnd
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format(
                    $order->getShippingAddress(),
                    'pdf'
                )
            );
            $shippingMethod = $order->getShippingDescription();
        }
        // @codingStandardsIgnoreLine
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }
        // @codingStandardsIgnoreLine
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        // @codingStandardsIgnoreLine
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;
            // @codingStandardsIgnoreLine
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            // @codingStandardsIgnoreLine
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            // @codingStandardsIgnoreLine
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            // @codingStandardsIgnoreLine
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . __(
                'Total Shipping Charges'
            ) . " " . $order->formatPriceTxt(
                $order->getShippingAmount()
            ) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (!empty($tracks)) {
                // @codingStandardsIgnoreLine
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                // @codingStandardsIgnoreLine
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            $this->y = $currentY;
            // OS comments start
            $showComments = $this->_scopeConfig->getValue(
                'infobeans_oscomments/general/show_comment',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            );
            $oscEnabled = $this->_scopeConfig->getValue(
                'infobeans_oscomments/general/enable_module',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            );
            $shippingLabel = $this->_scopeConfig->getValue(
                'infobeans_oscomments/general/shipping_label',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            );
            $adminVisibility = $this->_scopeConfig->getValue(
                'infobeans_oscomments/general/enable_admin',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            );

            if ($oscEnabled && $adminVisibility) {
                if ($showComments || $order->getDeliveryComment()!="") {
                    $this->y -= 10;
                    // @codingStandardsIgnoreLine
                    $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                    $page->setLineWidth(0.5);
                    $page->drawRectangle(25, $this->y, 570, $this->y - 25);

                    $commentStartY = $this->y -= 15;
                    $this->_setFontBold($page, 12);
                    // @codingStandardsIgnoreLine
                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                    $page->drawText(__($shippingLabel), 35, $this->y, 'UTF-8');

                    $this->y -= 10;
                    // @codingStandardsIgnoreLine
                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

                    $this->_setFontRegular($page, 10);
                    // @codingStandardsIgnoreLine
                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

                    $this->y -= 15;
                    
                    if ($order->getDeliveryComment()=="") {
                        $newtext = wordwrap("N/A", 150, "\n");
                    } else {
                        $newtext = wordwrap($order->getDeliveryComment(), 150, "\n");
                    }
                    
                    $newTextArr = explode("\n", $newtext);
                    foreach ($newTextArr as $textLine) {
                        $page->drawText(__($textLine), 35, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }

                    $page->drawLine(25, $commentStartY, 25, $this->y);
                    $page->drawLine(25, $this->y, 570, $this->y);
                    $page->drawLine(570, $this->y, 570, $commentStartY);
                }
            }
            // infobeans os comments ends
            
            $this->y -= 15;
        }
    }
}
