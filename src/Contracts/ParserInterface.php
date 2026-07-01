<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Contracts;

interface ParserInterface
{
    public function parse(string $text): ?array;
}
