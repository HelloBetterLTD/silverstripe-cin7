<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\ORM\DataExtension;

class ProductExtension extends DataExtension
{

    private static $db = [
        'ExternalID' => 'Int'
    ];

}
