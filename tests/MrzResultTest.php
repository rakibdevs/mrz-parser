<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\MrzParser;
use Rakibdevs\MrzParser\MrzResult;

it('reads an MRZ into a typed result', function () {
    $result = MrzParser::read(MRZ_PASSPORT);

    expect($result)->toBeInstanceOf(MrzResult::class)
        ->and($result->type)->toBe('Passport')
        ->and($result->cardNo)->toBe('L898902C3')
        ->and($result->nationalityCode)->toBe('UTO')
        ->and($result->valid)->toBeTrue()
        ->and($result->fullName())->toBe('ANNA MARIA ERIKSSON');
});

it('preserves the canonical array via toArray()', function () {
    expect(MrzParser::read(MRZ_PASSPORT)->toArray())->toBe(MrzParser::parse(MRZ_PASSPORT));
});

it('exposes dates as DateTimeImmutable', function () {
    $result = MrzParser::read(MRZ_PASSPORT);

    expect($result->dateOfBirthAsDate()->format('Y-m-d'))->toBe('1974-08-12')
        ->and($result->dateOfExpiryAsDate()->format('Y-m-d'))->toBe('2012-04-15');
});

it('reports expiry relative to a reference date', function () {
    $result = MrzParser::read(MRZ_PASSPORT); // expires 2012-04-15

    expect($result->isExpired(new DateTimeImmutable('2011-01-01')))->toBeFalse()
        ->and($result->isExpired(new DateTimeImmutable('2020-01-01')))->toBeTrue();
});

it('returns null expiry status when no expiry date is present', function () {
    $result = MrzResult::fromArray(['type' => 'X', 'date_of_expiry' => null]);

    expect($result->isExpired())->toBeNull()
        ->and($result->dateOfExpiryAsDate())->toBeNull();
});
