<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Parser;

use Rakibdevs\MrzParser\Contracts\ParserInterface;
use Rakibdevs\MrzParser\Support\CheckDigit;
use Rakibdevs\MrzParser\Support\MrzNormalizer;
use Rakibdevs\MrzParser\Traits\CountryMapper;
use Rakibdevs\MrzParser\Traits\DateFormatter;
use Rakibdevs\MrzParser\Traits\GenderMapper;

/**
 * French national identity card (Carte Nationale d'Identité), the pre-2021
 * legacy 2x36 format. It predates ICAO TD1/TD2 and has a bespoke layout:
 *
 * Line 1: type[0:2] country[2:5] surname[5:30] admin[30:36]
 * Line 2: docNumber[0:12] cd[12] givenNames[13:27] dob[27:33] cd[33] sex[34] compositeCd[35]
 *
 * There is no expiry date. The line-1 admin block and the document number are
 * opaque administrative identifiers, exposed as-is. The composite check digit
 * covers line 1 followed by the first 35 characters of line 2, with a single
 * continuous 7-3-1 weighting across both lines.
 */
class FrenchIdMrzParser implements ParserInterface
{
    use DateFormatter;
    use GenderMapper;
    use CountryMapper;

    protected string $text = '';

    protected string $firstLine = '';

    protected string $secondLine = '';

    protected function setText(string $text): self
    {
        $this->text = MrzNormalizer::normalize($text);

        return $this;
    }

    protected function extract(): self
    {
        $rows = MrzNormalizer::rows($this->text);
        $this->firstLine = $rows[0] ?? '';
        $this->secondLine = $rows[1] ?? '';

        return $this;
    }

    protected function getCardNo(): ?string
    {
        return rtrim(substr($this->secondLine, 0, 12), '<') ?: null;
    }

    protected function getIssuer(): ?string
    {
        return $this->mapCountry($this->getIssuerCode());
    }

    protected function getIssuerCode(): ?string
    {
        return rtrim(substr($this->firstLine, 2, 3), '<') ?: null;
    }

    protected function getLastName(): ?string
    {
        return $this->cleanName(substr($this->firstLine, 5, 25));
    }

    protected function getFirstName(): ?string
    {
        return $this->cleanName(substr($this->secondLine, 13, 14));
    }

    protected function getDateOfBirth(): ?string
    {
        return $this->formatDate(substr($this->secondLine, 27, 6), 'Y-m-d', true);
    }

    protected function getGender(): ?string
    {
        return $this->mapGender(substr($this->secondLine, 34, 1));
    }

    protected function getSex(): ?string
    {
        $sex = substr($this->secondLine, 34, 1);

        return in_array($sex, ['M', 'F', 'X'], true) ? $sex : null;
    }

    protected function getOptionalData(): ?string
    {
        return rtrim(substr($this->firstLine, 30, 6), '<') ?: null;
    }

    /**
     * @return array<string, bool>
     */
    protected function getValidation(): array
    {
        $composite = $this->firstLine . substr($this->secondLine, 0, 35);

        return [
            'card_no' => CheckDigit::verify(substr($this->secondLine, 0, 12), substr($this->secondLine, 12, 1)),
            'date_of_birth' => CheckDigit::verify(substr($this->secondLine, 27, 6), substr($this->secondLine, 33, 1)),
            'composite' => CheckDigit::verify($composite, substr($this->secondLine, 35, 1)),
        ];
    }

    protected function get(): array
    {
        $validation = $this->getValidation();

        return [
            'type' => 'National ID (France)',
            'card_no' => $this->getCardNo(),
            'issuer' => $this->getIssuer(),
            'issuer_code' => $this->getIssuerCode(),
            'date_of_expiry' => null,
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'date_of_birth' => $this->getDateOfBirth(),
            'gender' => $this->getGender(),
            'sex' => $this->getSex(),
            'optional_data' => $this->getOptionalData(),
            'nationality' => $this->getIssuer(),
            'nationality_code' => $this->getIssuerCode(),
            'valid' => ! in_array(false, $validation, true),
            'validation' => $validation,
            'raw' => $this->text,
        ];
    }

    public function parse(string $text): ?array
    {
        return $this
            ->setText($text)
            ->extract()
            ->get();
    }

    /**
     * Trim filler and collapse '<' separators into single spaces.
     */
    private function cleanName(string $field): ?string
    {
        $name = trim(preg_replace('/\s+/', ' ', str_replace('<', ' ', $field)) ?? '');

        return $name === '' ? null : $name;
    }
}
