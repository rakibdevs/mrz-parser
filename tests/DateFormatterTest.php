<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Traits\DateFormatter;

$formatter = new class () {
    use DateFormatter;
};

it('formats a valid YYMMDD date', function () use ($formatter) {
    expect($formatter->formatDate('740812'))->toBe('1974-08-12');
});

it('rejects impossible calendar dates', function (string $date) use ($formatter) {
    expect($formatter->formatDate($date))->toBeNull();
})->with([
    'month 13' => ['741312'],
    'day 32' => ['740832'],
    'feb 30' => ['110230'],
    'feb 29 non-leap' => ['110229'],
    'filler' => ['<<<<<<'],
    'short' => ['7408'],
    'non-digit' => ['74O812'],
]);

it('shifts a future date of birth back a century', function () use ($formatter) {
    // '491231' naively parses to 2049 (a future year); as a date of birth
    // it must be shifted back to 1949.
    expect($formatter->formatDate('491231', 'Y-m-d', true))->toBe('1949-12-31');
});

it('keeps expiry dates on the literal century pivot', function () use ($formatter) {
    expect($formatter->formatDate('491231'))->toBe('2049-12-31');
});

it('validates the YYMMDD shape', function () use ($formatter) {
    expect($formatter->validateDateFormat('740812'))->toBeTrue()
        ->and($formatter->validateDateFormat('741312'))->toBeFalse()
        ->and($formatter->validateDateFormat('<<<<<<'))->toBeFalse();
});
