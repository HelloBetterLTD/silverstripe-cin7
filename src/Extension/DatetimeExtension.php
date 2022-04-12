<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\Core\Extension;

class DatetimeExtension extends Extension
{

    public function Cin7Date()
    {
        return sprintf(
            '%sT%s',
            $this->owner->Format('yyyy-MM-dd'),
            $this->owner->Format('kk:mm:ssZZZZZ')
        );
    }

}
