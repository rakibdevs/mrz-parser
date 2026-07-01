<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Enums\DocumentType;
use Rakibdevs\MrzParser\Exceptions\InvalidFormatException;
use Rakibdevs\MrzParser\ValidateDocument;

it('detects the document type from structure', function (string $mrz, DocumentType $type) {
    expect((new ValidateDocument($mrz))->validate())->toBe($type);
})->with([
    'passport' => [MRZ_PASSPORT, DocumentType::PASSPORT],
    'visa 44' => [MRZ_VISA_44, DocumentType::VISA],
    'visa 36' => [MRZ_VISA_36, DocumentType::VISA],
    'td1' => [MRZ_TD1, DocumentType::TRAVEL_DOCUMENT_1],
    'td2' => [MRZ_TD2, DocumentType::TRAVEL_DOCUMENT_2],
]);

it('accepts every valid TD1 first character', function (string $first) {
    $mrz = "{$first}<UTOD231458907<<<<<<<<<<<<<<<\n"
        . "7408122F1204159UTO<<<<<<<<<<<6\n"
        . 'ERIKSSON<<ANNA<MARIA<<<<<<<<<<';

    expect((new ValidateDocument($mrz))->validate())->toBe(DocumentType::TRAVEL_DOCUMENT_1);
})->with(['I', 'A', 'C']);

it('accepts every valid TD2 first character', function (string $first) {
    $mrz = "{$first}<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\n"
        . 'D231458907UTO7408122F1204159<<<<<<<6';

    expect((new ValidateDocument($mrz))->validate())->toBe(DocumentType::TRAVEL_DOCUMENT_2);
})->with(['I', 'P', 'A', 'C']);

it('throws on structurally invalid input', function (string $mrz) {
    (new ValidateDocument($mrz))->validate();
})->throws(InvalidFormatException::class)->with([
    'empty' => [''],
    'one row' => ['P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<'],
    'wrong length' => ["P<UTO\nL898902C3"],
]);
