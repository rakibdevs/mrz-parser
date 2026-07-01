<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Laravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Rakibdevs\MrzParser\MrzParser;

/**
 * Laravel validation rule: passes when the value is a parseable MRZ.
 *
 *   $request->validate(['mrz' => [new ValidMrz()]]);
 *   $request->validate(['mrz' => [new ValidMrz(strict: true)]]); // require check digits
 */
class ValidMrz implements ValidationRule
{
    public function __construct(private readonly bool $strict = false)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || MrzParser::tryParse($value, $this->strict) === null) {
            $fail('The :attribute is not a valid machine-readable zone.');
        }
    }
}
