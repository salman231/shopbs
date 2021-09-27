<?php
namespace Webkul\Rmasystem\Model\Reason\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Rmasystem\Model\Reason
     */
    protected $reason;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct(\Webkul\Rmasystem\Model\Reason $reason)
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
