<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Support;

final class MrzNormalizer
{
    /**
     * Normalize raw MRZ input so that Windows/OCR output parses identically.
     *
     * - Converts CRLF / CR line endings to LF
     * - Trims surrounding whitespace on every line
     * - Drops blank lines (e.g. a trailing newline)
     * - Uppercases (MRZ is always A-Z, 0-9 and '<')
     */
    public static function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $lines = array_map('trim', explode("\n", $text));
        $lines = array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));

        return strtoupper(implode("\n", $lines));
    }

    /**
     * Normalize and split the MRZ into its individual rows.
     *
     * @return array<int, string>
     */
    public static function rows(string $text): array
    {
        $normalized = self::normalize($text);

        return $normalized === '' ? [] : explode("\n", $normalized);
    }
}
