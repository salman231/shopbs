<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Block\Adminhtml\Dashboard;

class OrderGraph extends \Magento\Backend\Block\Dashboard\Tab\Orders
{
    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy
     */
    protected $deliveryboy;
    
    /**
     * @var string
     */
    protected $_template = 'dashboard/graph.phtml';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResourceCollection;
    
    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $_helperdata;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Helper\Dashboard\Order $dataHelper
     * @param \Magento\Backend\Helper\Dashboard\Data $dashboardData
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Helper\Dashboard\Order $dataHelper,
        \Magento\Backend\Helper\Dashboard\Data $dashboardData,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollection,
        array $data = []
    ) {
        $this->deliveryboy = $deliveryboy;
        $this->_helperdata = $helper;
        $this->resource = $resource;
        $this->jsonHelper = $jsonHelper;
        $this->deliveryboyOrderResourceCollection = $deliveryboyOrderResourceCollection;
        parent::__construct($context, $collectionFactory, $dashboardData, $dataHelper, $data);
    }

    /**
     * @return \Magento\Backend\Helper\Dashboard\Data
     */
    public function getDashboardDataHelper()
    {
        return $this->_dashboardData;
    }
    
    /**
     * @param bool $directUrl
     * @return string
     */
    public function getChartUrl($directUrl = true)
    {
        $params = [
            'cht' => 'lc',
            'chls' => '7',
            'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
            'chm'  => 'B,f4d4b2,0,0,0',
            'chco' => 'db4814',
            'chxs' => '0,0,11|1,0,11',
            'chma' => '15,15,15,15'
        ];
        $this->_allSeries = $this->getRowsData($this->_dataRows);

        foreach ($this->_axisMaps as $axis => $attr) {
            $this->setAxisLabels($axis, $this->getRowsData($attr, true));
        }

        $timezoneLocal = $this->_localeDate->getConfigTimezone();
        /** @var \DateTime $dateStart */
        /** @var \DateTime $dateEnd */
        list($dateStart, $dateEnd) = $this->_collectionFactory->create()->getDateRange(
            $this->getDataHelper()->getParam('period'),
            '',
            '',
            true
        );
        $dateStart->setTimezone(new \DateTimeZone($timezoneLocal));
        $dateEnd->setTimezone(new \DateTimeZone($timezoneLocal));

        if ($this->getDataHelper()->getParam('period') == '24h') {
            $dateEnd->modify('-1 hour');
        } else {
            $dateEnd->setTime(23, 59, 59);
            $dateStart->setTime(0, 0, 0);
        }

        $dates = [];
        $datas = [];

        while ($dateStart <= $dateEnd) {
            switch ($this->getDataHelper()->getParam('period')) {
                case '7d':
                case '1m':
                    $d = $dateStart->format('Y-m-d');
                    $dateStart->modify('+1 day');
                    break;
                case '1y':
                case '2y':
                    $d = $dateStart->format('Y-m');
                    $dateStart->modify('+1 month');
                    break;
                default:
                    $d = $dateStart->format('Y-m-d H:00');
                    $dateStart->modify('+1 hour');
            }
            foreach ($this->getAllSeries() as $index => $serie) {
                if (in_array($d, $this->_axisLabels['x'])) {
                    $datas[$index][] = (double)array_shift($this->_allSeries[$index]);
                } else {
                    $datas[$index][] = 0;
                }
            }
            $dates[] = $d;
        }

        /**
         * Setting skip step
         */
        if (count($dates) > 8 && count($dates) < 15) {
            $c = 1;
        } else {
            if (count($dates) >= 15) {
                $c = 2;
            } else {
                $c = 0;
            }
        }
        /**
         * Skipping some x labels for good reading
         */
        $i = 0;
        foreach ($dates as $k => $d) {
            if ($i == $c) {
                $dates[$k] = $d;
                $i = 0;
            } else {
                $dates[$k] = '';
                $i++;
            }
        }

        $this->_axisLabels['x'] = $dates;
        $this->_allSeries = $datas;

        //Google encoding values
        if ($this->_encoding == "s") {
            // simple encoding
            $params['chd'] = "s:";
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "_";
        } else {
            // extended encoding
            $params['chd'] = "e:";
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "__";
        }

        // process each string in the array, and find the max length
        $localmaxvalue = [0];
        $localminvalue = [0];
        foreach ($this->getAllSeries() as $index => $serie) {
            $localmaxvalue[$index] = max($serie);
            $localminvalue[$index] = min($serie);
        }

        $maxvalue = max($localmaxvalue);
        $minvalue = min($localminvalue);

        // default values
        $yrange = 0;
        $yLabels = [];
        $miny = 0;
        $maxy = 0;
        $yorigin = 0;

        if ($minvalue >= 0 && $maxvalue >= 0) {
            if ($maxvalue > 10) {
                $p = pow(10, $this->_getPow($maxvalue));
                $maxy = ceil($maxvalue / $p) * $p;
                $yLabels = range($miny, $maxy, $p);
            } else {
                $maxy = ceil($maxvalue + 1);
                $yLabels = range($miny, $maxy, 1);
            }
            $yrange = $maxy;
            $yorigin = 0;
        }

        $chartdata = [];

        foreach ($this->getAllSeries() as $index => $serie) {
            $thisdataarray = $serie;
            $thisdataarrayLength = count($thisdataarray);
            if ($this->_encoding == "s") {
                // SIMPLE ENCODING
                for ($j = 0; $j < $thisdataarrayLength; $j++) {
                    $currentvalue = $thisdataarray[$j];
                    if (is_numeric($currentvalue)) {
                        $ylocation = round(
                            (strlen($this->_simpleEncoding) - 1) * ($yorigin + $currentvalue) / $yrange
                        );
                        $chartdata[] = substr($this->_simpleEncoding, $ylocation, 1) . $dataDelimiter;
                    } else {
                        $chartdata[] = $dataMissing . $dataDelimiter;
                    }
                }
            } else {
                // EXTENDED ENCODING
                for ($j = 0; $j < $thisdataarrayLength; $j++) {
                    $currentvalue = $thisdataarray[$j];
                    if (is_numeric($currentvalue)) {
                        if ($yrange) {
                            $ylocation = 4095 * ($yorigin + $currentvalue) / $yrange;
                        } else {
                            $ylocation = 0;
                        }
                        $firstchar = floor($ylocation / 64);
                        $secondchar = $ylocation % 64;
                        $mappedchar = substr(
                            $this->_extendedEncoding,
                            $firstchar,
                            1
                        ) . substr(
                            $this->_extendedEncoding,
                            $secondchar,
                            1
                        );
                        $chartdata[] = $mappedchar . $dataDelimiter;
                    } else {
                        $chartdata[] = $dataMissing . $dataDelimiter;
                    }
                }
            }
            $chartdata[] = $dataSetdelimiter;
        }
        $buffer = implode('', $chartdata);

        $buffer = rtrim($buffer, $dataSetdelimiter);
        $buffer = rtrim($buffer, $dataDelimiter);
        $buffer = str_replace($dataDelimiter . $dataSetdelimiter, $dataSetdelimiter, $buffer);

        $params['chd'] .= $buffer;

        $valueBuffer = [];

        if (count($this->_axisLabels) > 0) {
            $params['chxt'] = implode(',', array_keys($this->_axisLabels));
            $indexid = 0;
            foreach ($this->_axisLabels as $idx => $labels) {
                if ($idx == 'x') {
                    /**
                     * Format date
                     */
                    $periodRequestParam = $this->getDataHelper()->getParam('period');
                    foreach ($this->_axisLabels[$idx] as $_index => $_label) {
                        if ($_label != '') {
                            $period = new \DateTime($_label, new \DateTimeZone($timezoneLocal));
                            $this->_axisLabels[$idx][$_index] = $this->formatAxisLabel(
                                $periodRequestParam,
                                $period,
                                $_label
                            );
                        } else {
                            $this->_axisLabels[$idx][$_index] = '';
                        }
                    }

                    $tmpstring = implode('|', $this->_axisLabels[$idx]);

                    $valueBuffer[] = $indexid . ":|" . $tmpstring;
                } elseif ($idx == 'y') {
                    $valueBuffer[] = $indexid . ":|" . implode('|', $yLabels);
                }
                $indexid++;
            }
            $params['chxl'] = implode('|', $valueBuffer);
        }

        // chart size
        $params['chs'] = $this->getWidth() . 'x' . $this->getHeight();

        // return the encoded data
        if ($directUrl) {
            $p = [];
            foreach ($params as $name => $value) {
                $p[] = $name . '=' . urlencode($value);
            }
            return $this->getUrl('adminhtml/*/tunnel', ['_query' => $params]);
        } else {
            $gaData = urlencode(base64_encode(json_encode($params)));
            $gaHash = $this->_dashboardData->getChartDataHash($gaData);
            $params = ['ga' => $gaData, 'h' => $gaHash];
            return $this->getUrl('adminhtml/*/tunnel', ['_query' => $params]);
        }
    }

    /**
     * Format order graph x axis date label
     *
     * @param string $periodRequestParameter
     * @param \DateTime $labelPeriod
     * @param string $label
     * @return string
     */
    protected function formatAxisLabel($periodRequestParameter, $labelPeriod, $label)
    {
        switch ($periodRequestParameter) {
            case '24h':
                return $this->_localeDate->formatDateTime(
                    $labelPeriod->setTime($labelPeriod->format('H'), 0, 0),
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::SHORT
                );
            case '7d':
            case '1m':
                return $this->_localeDate->formatDateTime(
                    $labelPeriod,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            case '1y':
            case '2y':
                return date('m/Y', strtotime($label));
        }
    }
}
