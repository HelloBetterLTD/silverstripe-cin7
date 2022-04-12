<?php


namespace SilverStripers\Cin7\Extension;

use SilverStripe\ORM\DataExtension;

class MemberExtension extends DataExtension
{

    private static $db = [
        'Company' => 'Varchar',
        'PhoneNumber' => 'Varchar',
        'Mobile' => 'Varchar',
    ];

}
