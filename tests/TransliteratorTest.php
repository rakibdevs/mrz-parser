<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\Support\Transliterator;

it('transliterates Latin names to their MRZ form', function (string $input, string $expected) {
    expect(Transliterator::fromLatin($input))->toBe($expected);
})->with([
    'german umlaut' => ['Müller', 'MUELLER'],
    'eszett' => ['Weiß', 'WEISS'],
    'slashed o' => ['Jørgensen', 'JOERGENSEN'],
    'ae ligature' => ['Æther', 'AETHER'],
    'accents folded' => ['José', 'JOSE'],
    'spaces to filler' => ['Anna Maria', 'ANNA<MARIA'],
    'hyphen to filler' => ['Jean-Luc', 'JEAN<LUC'],
    'apostrophe to filler' => ["O'Brien", 'O<BRIEN'],
]);

it('matches an MRZ name against a visual-zone name', function () {
    expect(Transliterator::matches('MUELLER', 'Müller'))->toBeTrue()
        ->and(Transliterator::matches('ANNA<MARIA', 'Anna Maria'))->toBeTrue()
        ->and(Transliterator::matches('SMITH', 'Jones'))->toBeFalse();
});
