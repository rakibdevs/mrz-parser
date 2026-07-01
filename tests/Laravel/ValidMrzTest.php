<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Laravel\Rules\ValidMrz;

/**
 * Invoke a rule and capture whether it failed (avoids booting a full Laravel app).
 */
function runRule(ValidMrz $rule, mixed $value): ?string
{
    $message = null;
    $rule->validate('mrz', $value, function (string $error) use (&$message) {
        $message = $error;
    });

    return $message;
}

it('passes for a valid MRZ', function () {
    expect(runRule(new ValidMrz(), MRZ_PASSPORT))->toBeNull();
});

it('fails for non-MRZ input', function (mixed $value) {
    expect(runRule(new ValidMrz(), $value))->not->toBeNull();
})->with([
    'garbage' => ['not an mrz'],
    'empty' => [''],
    'non-string' => [12345],
]);

it('fails a bad check digit only in strict mode', function () {
    $bad = "P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\n"
        . 'L898902C35UTO7408122F1204159ZE184226B<<<<<10';

    expect(runRule(new ValidMrz(), $bad))->toBeNull()
        ->and(runRule(new ValidMrz(strict: true), $bad))->not->toBeNull();
});
