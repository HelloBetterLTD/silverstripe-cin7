<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\Core\Extension;

class DatetimeExtension extends Extension
{

    public static function get_offset()
    {
        $offset = date('Z');
        $hours = ($offset / 3600);
        $h = str_pad(intval($hours), 2, '0', STR_PAD_LEFT);
        $m = str_pad(($hours - intval($hours)) * 60, 2, '0', STR_PAD_LEFT);
        return sprintf(
            '%s%s:%s',
            $hours > 0 ? '+' : '-',
            $h,
            $m
        );
    }

    public function Cin7Date()
    {
        $dateString = $this->owner->Value;
        $date = new \DateTime($dateString);
        $offset = self::get_offset();
        $cin7Date = $date->format('Y-m-d\TH:i:s') . $offset;
        return $cin7Date;
    }

}
