<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Traits\CountryMapper;
use Rakibdevs\MrzParser\Traits\GenderMapper;

$genders = new class () {
    use GenderMapper;
};

$countries = new class () {
    use CountryMapper;
};

it('maps gender codes', function (?string $code, ?string $expected) use ($genders) {
    expect($genders->mapGender($code))->toBe($expected);
})->with([
    'male' => ['M', 'Male'],
    'female' => ['F', 'Female'],
    'filler' => ['<', null],
    'unknown' => ['X', null],
    'null' => [null, null],
]);

it('maps known country codes to names', function () use ($countries) {
    expect($countries->mapCountry('USA'))->toBe('United States of America (the)')
        ->and($countries->mapCountry('UTO'))->toBe('Utopian');
});

it('returns null for unknown or filler country codes', function (?string $code) use ($countries) {
    expect($countries->mapCountry($code))->toBeNull();
})->with([
    'unknown' => ['ZZZ'],
    'filler' => ['<<<'],
    'null' => [null],
]);

it('returns the raw code only when it is known', function () use ($countries) {
    expect($countries->mapCountryCode('UTO'))->toBe('UTO')
        ->and($countries->mapCountryCode('ZZZ'))->toBeNull();
});
