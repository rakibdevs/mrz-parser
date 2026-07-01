# Changelog

All notable changes to `mrz-parser` will be documented in this file.

## v2.0.0 - 2026-07-01

### Breaking changes

- Minimum PHP version is now **8.2** (was 8.0).
- `parse()` now returns additional keys (`issuer_code`, `nationality_code`, `sex`, `optional_data`, `valid`, `validation`, `raw`). Existing keys are unchanged.
- `Enums\DocumentType` is now a real backed enum instead of an abstract class with constants.

### Added

- **ICAO 9303 check-digit validation.** Each result exposes an overall `valid` flag plus a per-field `validation` map (document number, date of birth, date of expiry, personal number, and composite).
- Richer output: raw ISO alpha-3 codes (`issuer_code`, `nationality_code`), raw `sex` character, `optional_data`, and the normalized `raw` MRZ string.
- A full Pest test suite (parsers, validator, check digits, date/gender/country mappers, name handling) with ~99% coverage.

### Fixed

- CRLF / whitespace input is now normalized, so Windows- and OCR-sourced MRZ strings parse correctly instead of failing validation.
- Strict date validation rejects impossible calendar dates (e.g. Feb 30) instead of silently rolling them over.
- A date of birth that would resolve to the future is shifted back a century.
- Removed a PHP 8.4 implicit-nullable deprecation and guarded against `substr(null, ...)` on missing rows.
- Consistent trailing-filler trimming for given names across all document types.

### Changed

- Modernized the codebase: `declare(strict_types=1)`, typed properties, `match`-based adapter selection, and a cached country lookup table.
- CI now tests PHP 8.2, 8.3, 8.4 and 8.5; upgraded to Pest 3.

## v1.2.0 - 2022-06-30

**Full Changelog**: https://github.com/rakibdevs/mrz-parser/compare/v1.1.0...v1.2.0

1. Fixed Card No and First Name "<" Issue in Visa b292c6a
2. Added Semantic Versioning

Special Thanks to @sohelamin for the Review.

## v1.1.0 - 2022-06-29

### What's Changed

- Fixed #1 Replace the first name arrow with space.
- Fixed showing wrong passport number (0f66162:)
- Validate given date format in MRZ (ede0247)

### New Contributors

- @alaminfirdows made their first contribution in https://github.com/rakibdevs/mrz-parser/pull/2

**Full Changelog**: https://github.com/rakibdevs/mrz-parser/compare/v1.0...v1.1

## v1.0.0 - 2022-06-26

Extract information from Machine Readable Zone data for Passport, Visa, and Other Travel Documents.
