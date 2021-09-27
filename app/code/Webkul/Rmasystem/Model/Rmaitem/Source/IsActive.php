<?php
namespace Webkul\Rmasystem\Model\Rmaitem\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Rmasystem\Model\Rmaitem
     */
    protected $reason;

    /**
     * Constructor
     *
     * @param \Webkul\Rmasystem\Model\Rmaitem $reason
     */
    public function __construct(\Webkul\Rmasystem\Model\Rmaitem $reason)
    {
        $this->reason = $reason;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->reason->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
