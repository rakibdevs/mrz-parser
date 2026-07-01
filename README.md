
# MRZ (Machine Readable Zones) Parser for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rakibdevs/mrz-parser.svg?style=flat-square)](https://packagist.org/packages/rakibdevs/mrz-parser)


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
