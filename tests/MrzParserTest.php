<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Exceptions\InvalidFormatException;
use Rakibdevs\MrzParser\MrzParser;

it('parses a TD3 passport', function () {
    expect(MrzParser::parse(MRZ_PASSPORT))->toMatchArray([
        'type' => 'Passport',
        'card_no' => 'L898902C3',
        'issuer' => 'Utopian',
        'issuer_code' => 'UTO',
        'date_of_expiry' => '2012-04-15',
        'first_name' => 'ANNA MARIA',
        'last_name' => 'ERIKSSON',
        'date_of_birth' => '1974-08-12',
        'gender' => 'Female',
        'sex' => 'F',
        'personal_number' => 'ZE184226B',
        'nationality' => 'Utopian',
        'nationality_code' => 'UTO',
        'valid' => true,
    ]);
});

it('parses a 44-character visa', function () {
    expect(MrzParser::parse(MRZ_VISA_44))->toMatchArray([
        'type' => 'Visa',
        'card_no' => 'L8988901C',
        'issuer' => 'Utopian',
        'first_name' => 'ANNA MARIA',
        'last_name' => 'ERIKSSON',
        'date_of_birth' => '1974-08-12',
        'gender' => 'Female',
        'valid' => true,
    ]);
});

it('parses a 36-character visa', function () {
    $data = MrzParser::parse(MRZ_VISA_36);

    expect($data['type'])->toBe('Visa')
        ->and($data['card_no'])->toBe('L8988901C')
        ->and($data['last_name'])->toBe('ERIKSSON')
        ->and($data['valid'])->toBeTrue();
});

it('parses a TD1 travel document', function () {
    expect(MrzParser::parse(MRZ_TD1))->toMatchArray([
        'type' => 'National ID',
        'card_no' => 'D23145890',
        'issuer' => 'Utopian',
        'date_of_expiry' => '2012-04-15',
        'first_name' => 'ANNA MARIA',
        'last_name' => 'ERIKSSON',
        'date_of_birth' => '1974-08-12',
        'gender' => 'Female',
        'nationality' => 'Utopian',
        'valid' => true,
    ]);
});

it('parses a TD2 travel document', function () {
    expect(MrzParser::parse(MRZ_TD2))->toMatchArray([
        'type' => 'National ID',
        'card_no' => 'D23145890',
        'issuer' => 'Utopian',
        'date_of_expiry' => '2012-04-15',
        'first_name' => 'ANNA MARIA',
        'last_name' => 'ERIKSSON',
        'date_of_birth' => '1974-08-12',
        'gender' => 'Female',
        'nationality' => 'Utopian',
        'valid' => true,
    ]);
});

it('exposes per-field check-digit validation', function () {
    $data = MrzParser::parse(MRZ_PASSPORT);

    expect($data['validation'])->toBe([
        'card_no' => true,
        'date_of_birth' => true,
        'date_of_expiry' => true,
        'personal_number' => true,
        'composite' => true,
    ]);
});

it('flags an invalid document-number check digit', function () {
    // Change the document number check digit (index 9) from 6 to 5.
    $mrz = "P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\n"
        . 'L898902C35UTO7408122F1204159ZE184226B<<<<<10';

    $data = MrzParser::parse($mrz);

    expect($data['validation']['card_no'])->toBeFalse()
        ->and($data['valid'])->toBeFalse();
});

it('normalizes CRLF and surrounding whitespace', function () {
    $crlf = "  P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<  \r\n"
        . "L898902C36UTO7408122F1204159ZE184226B<<<<<10\r\n";

    expect(MrzParser::parse($crlf))->toEqual(MrzParser::parse(MRZ_PASSPORT));
});

it('echoes the normalized raw MRZ', function () {
    expect(MrzParser::parse(MRZ_PASSPORT)['raw'])->toBe(MRZ_PASSPORT);
});

it('throws on invalid formats', function (string $input) {
    MrzParser::parse($input);
})->throws(InvalidFormatException::class)->with([
    'empty' => [''],
    'single row' => ['P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<'],
    'too short' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<1"],
    'too long' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<X\nL898902C36UTO7408122F1204159ZE184226B<<<<<10X"],
    'unknown first char' => ["X<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<10"],
]);
