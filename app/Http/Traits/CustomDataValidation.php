<?php

namespace App\Http\Traits;

use Carbon\Carbon;

/**
 * Custom Data Validation Trait
 *
 * This class is dedicated towards validation adding any form of custom data validation necessary for the import
 *
 * Add validation methods and call them within the hasValidatedRequirements method
 */
trait CustomDataValidation
{

    /**
     * Process data validations
     *
     * @param array $data
     * @return bool
     */
    private static function hasValidatedRequirements(array $data): bool
    {
        return static::isAgeValid($data['date_of_birth']) || static::isCreditCardValid($data['credit_card']);
    }


    /**
     * Check if Date of Birth reach age requirements
     *
     * @param string|null $dateOfBirth
     * @return bool
     */
    private static function isAgeValid(?string $dateOfBirth): bool
    {
        // parse date of birth to age value
        $age = $dateOfBirth ? Carbon::parse(strtotime($dateOfBirth))->age : null;

        // if date of birth doesn't meet criteria return false
        if ($age && ($age < 18 || $age > 65)) {
            // skip data
            return false;
        }

        return true;
    }


    /**
     * Check if Credit Card meets requirements
     *
     * @param string $creditCardNumber
     * @return bool
     */
    private static function isCreditCardValid(array $creditCardNumber): bool
    {
        // check if credit card meets criteria
        return (bool) preg_match('/(.)\\1{2}/', $creditCardNumber['number']);
    }
}
