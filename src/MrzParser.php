<?php

namespace Rakibdevs\MrzParser;

use Rakibdevs\MrzParser\Enums\DocumentType;
use Rakibdevs\MrzParser\Exceptions\NotSupportedException;
use Rakibdevs\MrzParser\Parser\PassportMrzParser;
use Rakibdevs\MrzParser\Parser\TravelDocument1MrzParser;
use Rakibdevs\MrzParser\Parser\TravelDocument2MrzParser;
use Rakibdevs\MrzParser\Parser\VisaMrzParser;

class MrzParser
{
    protected $text;

    protected $documentType;

    protected $adapter;

    public function __construct(?string $text = null)
    {
        $this->text = $text;
    }

    protected function setAdapter()
    {
        switch ($this->documentType) {
            case DocumentType::PASSPORT:
                $this->adapter = new PassportMrzParser();

                break;
            case DocumentType::VISA:
                $this->adapter = new VisaMrzParser();

                break;
            case DocumentType::TRAVEL_DOCUMENT_1:
                $this->adapter = new TravelDocument1MrzParser();

                break;
            case DocumentType::TRAVEL_DOCUMENT_2:
                $this->adapter = new TravelDocument2MrzParser();

                break;
        }

        return $this;
    }

    protected function validate()
    {
        $this->documentType = (new ValidateDocument($this->text))->validate();

        return $this;
    }

    protected function get(): ?array
    {
        if (empty($this->adapter)) {
            throw new NotSupportedException("This format is not supported yet!");
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
