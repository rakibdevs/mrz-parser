<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Support;

/**
 * ICAO 9303 name transliteration.
 *
 * The MRZ name field only carries A-Z and the filler '<'. National characters
 * are transliterated according to ICAO Doc 9303 Part 3 — e.g. 'Ö' -> 'OE',
 * 'ß' -> 'SS', 'Æ' -> 'AE'. This mapping is deterministic in the Latin -> MRZ
 * direction but NOT reversible (an MRZ 'OE' may be a genuine "OE" or a
 * transliterated 'Ö'), so only {@see self::fromLatin()} is provided. Use
 * {@see self::matches()} to compare a visual-zone name against an MRZ name.
 */
final class Transliterator
{
    /**
     * Multi-character substitutions (applied before single-character ones).
     *
     * @var array<string, string>
     */
    private const DIGRAPHS = [
        'Æ' => 'AE', 'æ' => 'AE',
        'Œ' => 'OE', 'œ' => 'OE',
        'ß' => 'SS',
        'Ĳ' => 'IJ', 'ĳ' => 'IJ',
        'Ø' => 'OE', 'ø' => 'OE',
        'Å' => 'AA', 'å' => 'AA',
        'Ð' => 'D', 'ð' => 'D',
        'Þ' => 'TH', 'þ' => 'TH',
    ];

    /**
     * Diacritic folding for the remaining accented letters.
     *
     * @var array<string, string>
     */
    private const DIACRITICS = [
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Ā' => 'A', 'Ă' => 'A', 'Ą' => 'A',
        'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ę' => 'E', 'Ě' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I',
        'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'OE', 'Ō' => 'O', 'Ŏ' => 'O', 'Ő' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'UE', 'Ū' => 'U', 'Ŭ' => 'U', 'Ů' => 'U', 'Ű' => 'U', 'Ų' => 'U',
        'Ý' => 'Y', 'Ÿ' => 'Y',
        'Ł' => 'L', 'Ĺ' => 'L', 'Ľ' => 'L',
        'Ś' => 'S', 'Š' => 'S', 'Ş' => 'S', 'Ŝ' => 'S',
        'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z',
        'Ř' => 'R', 'Ŕ' => 'R',
        'Ť' => 'T', 'Ţ' => 'T',
        'Ğ' => 'G', 'Ĝ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
    ];

    /**
     * Convert a Latin (visual-zone) name to its MRZ representation:
     * uppercased, diacritics transliterated, spaces / hyphens / apostrophes
     * turned into the '<' filler.
     */
    public static function fromLatin(string $name): string
    {
        $name = mb_strtoupper(trim($name));
        $name = strtr($name, self::DIGRAPHS);
        $name = strtr($name, self::DIACRITICS);

        // Word separators become '<'; anything still non A-Z is dropped.
        $name = preg_replace('/[\s\-\'’.]+/u', '<', $name) ?? $name;
        $name = preg_replace('/[^A-Z<]/u', '', $name) ?? $name;

        return trim($name, '<');
    }

    /**
     * Compare a name taken from an MRZ against a visual-zone (Latin) name,
     * ignoring filler and separators. Useful to verify an MRZ against typed
     * input.
     */
    public static function matches(string $mrzName, string $visualName): bool
    {
        return self::canonical($mrzName) === self::canonical(self::fromLatin($visualName));
    }

    private static function canonical(string $value): string
    {
        return trim(str_replace('<', ' ', strtoupper($value)));
    }
}
