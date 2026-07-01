<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Traits;

trait GenderMapper
{
    /**
     * Map Gender based on Code (M/F). Anything else (incl. '<') maps to null.
     */
    public function mapGender(?string $code = null): ?string
    {
        return match ($code) {
            'M' => 'Male',
            'F' => 'Female',
            default => null,
        };
    }
}
