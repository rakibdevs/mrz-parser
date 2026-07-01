<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Parser;

use Rakibdevs\MrzParser\Contracts\ParserInterface;
use Rakibdevs\MrzParser\Support\CheckDigit;
use Rakibdevs\MrzParser\Support\MrzNormalizer;
use Rakibdevs\MrzParser\Traits\CountryMapper;
use Rakibdevs\MrzParser\Traits\DateFormatter;
use Rakibdevs\MrzParser\Traits\GenderMapper;
use Rakibdevs\MrzParser\Traits\NameFormatter;

class TravelDocument1MrzParser implements ParserInterface
{
    use DateFormatter;
    use GenderMapper;
    use CountryMapper;
    use NameFormatter;

    protected string $text = '';

    protected string $firstLine = '';

    protected string $secondLine = '';

    protected string $thirdLine = '';

    /**
     * Example String
     * Source: https://www.icao.int/publications/Documents/9303_p5_cons_en.pdf
     *
     * I<UTOD231458907<<<<<<<<<<<<<<<
     * 7408122F1204159UTO<<<<<<<<<<<6
     * ERIKSSON<<ANNA<MARIA<<<<<<<<<<
     */
    protected function setText(string $text): self
    {
        $this->text = MrzNormalizer::normalize($text);

        return $this;
    }

    protected function setNameString(): self
    {
        $this->nameString = explode('<<', $this->thirdLine);

        return $this;
    }

    protected function extract(): self
    {
        $rows = MrzNormalizer::rows($this->text);
        $this->firstLine = $rows[0] ?? '';
        $this->secondLine = $rows[1] ?? '';
        $this->thirdLine = $rows[2] ?? '';
        $this->setNameString();

        return $this;
    }

    /**
     * Type is at the discretion of the issuing state. 1–2 is AC for Crew
     * Member Certificates; ID / I< for national ID cards; IP for passport cards.
     */
    protected function getType(): string
    {
        return match (substr($this->firstLine, 0, 2)) {
            'AC' => 'Crew Member Certificates',
            'I<' => 'National ID',
            'IP' => 'Passport',
            default => 'Travel Document (TD1)',
        };
    }

    /**
     * Get Document Number
     * First row 6–14 alpha+num+< Document number
     */
    protected function getCardNo(): ?string
    {
        return rtrim(substr($this->firstLine, 5, 9), '<') ?: null;
    }

    protected function getIssuer(): ?string
    {
        return $this->mapCountry($this->getIssuerCode());
    }

    protected function getIssuerCode(): ?string
    {
        return rtrim(substr($this->firstLine, 2, 3), '<') ?: null;
    }

    /**
     * Get Date of Expiry
     * Second row 9-14 character: (YYMMDD)
     */
    protected function getDateOfExpiry(): ?string
    {
        return $this->formatDate(substr($this->secondLine, 8, 6));
    }

    /**
     * Get Date of Birth
     * Second row 1-6 character: (YYMMDD)
     */
    protected function getDateOfBirth(): ?string
    {
        return $this->formatDate(substr($this->secondLine, 0, 6), 'Y-m-d', true);
    }

    protected function getGender(): ?string
    {
        return $this->mapGender(substr($this->secondLine, 7, 1));
    }

    protected function getSex(): ?string
    {
        $sex = substr($this->secondLine, 7, 1);

        return in_array($sex, ['M', 'F'], true) ? $sex : null;
    }

    protected function getNationality(): ?string
    {
        return $this->mapCountry($this->getNationalityCode());
    }

    protected function getNationalityCode(): ?string
    {
        return rtrim(substr($this->secondLine, 15, 3), '<') ?: null;
    }

    protected function getOptionalData(): ?string
    {
        return rtrim(substr($this->firstLine, 15, 15), '<') ?: null;
    }

    /**
     * Verify the ICAO 9303 TD1 check digits (document number on line 1, date
     * of birth / expiry on line 2, and the composite over all fields).
     *
     * @return array<string, bool>
     */
    protected function getValidation(): array
    {
        $line1 = $this->firstLine;
        $line2 = $this->secondLine;

        $composite = substr($line1, 5, 25)
            . substr($line2, 0, 7)
            . substr($line2, 8, 7)
            . substr($line2, 18, 11);

        return [
            'card_no' => CheckDigit::verify(substr($line1, 5, 9), substr($line1, 14, 1)),
            'date_of_birth' => CheckDigit::verify(substr($line2, 0, 6), substr($line2, 6, 1)),
            'date_of_expiry' => CheckDigit::verify(substr($line2, 8, 6), substr($line2, 14, 1)),
            'composite' => CheckDigit::verify($composite, substr($line2, 29, 1)),
        ];
    }

    protected function get(): array
    {
        $validation = $this->getValidation();

        return [
            'type' => $this->getType(),
            'card_no' => $this->getCardNo(),
            'issuer' => $this->getIssuer(),
            'issuer_code' => $this->getIssuerCode(),
            'date_of_expiry' => $this->getDateOfExpiry(),
            'first_name' => $this->firstNameFromNameString(),
            'last_name' => $this->lastNameFromNameString(),
            'date_of_birth' => $this->getDateOfBirth(),
            'gender' => $this->getGender(),
            'sex' => $this->getSex(),
            'optional_data' => $this->getOptionalData(),
            'nationality' => $this->getNationality(),
            'nationality_code' => $this->getNationalityCode(),
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
}
