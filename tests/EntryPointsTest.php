<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Exceptions\InvalidChecksumException;
use Rakibdevs\MrzParser\MrzParser;
use Rakibdevs\MrzParser\MrzResult;

const MRZ_BAD_CHECKSUM = "P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\n"
    . 'L898902C35UTO7408122F1204159ZE184226B<<<<<10';

it('tryParse returns an array for valid input and null otherwise', function () {
    expect(MrzParser::tryParse(MRZ_PASSPORT))->toBeArray()
        ->and(MrzParser::tryParse('not an mrz'))->toBeNull()
        ->and(MrzParser::tryParse(''))->toBeNull();
});

it('tryRead returns a result or null', function () {
    expect(MrzParser::tryRead(MRZ_PASSPORT))->toBeInstanceOf(MrzResult::class)
        ->and(MrzParser::tryRead('garbage'))->toBeNull();
});

it('strict mode throws on a failed check digit', function () {
    MrzParser::parse(MRZ_BAD_CHECKSUM, strict: true);
})->throws(InvalidChecksumException::class);

it('strict mode passes a valid document', function () {
    expect(MrzParser::parse(MRZ_PASSPORT, strict: true)['valid'])->toBeTrue();
});

it('strict tryParse swallows the checksum failure', function () {
    expect(MrzParser::tryParse(MRZ_BAD_CHECKSUM, strict: true))->toBeNull()
        ->and(MrzParser::tryParse(MRZ_BAD_CHECKSUM))->toBeArray(); // non-strict still parses
});
