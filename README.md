
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
$data = MrzParser::parse('I<SWE59000002<8198703142391<<<
8703145M1701027SWE<<<<<<<<<<<8
SPECIMEN<<SVEN<<<<<<<<<<<<<<<<');

var_dump($data);
```

## Supported Document
|---|---|
|Type|Example|
|Passport (TD3)|P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<
L898902C36UTO7408122F1204159ZE184226B<<<<<10|


## Testing

```bash
composer test
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

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
