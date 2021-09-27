<?php

namespace Bb\RemovePostcode\Plugin\Customer\Address;

class CityValidatorValue
{
    public function afterValidateValue($subject)
    {
        return true;
    }

}