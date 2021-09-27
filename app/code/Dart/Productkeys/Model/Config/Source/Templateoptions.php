<?php
/**
 * Dart Productkeys Email Template List
 *
 * @package        Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Option\ArrayInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Templateoptions extends AbstractSource implements ArrayInterface
{
    private $collectionFactory;

    private $emailConfig;

    private $options;

    public function __construct(
        CollectionFactory $collectionFactory,
        Config $emailConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->emailConfig = $emailConfig;
        $this->scopeConfig = $scopeConfig;
    }

    public function getCustomTemplates()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $groups = [];

        foreach ($this->getCustomTemplates() as $template) {
            $groups[$template->getTemplateId()] = $template->getTemplateCode();
        }

        return $groups;
    }

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->getAttribute()) {
            if (!$this->options) {
                $default = 'productkeys_delivery';
                $this->options = 'productkeys_delivery';
            } else {
                $default = 'productkeys_warning';
                $this->options = '';
            }
        } else {
            $default = $this->getAttribute()->getDefaultValue();
        }

        if ($default == 'productkeys_delivery') {
            $label = 'Productkey Delivery';
        } else {
            $label = 'Productkey Warning';
        }

        $arr = $this->toArray();
        $result = [];
        $result[] = [
            'value' => $default,
            'label' => 'Default ('.$label.')'
        ];
        foreach ($arr as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
