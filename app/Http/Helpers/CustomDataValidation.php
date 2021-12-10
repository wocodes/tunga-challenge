<?php

namespace App\Http\Helpers;

use Carbon\Carbon;

/**
 * Custom Data Validation Trait
 *
 * This class is dedicated towards validation adding any form of custom data validation necessary for the import
 *
 * Add validation methods and call them within the hasValidatedRequirements method
 */
class CustomDataValidation
{
    public array $data;

    /**
     * @var false
     */
    private bool $hasRequirements;


    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __call($name, $args) {
        if ($this->hasRequirements) {
            $this->hasRequirements = call_user_func_array($name, $args);
        }
        return $this;
    }

    /**
     * Process data validations
     *
     * @param array $data
     */
    public function checkRequirements()
    {
        $this->isAgeValid()->isCreditCardValid();

        return $this;
    }


    public function handle()
    {
        return $this->hasRequirements;
    }

    /**
     * Check if Date of Birth reach age requirements
     *
     */
    private function isAgeValid()
    {
        // parse date of birth to age value
        $age = $this->data['date_of_birth'] ? Carbon::parse(strtotime($this->data['date_of_birth']))->age : null;

        // if date of birth doesn't meet criteria return false
        if ($age && ($age < 18 || $age > 65)) {
            // skip data
            $this->hasRequirements = false;
        }

        $this->hasRequirements = true;

        return $this;
    }


    /**
     * Check if Credit Card meets requirements
     *
     */
    private function isCreditCardValid()
    {
        // check if credit card meets criteria
        $this->hasRequirements = preg_match('/(.)\\1{2}/', $this->data['credit_card']['number']);

        return $this;
    }
}
