<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Template;

class ExampleForm
{
    private $repeatFields = [
        'group',
        'field',
        'option',
        'page',
        'dependency-group'
    ];

    private $requiredFields = [
        'title',
        'code',
        'store_id',
        'submit_button'
    ];

    /**
     * @var \Amasty\Customform\Model\FormFactory
     */
    private $form;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    private $formRepository;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Customform\Block\Widget\Form\Element\Country
     */
    private $countryData;

    public function __construct(
        \Amasty\Customform\Helper\Data $helper,
        \Amasty\Customform\Model\FormRepository $formRepository,
        \Amasty\Customform\Model\FormFactory $form,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Amasty\Customform\Block\Widget\Form\Element\Country $countryData
    ) {
        $this->form = $form;
        $this->date = $date;
        $this->formRepository = $formRepository;
        $this->helper = $helper;
        $this->countryData = $countryData;
    }

    public function createExampleForms()
    {
        $paths = $this->getXmlTemplatesPaths();
        foreach ($paths as $path) {
            $xmlDoc = simplexml_load_file($path);
            $templateData = $this->parseNode($xmlDoc);
            if (count($templateData) == 3) {
                $templateData = array_merge($templateData[0], $templateData[1], $templateData[2]);
            }
            $this->createForm($templateData);
        }
    }

    /**
     * @param \SimpleXMLElement $node
     * @param string $parentKeyNode
     *
     * @return array|string
     */
    private function parseNode($node, $parentKeyNode = '')
    {
        $data = [];
        foreach ($node as $keyNode => $childNode) {
            if (is_object($childNode)) {
                if (in_array($keyNode, $this->repeatFields) && $parentKeyNode != 'dependency-group') {
                    $data[] = $this->parseNode($childNode, $keyNode);
                } else {
                    $data[$keyNode] = $this->parseNode($childNode, $keyNode);
                    if ($keyNode == 'name' && $childNode == 'country') {
                        $data['values'] = $this->countryData->getCountryOptions();
                    }
                }
            }
        }

        if (count($node) == 0) {
            $data = (string)$node;
            if ($data == 'true') {
                $data = true;
            }
        }

        return $data;
    }

    /**
     * @param array $data
     */
    private function createForm($data)
    {
        if ($this->isTemplateDataValid($data)) {
            $form = $this->form->create();
            foreach ($data as $key => $item) {
                if (is_array($item)) {
                    $form->setData($key, $this->helper->encode($item));
                } else {
                    $form->setData($key, $item);
                }
            }
            $form->setCreatedAt($this->date->gmtDate());
            $this->formRepository->save($form);
        }
    }

    /**
     * @return array
     */
    private function getXmlTemplatesPaths()
    {
        $p = strrpos(__DIR__, DIRECTORY_SEPARATOR);
        $directoryPath = $p ? substr(__DIR__, 0, $p) : __DIR__;
        $directoryPath .= '/../etc/adminhtml/Example/';

        return glob($directoryPath . '*.xml');
    }

    /**
     * @param array $data
     * @return bool
     */
    private function isTemplateDataValid($data = [])
    {
        $result = true;
        foreach ($this->requiredFields as $fieldName) {
            if (!array_key_exists($fieldName, $data)) {
                $result = false;
            }
        }

        return $result;
    }
}
