<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * Copyright В© 2018 Amasty. All rights reserved.
 */
namespace Amasty\Customform\Block\Widget\Form\Element;

class Country extends AbstractElement
{
    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    public function __construct(
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer
    ) {
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        parent::__construct();
    }

    public function _construct()
    {
        parent::_construct();
        $this->options['title'] = __('Country');
        $this->options['image_href'] = 'Amasty_Customform::images/dropdown.png';
    }

    public function generateContent()
    {
        return '<select><option value="">' . $this->getTestOptionText() . '</option></select>';
    }

    protected function getTestOptionText()
    {
        return __('--Select a country--');
    }

    /**
     * @param $type
     * @param $parentType
     * @return array
     */
    public function getElementData($type, $parentType)
    {
        $result = parent::getElementData($type, $parentType);
        $result['options'] = $this->getCountryOptions();

        return $result;
    }

    /**
     * @return array
     */
    public function getCountryOptions()
    {
        $data = [];

        $data[] = [
            'value'   => ' ',
            'label'   => ' ',
            'regions' => []
        ];

        $countries = $this->countryInformationAcquirer->getCountriesInfo();
        foreach ($countries as $country) {
            // Get regions for this country:
            $regions = [];

            if ($availableRegions = $country->getAvailableRegions()) {
                foreach ($availableRegions as $region) {
                    $regions[] = [
                        'id'   => $region->getId(),
                        'code' => $region->getCode(),
                        'name' => $region->getName()
                    ];
                }
            }

            // Add to data:
            $data[] = [
                'value'   => $country->getTwoLetterAbbreviation(),
                'label'   => __($country->getFullNameLocale())->render(),
                'regions' => $regions
            ];
        }

        return $data;
    }

}
