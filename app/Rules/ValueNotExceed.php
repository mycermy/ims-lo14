<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValueNotExceed implements ValidationRule
{
    protected $compareValue;
    protected $errorMessage;

    /**
     * Create a new rule instance.
     *
     * @param  float  $compareValue
     * @param  string  $errorMessage
     * @return void
     */
    public function __construct($compareValue, $errorMessage)
    {
        $this->compareValue = $compareValue;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value > $this->compareValue) {
            $fail($this->errorMessage);
        }
    }
}

