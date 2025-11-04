<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTelephoneSenegal implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Format téléphone Sénégal : +221XXXXXXXXX ou 77XXXXXXX ou 78XXXXXXX ou 76XXXXXXX ou 70XXXXXXX
        if (!preg_match('/^(\+221|221)?(76|77|78|70)\d{7}$/', $value)) {
            $fail('Le numéro de téléphone doit être au format sénégalais (ex: +221771234567 ou 771234567). Le numéro fourni : ' . $value);
        }
    }
}
