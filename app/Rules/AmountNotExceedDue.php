<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AmountNotExceedDue implements ValidationRule
{
    protected $dueAmount;

    /**
     * Create a new rule instance.
     *
     * @param  float  $dueAmount
     * @return void
     */
    public function __construct($dueAmount)
    {
        $this->dueAmount = $dueAmount;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value > $this->dueAmount) {
            $fail('The payment amount should not exceed the due amount.');
        }
    }
}
