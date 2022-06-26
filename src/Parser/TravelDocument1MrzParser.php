<?php

namespace Rakibdevs\MrzParser\Parser;

use Rakibdevs\MrzParser\Contracts\ParserInterface;
use Rakibdevs\MrzParser\Traits\CountryMapper;
use Rakibdevs\MrzParser\Traits\DateFormatter;
use Rakibdevs\MrzParser\Traits\GenderMapper;

class TravelDocument1MrzParser implements ParserInterface
{
    use DateFormatter;
    use GenderMapper;
    use CountryMapper;

    protected $text;

    protected $firstLine;

    protected $secondLine;

    protected $thirdLine;

    protected $nameString;

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
        $this->text = $text;

        return $this;
    }

    /**
     * Set Name String
     */
    protected function setNameString(): self
    {
        $this->nameString = explode('<<', $this->thirdLine);

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
        $this->thirdLine = $text[2] ?? null;
        $this->setNameString();

        return $this;
    }


    /**
     * Second row first 9 character	alpha+num+<	Document number
     */
    protected function getCardNo(): ?string
    {
        $cardNo = substr($this->firstLine, 5, 9);
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
     * Second row 9-14	character: (YYMMDD)
     */
    protected function getDateOfExpiry(): ?string
    {
        $date = substr($this->secondLine, 8, 6);

        return $date ? $this->formatDate($date) : null;
    }

    /**
     * Get Date of Birth
     * Second row 1-6	character: (YYMMDD)
     */
    protected function getDateOfBirth(): ?string
    {
        $date = substr($this->secondLine, 0, 6);

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
     * Position 8, M/F/<
     *
     */
    protected function getGender(): ?string
    {
        return $this->mapGender(substr($this->secondLine, 7, 1));
    }

    /**
     * Get Nationality
     */
    protected function getNationality(): ?string
    {
        $code = chop(substr($this->secondLine, 15, 3), "<");

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
            'type' => 'Official Travel Document',
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
