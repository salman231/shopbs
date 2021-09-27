<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\GiftCard\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class IsExpire extends Column
{
    /**
     * @var \Webkul\GiftCard\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Webkul\GiftCard\Model\GiftUserFactory
     */
    protected $_giftuser;

    /**
     * Constructor.
     *
     * @param ContextInterface                         $context
     * @param UiComponentFactory                       $uiComponentFactory
     * @param \Webkul\GiftCard\Helper\Data             $helperData
     * @param \Webkul\GiftCard\Model\GiftUserFactory   $giftUser
     * @param array                                    $components
     * @param array                                    $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Webkul\GiftCard\Helper\Data $helperData,
        \Webkul\GiftCard\Model\GiftUserFactory $giftUser,
        \Webkul\GiftCard\Model\GiftDetailFactory $giftDetail,
        array $components = [],
        array $data = []
    ) {
        $this->_helperData = $helperData;
        $this->_giftuser = $giftUser;
        $this->giftDetail = $giftDetail;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $websiteId = false;
                try {
                    $websiteId = $this->giftDetail->create()->load($item['giftcodeid'])->getWebsiteId();
                } catch (\Exception $e) {
                    $e->getMessage();
                }
                $isExpire = $this->_helperData->checkExpirationOfGiftCard(
                    $item['alloted'],
                    $item['duration'],
                    $websiteId
                );
                if ($isExpire) {
                    $giftModel = $this->_giftuser->create()->load($item['giftuserid']);
                    if ($giftModel->getId() && !$giftModel->getIsExpire()) {
                        $giftModel->setIsExpire(1);
                        $giftModel->save();
                    }
                }

                if ($isExpire) {
                    $item['is_expire'] = '<div style="background:#f9d4d4;border:1px solid;
                    border-color:#e22626;padding: 0 7px;text-align:center;
                    text-transform: uppercase;color:#e22626;font-weight:bold;"
                     title="Gift Card is expire">'.__('Expired').'</div>';
                } else {
                    $item['is_expire'] = '<div style="background:#d0e5a9;border:1px solid;
                    border-color:#5b8116;padding: 0 7px;text-align:center;
                    text-transform: uppercase;color:#185b00;font-weight:bold;" 
                    title="Gift Card is active">'.__('Active').'</div>';
                }
            }
        }
        return $dataSource;
    }
}
