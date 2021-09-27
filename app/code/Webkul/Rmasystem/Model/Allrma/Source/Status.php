<?php
namespace Webkul\Rmasystem\Model\Allrma\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status provides options array
 */
class Status implements OptionSourceInterface
{
    /**
     * @var Allrma
     */
    protected $allrma;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(\Webkul\Rmasystem\Model\Allrma $allrma)
    {
        $this->allrma = $allrma;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->allrma->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
