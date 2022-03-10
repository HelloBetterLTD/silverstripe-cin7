<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\ORM\DataExtension;

class FileExtension extends DataExtension
{

    private static $db = [
        'ExternalHash' => 'Varchar'
    ];

}
