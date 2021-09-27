<?php
namespace Webkul\Rmasystem\Ui\Component\Listing\Column;

/**
 * Class Price for currency
 */
class Price extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as & $items) {
                $currencyCode = isset($items['base_currency_code']) ? $items['base_currency_code'] : null;
                $items[$this->getData('name')] = $this->priceFormatter->format(
                    $items[$this->getData('name')],
                    false,
                    null,
                    null,
                    $currencyCode
                );
            }
        }

        return $dataSource;
    }
}
