<?php

namespace Bb\RemovePostcode\Plugin\Customer\Address;

class CityValidator
{
    public function afterIsValid($subject)
    {
        return true;
    }

}