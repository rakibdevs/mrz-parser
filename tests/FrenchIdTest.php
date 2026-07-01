<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Enums\DocumentType;
use Rakibdevs\MrzParser\MrzParser;
use Rakibdevs\MrzParser\ValidateDocument;

it('detects the French national ID before TD2', function () {
    expect((new ValidateDocument(MRZ_FRENCH_ID))->validate())->toBe(DocumentType::FRENCH_ID);
});

it('parses a French national ID', function () {
    expect(MrzParser::parse(MRZ_FRENCH_ID))->toMatchArray([
        'type' => 'National ID (France)',
        'card_no' => '050693202043',
        'issuer' => 'France',
        'issuer_code' => 'FRA',
        'date_of_expiry' => null,
        'first_name' => 'CHRISTIANE NI',
        'last_name' => 'DOUEL',
        'date_of_birth' => '1929-06-20',
        'gender' => 'Female',
        'sex' => 'F',
        'nationality' => 'France',
        'valid' => true,
    ]);
});

it('validates all French ID check digits', function () {
    expect(MrzParser::parse(MRZ_FRENCH_ID)['validation'])->toBe([
        'card_no' => true,
        'date_of_birth' => true,
        'composite' => true,
    ]);
});

it('flags a tampered French ID composite check digit', function () {
    // Flip the final composite check digit from 3 to 4.
    $mrz = "IDFRADOUEL<<<<<<<<<<<<<<<<<<<<932013\n"
        . '0506932020438CHRISTIANE<<NI2906209F4';

    $data = MrzParser::parse($mrz);

    expect($data['validation']['composite'])->toBeFalse()
        ->and($data['valid'])->toBeFalse();
});

it('reports no expiry for a French ID', function () {
    expect(MrzParser::read(MRZ_FRENCH_ID)->isExpired())->toBeNull();
});
