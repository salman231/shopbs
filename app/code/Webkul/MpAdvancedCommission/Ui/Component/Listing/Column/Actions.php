<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Ui\Component\Listing\Column;
 
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
 
class Actions extends Column
{
    /** Url path */
    const EMPLOYEE_URL_PATH_EDIT = 'mpadvancedcommission/commissionrules/edit';
    const EMPLOYEE_URL_PATH_DELETE = 'mpadvancedcommission/commissionrules/delete';
 
    /** @var UrlInterface */
    protected $_urlBuilder;
 
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['rule_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            self::EMPLOYEE_URL_PATH_EDIT,
                            ['rule_id' => $item['rule_id']]
                        ),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            self::EMPLOYEE_URL_PATH_DELETE,
                            ['rule_id' => $item['rule_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete Commission Rule'),
                            'message' => __(
                                'Are you sure you want to delete this commission rule?'
                            )
                        ]
                    ];
                }
            }
        }
 
        return $dataSource;
    }
}
