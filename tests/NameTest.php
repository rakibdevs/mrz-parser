<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\MrzParser;

it('returns a null first name when only a surname is present', function () {
    $line1 = str_pad('P<UTOERIKSSON', 44, '<');
    $line2 = 'L898902C36UTO7408122F1204159ZE184226B<<<<<10';

    $data = MrzParser::parse("{$line1}\n{$line2}");

    expect($data['last_name'])->toBe('ERIKSSON')
        ->and($data['first_name'])->toBeNull();
});

it('joins multi-part given names with spaces (TD1)', function () {
    $line1 = 'I<UTOD231458907<<<<<<<<<<<<<<<';
    $line2 = '7408122F1204159UTO<<<<<<<<<<<6';
    $line3 = str_pad('WILLIAMS<<JOHN<PAUL', 30, '<');

    $data = MrzParser::parse("{$line1}\n{$line2}\n{$line3}");

    expect($data['last_name'])->toBe('WILLIAMS')
        ->and($data['first_name'])->toBe('JOHN PAUL');
});
