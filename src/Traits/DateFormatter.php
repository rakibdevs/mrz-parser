<?php

namespace Rakibdevs\MrzParser\Traits;

use DateTime;

trait DateFormatter
{
    public function formatDate($date, $format = "Y-m-d")
    {
        $dateTime = DateTime::createFromFormat('ymd', $date);

        return $dateTime->format($format);
    }
}
