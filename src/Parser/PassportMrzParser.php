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

class PassportMrzParser implements ParserInterface
{
    use DateFormatter;
    use GenderMapper;
    use CountryMapper;
    use NameFormatter;

    protected string $text = '';

    protected string $firstLine = '';

    protected string $secondLine = '';

    /**
     * Example String
     *
     * P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<
     * L898902C36UTO7408122F1204159ZE184226B<<<<<10
     */
    protected function setText(string $text): self
    {
        $this->text = MrzNormalizer::normalize($text);

        return $this;
    }

    protected function setNameString(): self
    {
        $this->nameString = explode('<<', substr($this->firstLine, 5));

        return $this;
    }

    protected function extract(): self
    {
        $rows = MrzNormalizer::rows($this->text);
        $this->firstLine = $rows[0] ?? '';
        $this->secondLine = $rows[1] ?? '';
        $this->setNameString();

        return $this;
    }

    /**
     * Second row first 9 character alpha+num+< Passport number
     */
    protected function getCardNo(): ?string
    {
        return rtrim(substr($this->secondLine, 0, 9), '<') ?: null;
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
     * Second row 22–27 character: (YYMMDD)
     */
    protected function getDateOfExpiry(): ?string
    {
        return $this->formatDate(substr($this->secondLine, 21, 6));
    }

    /**
     * Get Date of Birth
     * Second row 14–19 character: (YYMMDD)
     */
    protected function getDateOfBirth(): ?string
    {
        return $this->formatDate(substr($this->secondLine, 13, 6), 'Y-m-d', true);
    }

    /**
     * Get Gender from Position 21, M/F/<
     */
    protected function getGender(): ?string
    {
        return $this->mapGender(substr($this->secondLine, 20, 1));
    }

    protected function getSex(): ?string
    {
        $sex = substr($this->secondLine, 20, 1);

        return in_array($sex, ['M', 'F'], true) ? $sex : null;
    }

    /**
     * Get Personal Number
     * 29–42 alpha+num+< (may be used by the issuing country as it desires)
     */
    protected function getPersonalNumber(): ?string
    {
        return rtrim(substr($this->secondLine, 28, 14), '<') ?: null;
    }

    protected function getNationality(): ?string
    {
        return $this->mapCountry($this->getNationalityCode());
    }

    protected function getNationalityCode(): ?string
    {
        return rtrim(substr($this->secondLine, 10, 3), '<') ?: null;
    }

    /**
     * Verify the ICAO 9303 check digits for the TD3 second row.
     *
     * @return array<string, bool>
     */
    protected function getValidation(): array
    {
        $line = $this->secondLine;

        $composite = substr($line, 0, 10)
            . substr($line, 13, 7)
            . substr($line, 21, 7)
            . substr($line, 28, 15);

        return [
            'card_no' => CheckDigit::verify(substr($line, 0, 9), substr($line, 9, 1)),
            'date_of_birth' => CheckDigit::verify(substr($line, 13, 6), substr($line, 19, 1)),
            'date_of_expiry' => CheckDigit::verify(substr($line, 21, 6), substr($line, 27, 1)),
            'personal_number' => CheckDigit::verify(substr($line, 28, 14), substr($line, 42, 1)),
            'composite' => CheckDigit::verify($composite, substr($line, 43, 1)),
        ];
    }

    protected function get(): array
    {
        $validation = $this->getValidation();

        return [
            'type' => 'Passport',
            'card_no' => $this->getCardNo(),
            'issuer' => $this->getIssuer(),
            'issuer_code' => $this->getIssuerCode(),
            'date_of_expiry' => $this->getDateOfExpiry(),
            'first_name' => $this->firstNameFromNameString(),
            'last_name' => $this->lastNameFromNameString(),
            'date_of_birth' => $this->getDateOfBirth(),
            'gender' => $this->getGender(),
            'sex' => $this->getSex(),
            'personal_number' => $this->getPersonalNumber(),
            'optional_data' => $this->getPersonalNumber(),
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
