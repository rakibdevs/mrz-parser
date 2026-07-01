<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Enums;

enum DocumentType: string
{
    case PASSPORT = 'passport';
    case VISA = 'visa';
    case TRAVEL_DOCUMENT_1 = 'travel_document_1';
    case TRAVEL_DOCUMENT_2 = 'travel_document_2';
    case FRENCH_ID = 'french_id';
}
