<?php

namespace Bb\RemovePostcode\Plugin\Customer\Address;

class CityValidatorAddress
{
    public function afterValidate($subject, $result)
    {
        if(count($result) == 1){
            if(!\Zend_Validate::is($subject->getCitycode(),'NotEmpty')){
                return true;
            }
        }
    }


}