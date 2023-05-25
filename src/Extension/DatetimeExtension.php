<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\Core\Extension;

class DatetimeExtension extends Extension
{

    public function Cin7Date()
    {

        $time = $this->owner->Value;
        $nzst = new \DateTimeZone('Pacific/Auckland');
        $utc = new \DateTimeZone('UTC');
        $dateNZ = new \DateTime($time, $nzst);
        $dateUTC = $dateNZ->setTimezone($utc);
        $utcDate = sprintf(
            '%sT%s+12:00',
            $dateUTC->format('Y-m-d'),
            $dateUTC->format('H:i:s')
        );
        return $utcDate;


        return sprintf(
            '%sT%s',
            $this->owner->Format('yyyy-MM-dd'),
            $this->owner->Format('HH:mm:ssZZZZZ')
        );
    }

}
