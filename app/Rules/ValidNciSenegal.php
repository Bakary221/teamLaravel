<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNciSenegal implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Format NCI Sénégal : Lettres + Chiffres (ex: AB123456789)
        if (!preg_match('/^[A-Z]{2}\d{9}$/', $value)) {
            $fail('Le numéro NCI doit être au format : 2 lettres majuscules suivies de 9 chiffres (ex: AB123456789).');
        }
    }
}
