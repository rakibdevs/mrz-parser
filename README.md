
# MRZ (Machine Readable Zones) Parser for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rakibdevs/mrz-parser.svg?style=flat-square)](https://packagist.org/packages/rakibdevs/mrz-parser)
[![Tests](https://github.com/rakibdevs/mrz-parser/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/rakibdevs/mrz-parser/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/rakibdevs/mrz-parser.svg?style=flat-square)](https://packagist.org/packages/rakibdevs/mrz-parser)

A PHP package for MRZ (Machine Readable Zones) code parser for Passport, Visa & Travel Document (TD1 & TD2).

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
    "date_of_expiry": "2012-04-15",
    "first_name": "ANNA MARIA",
    "last_name": "ERIKSSON",
    "date_of_birth": "1974-08-12",
    "gender": "Female",
    "personal_number": "ZE184226B",
    "nationality": "Utopian"
}
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
