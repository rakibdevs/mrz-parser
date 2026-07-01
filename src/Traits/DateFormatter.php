<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Traits;

use DateTime;

trait DateFormatter
{
    /**
     * Format a date from the MRZ YYMMDD representation.
     *
     * MRZ carries no century, so for dates that must lie in the past
     * (e.g. date of birth) a naive value that resolves to the future is
     * shifted back 100 years. Impossible dates (e.g. Feb 30) return null.
     */
    public function formatDate(string $date, string $format = 'Y-m-d', bool $isPast = false): ?string
    {
        if (! $this->validateDateFormat($date)) {
            return null;
        }

        $dateTime = DateTime::createFromFormat('!ymd', $date);
        $errors = DateTime::getLastErrors();

        if ($dateTime === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        if ($isPast && $dateTime > new DateTime('today')) {
            $dateTime->modify('-100 years');
        }

        return $dateTime->format($format);
    }

    /**
     * Cheap pre-filter for the YYMMDD shape.
     * MM range must be 01-12, DD range must be 01-31.
     * Calendar-impossible dates (Feb 30, Feb 29 on non-leap years) are
     * rejected later via DateTime::getLastErrors().
     */
    public function validateDateFormat(string $date): bool
    {
        if (strlen($date) !== 6 || ! ctype_digit($date)) {
            return false;
        }

        $month = (int) substr($date, 2, 2);
        $day = (int) substr($date, 4, 2);

        return $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31;
    }
}
