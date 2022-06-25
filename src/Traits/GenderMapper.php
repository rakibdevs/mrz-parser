<?php

namespace Rakibdevs\MrzParser\Traits;

trait GenderMapper
{
    public function mapGender(string $code = null): ?string
    {
        return match ($code) {
            "M" => "Male",
            "F" => "Female",
            default => null
        };
    }
}
