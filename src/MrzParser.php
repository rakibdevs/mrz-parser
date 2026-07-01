<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser;

use Rakibdevs\MrzParser\Contracts\ParserInterface;
use Rakibdevs\MrzParser\Enums\DocumentType;
use Rakibdevs\MrzParser\Exceptions\NotSupportedException;
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

    public static function parse(string $text): array
    {
        return (new static($text))
            ->validate()
            ->setAdapter()
            ->get();
    }
}
