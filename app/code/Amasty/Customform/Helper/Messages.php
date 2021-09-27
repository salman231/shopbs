<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */
namespace Amasty\Customform\Helper;

use Magento\Eav\Helper\Data as EavData;

class Messages extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $messages = [];

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var EavData
     */
    private $eavData;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        EavData $eavData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->eavData = $eavData;
        
        $this->messages = [
            'addOption' => __('Add Option +'),
            'allFieldsRemoved' => __('All fields were removed.'),
            'allowSelect' => __('Allow Select'),
            'allowMultipleFiles' => __('Allow users to upload multiple files'),
            'autocomplete' => __('Autocomplete'),
            'button' => __('Button'),
            'cannotBeEmpty' => __('This field cannot be empty'),
            'checkboxGroup' => __('Checkbox Group'),
            'checkbox' => __('Checkbox'),
            'checkboxes' => __('Checkboxes'),
            'className' => __('Class'),
            'clearAllMessage' => __('Are you sure you want to clear all fields?'),
            'clearAll' => __('Clear'),
            'close' => __('Close'),
            'content' => __('Content'),
            'copy' => __('Copy To Clipboard'),
            'copyButton' => __('&#43;'),
            'copyButtonTooltip' => __('Copy'),
            'dateField' => __('Date Field'),
            'description' => __('Tooltip'),
            'descriptionField' => __('Description'),
            'devMode' => __('Developer Mode'),
            'editNames' => __('Edit Names'),
            'editorTitle' => __('Form Elements'),
            'editXML' => __('Edit XML'),
            'enableOther' => __('Enable &quot;Other&quot;'),
            'enableOtherMsg' => __('Let users to enter an unlisted option'),
            'fieldDeleteWarning' => false,
            'fieldVars' => __('Field Variables'),
            'fieldNonEditable' => __('This field cannot be edited.'),
            'fieldRemoveWarning' => __('Are you sure you want to remove this field?'),
            'fileUpload' => __('File Upload'),
            'formUpdated' => __('Form Updated'),
            'getStarted' => __('Drag a field from the right to this area'),
            'googlemap' => __('Google Map'),
            'header' => __('Header'),
            'hide' => __('Edit'),
            'hidden' => __('Hidden Input'),
            'label' => __('Field Title'),
            'labelEmpty' => __('Field Label cannot be empty'),
            'limitRole' => __('Limit access to one or more of the following roles:'),
            'mandatory' => __('Mandatory'),
            'maxlength' => __('Max Length'),
            'minOptionMessage' => __('This field requires a minimum of 2 options'),
            'multipleFiles' => __('Multiple Files'),
            'allowed_extension' => __('Allowed Extensions'),
            'max_file_size' => __('Max. File Size (MB)'),
            'name' => __('Code'),
            'no' => __('No'),
            'number' => __('Number'),
            'off' => __('Off'),
            'on' => __('On'),
            'option' => __('Option'),
            'star' => __('Star'),
            'comment' => __('Comment'),
            'optional' => __('optional'),
            'optionLabelPlaceholder' => __('Label'),
            'optionValuePlaceholder' => __('Value'),
            'optionEmpty' => __('Option value required'),
            'other' => __('Other'),
            'paragraph' => __('Paragraph'),
            'placeholder' => __('Placeholder'),
            'placeholders' => [
                'value' => __('Value'),
                'label' => __('Label'),
                'text' => '',
                'textarea' => '',
                'email' => __('Enter you email'),
                'placeholder' => '',
                'className' => __('space separated classes'),
                'password' => __('Enter your password')
            ],
            'preview' => __('Preview'),
            'radioGroup' => __('Radio Group'),
            'radio' => __('Radio'),
            'rating' => __('Rating'),
            'removeMessage' => __('Remove Element'),
            'removeOption' => __('Remove Option'),
            'remove' => __('&#215;'),
            'required' => __('Required'),
            'richText' => __('Rich Text Editor'),
            'roles' => __('Access'),
            'save' => __('Save'),
            'selectOptions' => __('Options'),
            'select' => __('Select'),
            'selectColor' => __('Select Color'),
            'selectionsMessage' => __('Allow Multiple Selections'),
            'size' => __('Size'),
            'sizes' => [
                'xs' => __('Extra Small'),
                'sm' => __('Small'),
                'm'  => __('Default'),
                'lg' => __('Large')
            ],
            'layout' => __('Layout'),
            'layouts' => [
                ['value' => 'one', 'label' => __('One Column')],
                ['value' => 'two', 'label' => __('Two Column')],
                ['value' => 'three', 'label' => __('Three Column')]
            ],
            'style' => __('Custom Style'),
            'styles' => [
                'btn' => [
                    'default' => __('Default'),
                    'danger'  => __('Danger'),
                    'info'    => __('Info'),
                    'primary' => __('Primary'),
                    'success' => __('Success'),
                    'warning' => __('Warning')
                ]
            ],
            'validation' => __('Validation'),
            'subtype' => __('Type'),
            'text' => __('Text Field'),
            'textArea' => __('Text Area'),
            'toggle' => __('Toggle'),
            'warning' => __('Warning!'),
            'value' => __('Default Value'),
            'viewJSON' => __('{  }'),
            'viewXML' => __('&lt;/&gt;'),
            'yes' => __('Yes'),
            'dependencyTitle' => __('Dependency')
        ];
    }

    public function getMessages()
    {
        $this->messages['validations'] = $this->eavData->getFrontendClasses(null);
        if (isset($this->messages['validations'][0]['value']) && !$this->messages['validations'][0]['value']) {
            $this->messages['validations'][0]['value'] = ' ';
        }

        return $this->jsonEncoder->encode($this->messages);
    }
}
