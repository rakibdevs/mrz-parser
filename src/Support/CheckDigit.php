<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Support;

/**
 * ICAO 9303 check-digit computation (the "7-3-1" weighted modulo-10 scheme).
 *
 * Character values: digits 0-9 keep their value, letters A-Z map to 10-35,
 * and the filler '<' counts as 0. Each position is multiplied by a weight
 * that cycles 7, 3, 1; the check digit is the sum modulo 10.
 */
final class CheckDigit
{
    /** @var array<int, int> */
    private const WEIGHTS = [7, 3, 1];

    /**
     * Compute the check digit for a field.
     */
    public static function calculate(string $value): int
    {
        $sum = 0;

        foreach (str_split(strtoupper($value)) as $index => $char) {
            $sum += self::charValue($char) * self::WEIGHTS[$index % 3];
        }

        return $sum % 10;
    }

    /**
     * Verify a field against its expected check digit.
     *
     * A filler check digit ('<') is only accepted when the field itself is
     * entirely filler, matching ICAO's handling of unused optional fields.
     */
    public static function verify(string $value, string $expected): bool
    {
        $expected = strtoupper($expected);

        if ($expected === '<') {
            return trim($value, '<') === '';
        }

        if (! ctype_digit($expected)) {
            return false;
        }

        return self::calculate($value) === (int) $expected;
    }

    private static function charValue(string $char): int
    {
        if ($char === '<') {
            return 0;
        }

        if (ctype_digit($char)) {
            return (int) $char;
        }

        if (ctype_alpha($char)) {
            return ord($char) - 55; // 'A' (65) => 10 ... 'Z' (90) => 35
        }

        return 0;
    }
}
