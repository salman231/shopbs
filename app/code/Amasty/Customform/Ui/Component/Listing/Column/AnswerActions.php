<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Ui\Component\Listing\Column;

class AnswerActions extends Actions
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_customform/answer/edit',
                        [
                            'id' => $item['answer_id']
                        ]
                    ),
                    'label' => __('View'),
                    'hidden' => false,
                ];
            }
        }
        return $dataSource;
    }
}
