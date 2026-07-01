<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser;

use Rakibdevs\MrzParser\Enums\DocumentType;
use Rakibdevs\MrzParser\Exceptions\InvalidFormatException;
use Rakibdevs\MrzParser\Support\MrzNormalizer;

class ValidateDocument
{
    protected string $text;

    protected ?DocumentType $documentType = null;

    protected int $rows = 0;

    protected int $characterCountOfRow = 0;

    protected string $firstCharacter = '';

    public function __construct(?string $text = null)
    {
        $this->text = MrzNormalizer::normalize($text ?? '');
    }

    protected function setProperties(): self
    {
        $rows = MrzNormalizer::rows($this->text);
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
        if ($this->rows === 2 && $this->firstCharacter === 'P' && $this->characterCountOfRow === 44) {
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
        if ($this->rows === 2 && $this->firstCharacter === 'V' && in_array($this->characterCountOfRow, [44, 36], true)) {
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
        if ($this->rows === 3 && in_array($this->firstCharacter, ['I', 'A', 'C'], true) && $this->characterCountOfRow === 30) {
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
        if ($this->rows === 2 && in_array($this->firstCharacter, ['I', 'P', 'A', 'C'], true) && $this->characterCountOfRow === 36) {
            $this->documentType = DocumentType::TRAVEL_DOCUMENT_2;

            return true;
        }

        return false;
    }

    /**
     * French national ID (legacy Carte Nationale d'Identité)
     * 2 rows, 36 characters each, first line starts with 'IDFRA'.
     *
     * Checked before TD2 because it also starts with 'I' and is 2x36, but has
     * a bespoke field layout.
     */
    protected function isFrenchId(): bool
    {
        if ($this->rows === 2 && $this->characterCountOfRow === 36 && str_starts_with($this->text, 'IDFRA')) {
            $this->documentType = DocumentType::FRENCH_ID;

            return true;
        }

        return false;
    }

    /**
     * Validate Document Based on Structure
     *
     * Reference: https://en.wikipedia.org/wiki/Machine-readable_passport
     */
    protected function isValid(): bool
    {
        return $this->isPassport()
            || $this->isVisa()
            || $this->isTravelDocument1()
            || $this->isFrenchId()
            || $this->isTravelDocument2();
    }

    /**
     * Validate Machine Readable Zone from Document
     */
    public function validate(): DocumentType
    {
        $this->setProperties();

        if (! $this->isValid()) {
            throw new InvalidFormatException('The given input format is invalid!');
        }

        return $this->documentType;
    }
}
