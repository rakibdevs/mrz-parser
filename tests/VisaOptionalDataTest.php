<?php

declare(strict_types=1);

use Rakibdevs\MrzParser\MrzParser;

it('reads the 16-character optional field on an MRV-A (44)', function () {
    expect(MrzParser::parse(MRZ_VISA_44)['optional_data'])->toBe('6ZE184226B');
});

it('reads the 8-character optional field on an MRV-B (36)', function () {
    // The MRV-B sample carries only filler in its 8-char optional slot.
    expect(MrzParser::parse(MRZ_VISA_36)['optional_data'])->toBeNull();
});
