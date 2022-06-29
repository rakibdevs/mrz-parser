<?php

namespace Rakibdevs\MrzParser\Traits;

use DateTime;

trait DateFormatter
{
    /**
     * Format date from YYMMDD
     *
     * @param string $date
     * @param string $format
     *
     * @return null|string
     */
    public function formatDate($date, $format = "Y-m-d"): ?string
    {
        if ($this->validateDateFormat($date)) {
            $dateTime = DateTime::createFromFormat('ymd', $date);

            return $dateTime->format($format);
        }

        return null;
    }

    /**
     * Validate Date Format YYMMDD
     * MM range must be 01-12
     * DD range must be 01-31
     *
     * @param string $date
     * @return bool
     */
    public function validateDateFormat(string $date): bool
    {
        $month = (int) substr($date, 2, 2);
        $date = (int) substr($date, 4, 2);

        return $month >= 1 && $month <= 12 && $date >= 1 && $date <= 31;
    }
}
