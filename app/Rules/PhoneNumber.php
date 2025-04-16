<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove any non-digit characters except plus sign
        $cleanedValue = preg_replace('/[^\d+]/', '', $value);
        
        // Ensure it starts with a plus sign
        if (!str_starts_with($cleanedValue, '+')) {
            $cleanedValue = '+' . $cleanedValue;
        }
        
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneUtil->parse($cleanedValue, null);
            
            if (!$phoneUtil->isValidNumber($phoneNumber)) {
                $fail('The phone number format is invalid. Please use international format (e.g., +1234567890).');
            }
        } catch (NumberParseException $e) {
            $fail('Phone number parsing failed: ' . $e->getMessage());
        }
    }
}