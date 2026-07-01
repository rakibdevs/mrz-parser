<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\MrzParser;

function td1WithPrefix(string $prefix): string
{
    return "{$prefix}UTOD231458907<<<<<<<<<<<<<<<\n"
        . "7408122F1204159UTO<<<<<<<<<<<6\n"
        . 'ERIKSSON<<ANNA<MARIA<<<<<<<<<<';
}

function td2WithPrefix(string $prefix): string
{
    return "{$prefix}UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\n"
        . 'D231458907UTO7408122F1204159<<<<<<<6';
}

it('labels TD1 documents from the first two characters', function (string $prefix, string $label) {
    expect(MrzParser::parse(td1WithPrefix($prefix))['type'])->toBe($label);
})->with([
    'crew' => ['AC', 'Crew Member Certificates'],
    'national id' => ['I<', 'National ID'],
    'passport card' => ['IP', 'Passport'],
    'default' => ['C<', 'Travel Document (TD1)'],
]);

it('labels TD2 documents from the first two characters', function (string $prefix, string $label) {
    expect(MrzParser::parse(td2WithPrefix($prefix))['type'])->toBe($label);
})->with([
    'crew' => ['AC', 'Crew Member Certificates'],
    'national id' => ['I<', 'National ID'],
    'passport card' => ['IP', 'Passport'],
    'default' => ['C<', 'Travel Document (TD2)'],
]);
