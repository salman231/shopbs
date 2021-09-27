<?php
/**
 * Dart Productkeys Product Attributes Modifier
 *
 * @package        Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

class GeneralConfigDependents extends AbstractModifier
{
    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    private $arrayManager;

    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    public function modifyMeta(array $meta)
    {
        $meta = $this->customizeFieldSub($meta);
 
        return $meta;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    protected function customizeFieldSub(array $meta)
    {
        $disable_attr = ['productkey_issue_invoice', 'productkey_updatestock', 'productkey_type',
        'productkey_not_available', 'productkey_send_email', 'productkey_email_template', 'productkey_low_warning',
        'productkey_warning_template', 'productkey_warning_level', 'productkey_warning_email'];

        foreach ($disable_attr as $attr) {
            $weightPath = $this->arrayManager->findPath($attr, $meta, null, 'children');
            if ($weightPath) {
                $meta = $this->arrayManager->merge(
                    $weightPath . static::META_CONFIG_PATH,
                    $meta,
                    [
                        'dataScope' => $attr,
                        'validation' => [
                            'required-entry' => false,
                            'validate-zero-or-greater' => false
                        ],
                        'additionalClasses' => 'generalconfig_attributes'
                    ]
                );
            }
        }

        return $meta;
    }
}
