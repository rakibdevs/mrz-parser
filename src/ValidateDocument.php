<?php

namespace Rakibdevs\MrzParser;

use Rakibdevs\MrzParser\Enums\DocumentType;
use Rakibdevs\MrzParser\Exceptions\InvalidFormatException;

class ValidateDocument
{
    protected $text;

    protected $documentType;

    protected $rows;

    protected $characterCountOfRow;

    protected $firstCharacter;

    public function __construct(?string $text = null)
    {
        $this->text = $text;
    }

    protected function setProperties()
    {
        $rows = explode("\n", $this->text);
        $this->rows = count($rows);
        $this->characterCountOfRow = isset($rows[0]) ? strlen($rows[0]) : 0;
        $this->firstCharacter = substr($this->text, 0, 1);

        return $this;
    }

    /**
     * Passport (TD3)
     * 2 rows, 44 character each row, start with 'P'
     */
    protected function isPassport(): bool
    {
        if ($this->rows == 2 && $this->firstCharacter == 'P' && $this->characterCountOfRow == 44) {
            $this->documentType = DocumentType::PASSPORT;

            return true;
        }

        return false;
    }

    /**
     * Visa:
     * 2 rows, 36/44 character each row, start with 'V'
     */
    protected function isVisa(): bool
    {
        if ($this->rows == 2 && $this->firstCharacter == 'V' && in_array($this->characterCountOfRow, [44, 36])) {
            $this->documentType = DocumentType::VISA;

            return true;
        }

        return false;
    }

    /**
     * Travel Document (TD1)
     * 3 rows, 30 character each row, start with 'I/A/C'
     */
    protected function isTravelDocument1(): bool
    {
        if ($this->rows == 3 && in_array($this->firstCharacter, ["I", "A", "C"]) && $this->characterCountOfRow == 30) {
            $this->documentType = DocumentType::TRAVEL_DOCUMENT_1;

            return true;
        }

        return false;
    }

    /**
     * Travel Document (TD2)
     * 2 rows, 36 character each row, start with 'I/P/A/C'
     */
    protected function isTravelDocument2(): bool
    {
        if ($this->rows == 2 && in_array($this->firstCharacter, ["I", "P", "A", "C"]) && $this->characterCountOfRow == 36) {
            $this->documentType = DocumentType::TRAVEL_DOCUMENT_2;

            return true;
        }

        return false;
    }

    /**
     * Validate Document Based on Structure
     *
     * Reference: https://en.wikipedia.org/wiki/Machine-readable_passport
     *
     */
    protected function isValid()
    {
        return $this->isPassport() || $this->isVisa() || $this->isTravelDocument1() || $this->isTravelDocument2();
    }

    /**
     * Validate Machine Readable Zone from Document
     */
    public function validate(): mixed
    {
        $this->setProperties();

        if (! $this->isValid()) {
            throw new InvalidFormatException("The given input format is invalid!");
        }

        return $this->documentType;
    }
}
