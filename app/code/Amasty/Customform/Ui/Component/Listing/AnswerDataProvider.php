<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Ui\Component\Listing;

use Amasty\Customform\Model\Config\Source\Status;

class AnswerDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @param \Magento\Framework\Api\Search\SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(\Magento\Framework\Api\Search\SearchResultInterface $searchResult)
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        foreach ($searchResult->getItems() as $item) {
            $status = $item->getAdminResponseStatus();
            switch ($status) {
                case Status::PENDING:
                    $status = __('Pending');
                    break;
                case Status::ANSWERED:
                    $status = __('Answered');
            }
            $item->setData('admin_response_status', $status);
            $result['items'][] = $item->getData();
        }

        return $result;
    }
}
