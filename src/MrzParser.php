<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser;

use Rakibdevs\MrzParser\Contracts\ParserInterface;
use Rakibdevs\MrzParser\Enums\DocumentType;
use Rakibdevs\MrzParser\Exceptions\InvalidChecksumException;
use Rakibdevs\MrzParser\Exceptions\InvalidFormatException;
use Rakibdevs\MrzParser\Exceptions\NotSupportedException;
use Rakibdevs\MrzParser\Parser\FrenchIdMrzParser;
use Rakibdevs\MrzParser\Parser\PassportMrzParser;
use Rakibdevs\MrzParser\Parser\TravelDocument1MrzParser;
use Rakibdevs\MrzParser\Parser\TravelDocument2MrzParser;
use Rakibdevs\MrzParser\Parser\VisaMrzParser;
use Rakibdevs\MrzParser\Support\MrzNormalizer;

class MrzParser
{
    protected string $text;

    protected ?DocumentType $documentType = null;

    protected ?ParserInterface $adapter = null;

    public function __construct(?string $text = null)
    {
        $this->text = MrzNormalizer::normalize($text ?? '');
    }

    protected function setAdapter(): self
    {
        $this->adapter = match ($this->documentType) {
            DocumentType::PASSPORT => new PassportMrzParser(),
            DocumentType::VISA => new VisaMrzParser(),
            DocumentType::TRAVEL_DOCUMENT_1 => new TravelDocument1MrzParser(),
            DocumentType::TRAVEL_DOCUMENT_2 => new TravelDocument2MrzParser(),
            DocumentType::FRENCH_ID => new FrenchIdMrzParser(),
            default => null,
        };

        return $this;
    }

    protected function validate(): self
    {
        $this->documentType = (new ValidateDocument($this->text))->validate();

        return $this;
    }

    protected function get(): array
    {
        if ($this->adapter === null) {
            throw new NotSupportedException('This format is not supported yet!');
        }

        return $this->adapter->parse($this->text);
    }

    /**
     * Parse an MRZ into the canonical associative array.
     *
     * @param bool $strict when true, throw if any check digit fails
     *
     * @throws InvalidFormatException when the input is not a recognised MRZ
     * @throws NotSupportedException when the format has no parser
     * @throws InvalidChecksumException when $strict and a check digit fails
     *
     * @return array<string, mixed>
     */
    public static function parse(string $text, bool $strict = false): array
    {
        $data = (new static($text))
            ->validate()
            ->setAdapter()
            ->get();

        if ($strict && ($data['valid'] ?? false) === false) {
            throw new InvalidChecksumException('One or more MRZ check digits are invalid.');
        }

        return $data;
    }

    /**
     * Parse an MRZ into a typed {@see MrzResult}.
     */
    public static function read(string $text, bool $strict = false): MrzResult
    {
        return MrzResult::fromArray(static::parse($text, $strict));
    }

    /**
     * Parse an MRZ, returning null instead of throwing on invalid input.
     *
     * @return array<string, mixed>|null
     */
    public static function tryParse(string $text, bool $strict = false): ?array
    {
        try {
            return static::parse($text, $strict);
        } catch (InvalidFormatException | NotSupportedException | InvalidChecksumException) {
            return null;
        }
    }

    /**
     * Read an MRZ into a typed {@see MrzResult}, or null on invalid input.
     */
    public static function tryRead(string $text, bool $strict = false): ?MrzResult
    {
        $data = static::tryParse($text, $strict);

        return $data === null ? null : MrzResult::fromArray($data);
    }
}
