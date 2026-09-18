<?php

namespace App\Rules;

use App\Support\MathCaptcha;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MathCaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! MathCaptcha::check($value)) {
            $fail('Ответ на контрольный вопрос неверный.');
        }
    }
}