
# MRZ (Machine Readable Zones) Parser for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rakibdevs/mrz-parser.svg?style=flat-square)](https://packagist.org/packages/rakibdevs/mrz-parser)
[![Tests](https://img.shields.io/github/actions/workflow/status/rakibdevs/mrz-parser/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rakibdevs/mrz-parser/actions/workflows/run-tests.yml)
[![PHP Version](https://img.shields.io/packagist/dependency-v/rakibdevs/mrz-parser/php?style=flat-square)](https://packagist.org/packages/rakibdevs/mrz-parser)
[![Total Downloads](https://img.shields.io/packagist/dt/rakibdevs/mrz-parser.svg?style=flat-square)](https://packagist.org/packages/rakibdevs/mrz-parser)
[![License](https://img.shields.io/packagist/l/rakibdevs/mrz-parser.svg?style=flat-square)](LICENSE.md)


A PHP package for MRZ (Machine Readable Zones) code parser for Passport, Visa & Travel Document (TD1 & TD2).

It reads the machine-readable zone, extracts the document fields, and — new in v2 — verifies the ICAO 9303 check digits so you can tell whether the data is internally consistent.

## Requirements

- PHP 8.2 or higher

## Installation

You can install the package via composer:

```bash
composer require rakibdevs/mrz-parser
```

## Usage

```php
use Rakibdevs\MrzParser\MrzParser;
.....
.....
$data = MrzParser::parse('P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<
L898902C36UTO7408122F1204159ZE184226B<<<<<10');

var_dump($data);
```
## Output
```json
{
    "type": "Passport",
    "card_no": "L898902C3",
    "issuer": "Utopian",
    "issuer_code": "UTO",
    "date_of_expiry": "2012-04-15",
    "first_name": "ANNA MARIA",
    "last_name": "ERIKSSON",
    "date_of_birth": "1974-08-12",
    "gender": "Female",
    "sex": "F",
    "personal_number": "ZE184226B",
    "optional_data": "ZE184226B",
    "nationality": "Utopian",
    "nationality_code": "UTO",
    "valid": true,
    "validation": {
        "card_no": true,
        "date_of_birth": true,
        "date_of_expiry": true,
        "personal_number": true,
        "composite": true
    },
    "raw": "P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<10"
}
```

### Check-digit validation

Every result carries a `valid` flag (overall) and a `validation` map with the outcome of each ICAO 9303 check digit (`card_no`, `date_of_birth`, `date_of_expiry`, `personal_number` for passports, and the `composite`). Visas carry no composite check digit, so their `validation` map omits it. Use these to reject OCR mistakes or tampered documents.

### Input handling

Input is normalized before parsing: `\r\n` / `\r` line endings are converted to `\n`, surrounding whitespace is trimmed, and blank lines are dropped — so MRZ strings copied from Windows or OCR output parse the same as clean input. Dates are validated strictly (impossible dates such as Feb 30 return `null`), and a date of birth that would resolve to the future is shifted back a century.

## Advanced usage

### Typed result object

`read()` returns an `MrzResult` value object with typed properties and helpers, while `toArray()` gives you the same array as `parse()`:

```php
$mrz = MrzParser::read($text);

$mrz->cardNo;                    // 'L898902C3'
$mrz->fullName();                // 'ANNA MARIA ERIKSSON'
$mrz->dateOfBirthAsDate();       // DateTimeImmutable
$mrz->isExpired();               // bool (or null when the format has no expiry)
$mrz->valid;                     // bool
$mrz->toArray();                 // the canonical parse() array
```

### Non-throwing parsing

`parse()`/`read()` throw on unrecognised input. Use the `try*` variants to get `null` instead:

```php
MrzParser::tryParse($text);      // ?array
MrzParser::tryRead($text);       // ?MrzResult
```

### Strict check-digit mode

Pass `strict: true` to reject documents whose check digits don't verify (throws `InvalidChecksumException`; the `try*` variants return `null`):

```php
MrzParser::parse($text, strict: true);
MrzParser::read($text, strict: true);
```

### Name transliteration

The MRZ name field only holds `A–Z`. Convert a visual-zone (accented) name to its MRZ form, or compare the two:

```php
use Rakibdevs\MrzParser\Support\Transliterator;

Transliterator::fromLatin('Müller');            // 'MUELLER'
Transliterator::fromLatin('Jørgensen');         // 'JOERGENSEN'
Transliterator::matches('MUELLER', 'Müller');   // true
```

### Laravel

The package ships an auto-discovered service provider and a validation rule (requires `illuminate/support`):

```php
use Rakibdevs\MrzParser\Laravel\Rules\ValidMrz;

$request->validate([
    'mrz' => [new ValidMrz()],              // must be a parseable MRZ
    'mrz' => [new ValidMrz(strict: true)],  // ...with valid check digits
]);
```

## Supported Document
#### [Passport (TD3)](https://www.icao.int/publications/Documents/9303_p4_cons_en.pdf)
```
P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<
L898902C36UTO7408122F1204159ZE184226B<<<<<10
```
#### [Visa](https://www.icao.int/publications/Documents/9303_p7_cons_en.pdf)
```
V<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<
L8988901C4XXX7408122F96121096ZE184226B<<<<<<
```
Or
```
V<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<
L8988901C4XXX7408122F9612109<<<<<<<<
```
#### [Travel Document (TD1)](https://www.icao.int/publications/Documents/9303_p5_cons_en.pdf)
```
I<UTOD231458907<<<<<<<<<<<<<<<
7408122F1204159UTO<<<<<<<<<<<6
ERIKSSON<<ANNA<MARIA<<<<<<<<<<
```

#### [Travel Document (TD2)](https://www.icao.int/publications/Documents/9303_p6_cons_en.pdf)
```
I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<
D231458907UTO7408122F1204159<<<<<<<6
```

#### French National ID (legacy 2×36 Carte Nationale d'Identité)
This pre-2021 format predates ICAO TD1/TD2 and has its own layout (no expiry date is encoded).
```
IDFRADOUEL<<<<<<<<<<<<<<<<<<<<932013
0506932020438CHRISTIANE<<NI2906209F3
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Md. Rakibul Islam](https://github.com/rakibdevs)
- [All Contributors](../../contributors)

Special thanks to [tetrahydra](https://github.com/tetrahydra) for organised country list, [Al Amin](https://github.com/al-amindev) for help extracting information.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
