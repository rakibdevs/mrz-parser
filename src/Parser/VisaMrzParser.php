<?php

namespace Rakibdevs\MrzParser\Parser;

use Rakibdevs\MrzParser\Contracts\ParserInterface;
use Rakibdevs\MrzParser\Traits\CountryMapper;
use Rakibdevs\MrzParser\Traits\DateFormatter;
use Rakibdevs\MrzParser\Traits\GenderMapper;

class VisaMrzParser implements ParserInterface
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
     *
     * V<UNKKONI<<MARTINA<<<<<<<<<<<<<<<<<<<<<<<<<<
     * K0503499<8UNK9701241F06022201170650553<<<<10
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
     * Second row first 9 character	alpha+num+<	Visa number
     */
    protected function getCardNo(): ?string
    {
        $cardNo = substr($this->secondLine, 0, 9);
        $cardNo = chop($cardNo, "<"); // remove extra '<' from card no

        return $cardNo;
    }

    /**
     * Get Visa Issuer
     */
    protected function getIssuer(): ?string
    {
        $issuer = chop(substr($this->firstLine, 2, 3), "<");

        return $this->mapCountry($issuer);
    }

    /**
     * Get Date of Expiry
     * Second row 22–27	character: (YYMMDD)
     */
    protected function getDateOfExpiry(): ?string
    {
        $date = substr($this->secondLine, 21, 6);

        return $date ? $this->formatDate($date) : null;
    }

    /**
     * Get Date of Birth
     * Second row 14–19	character: (YYMMDD)
     */
    protected function getDateOfBirth(): ?string
    {
        $date = substr($this->secondLine, 13, 6);

        return $date ? $this->formatDate($date) : null;
    }

    /**
     * Get First Name from Name String
     *
     * MARTINA<<<<<<<<<<<<<<<<<<<<<<<<<<
     */
    protected function getFirstName(): ?string
    {
        return isset($this->nameString[1]) ? str_replace('<', ' ', chop($this->nameString[1], "<")) : null;
    }

    /**
     * Get Last Name from Name String
     *
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
            'type' => 'Visa',
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
