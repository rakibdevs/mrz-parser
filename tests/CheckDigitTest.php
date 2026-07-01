<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Support\CheckDigit;

it('computes the ICAO 7-3-1 check digit', function (string $value, int $expected) {
    expect(CheckDigit::calculate($value))->toBe($expected);
})->with([
    'document number' => ['L898902C3', 6],
    'date of birth' => ['740812', 2],
    'date of expiry' => ['120415', 9],
    'letters and filler' => ['ZE184226B<<<<<', 1],
]);

it('verifies a field against its check digit', function () {
    expect(CheckDigit::verify('740812', '2'))->toBeTrue()
        ->and(CheckDigit::verify('740812', '3'))->toBeFalse();
});

it('accepts a filler check digit only for a filler field', function () {
    expect(CheckDigit::verify('<<<<<<', '<'))->toBeTrue()
        ->and(CheckDigit::verify('L898902C3', '<'))->toBeFalse();
});

it('rejects a non-digit expected check digit', function () {
    expect(CheckDigit::verify('740812', 'X'))->toBeFalse();
});

it('treats unexpected characters as zero-weight', function () {
    // The trailing symbol carries no value, so it does not change the sum.
    expect(CheckDigit::calculate('1!'))->toBe(CheckDigit::calculate('1'));
});
