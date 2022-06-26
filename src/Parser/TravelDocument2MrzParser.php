<?php

namespace Rakibdevs\MrzParser\Parser;

use Rakibdevs\MrzParser\Contracts\ParserInterface;
use Rakibdevs\MrzParser\Traits\CountryMapper;
use Rakibdevs\MrzParser\Traits\DateFormatter;
use Rakibdevs\MrzParser\Traits\GenderMapper;

class TravelDocument2MrzParser implements ParserInterface
{
    use DateFormatter;
    use GenderMapper;
    use CountryMapper;

    protected $text;

    protected $firstLine;

    protected $secondLine;

    protected $nameString;

    /**
     * Example String
     * Source: https://www.icao.int/publications/Documents/9303_p6_cons_en.pdf
     *
     * I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<
     * D231458907UTO7408122F1204159<<<<<<<6
     */
    protected function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Set Name String
     */
    protected function setNameString(): self
    {
        $this->nameString = explode('<<', substr($this->firstLine, 5));

        return $this;
    }

    /**
     * Extract information
     */
    protected function extract(): self
    {
        $text = explode("\n", $this->text);
        $this->firstLine = $text[0] ?? null;
        $this->secondLine = $text[1] ?? null;
        $this->setNameString();

        return $this;
    }

    /**
     * Get Type beased on first two string
     *
     * Type, This is at the discretion of the issuing state or authority,
     * but 1–2 should be AC for Crew Member Certificates and V is not allowed as 2nd character.
     * ID or I< are typically used for nationally issued ID cards and IP for passport cards.
     */
    protected function getType()
    {
        $firstTwoCharacter = substr($this->firstLine, 0, 2);

        return match ($firstTwoCharacter) {
            'AC' => 'Crew Member Certificates',
            'I<' => 'National ID',
            'IP' => 'Passport',
            default => "Travel Document (TD2)"
        };
    }

    /**
     * Get Document Number
     * 1-9	alpha+num+<	Document number
     */
    protected function getCardNo(): ?string
    {
        $cardNo = substr($this->secondLine, 0, 9);
        $cardNo = chop($cardNo, "<"); // remove extra '<' from card no

        return $cardNo;
    }

    /**
     * Get Document Issuer
     */
    protected function getIssuer(): ?string
    {
        $issuer = chop(substr($this->firstLine, 2, 3), "<");

        return $this->mapCountry($issuer);
    }

    /**
     * Get Date of Expiry
     * Second row 22-27	character: (YYMMDD)
     */
    protected function getDateOfExpiry(): ?string
    {
        $date = substr($this->secondLine, 21, 6);

        return $date ? $this->formatDate($date) : null;
    }

    /**
     * Get Date of Birth
     * Second row 14-19	character: (YYMMDD)
     */
    protected function getDateOfBirth(): ?string
    {
        $date = substr($this->secondLine, 13, 6);

        return $date ? $this->formatDate($date) : null;
    }

    /**
     * Get First Name from Name String
     *
     * <<ANNA<MARIA<<<<<<<<<<
     */
    protected function getFirstName(): ?string
    {
        return isset($this->nameString[1]) ? str_replace("<", " ", $this->nameString[1]) : null;
    }

    /**
     * Get Last Name from Name String
     */
    protected function getLastName(): ?string
    {
        return $this->nameString[0] ?? null;
    }

    /**
     * Get Gender
     * Position 21, M/F/<
     *
     */
    protected function getGender(): ?string
    {
        return $this->mapGender(substr($this->secondLine, 20, 1));
    }

    /**
     * Get Nationality
     */
    protected function getNationality(): ?string
    {
        $code = chop(substr($this->secondLine, 10, 3), "<");

        return $this->mapCountry($code);
    }

    /**
     * Get Output from MRZ
     *
     * @return array
     */
    protected function get(): array
    {
        return [
            'type' => $this->getType(),
            'card_no' => $this->getCardNo(),
            'issuer' => $this->getIssuer(),
            'date_of_expiry' => $this->getDateOfExpiry(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'date_of_birth' => $this->getDateOfBirth(),
            'gender' => $this->getGender(),
            'nationality' => $this->getNationality(),
        ];
    }

    /**
     * Parse MRZ to Json Data
     *
     * @param string $text
     * @return null|array
     */
    public function parse(string $text): ?array
    {
        return $this
            ->setText($text)
            ->extract()
            ->get();
    }
}
