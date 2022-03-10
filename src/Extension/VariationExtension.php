<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\ORM\DataExtension;
use SilverStripers\Cin7\Model\Price;

class VariationExtension extends DataExtension
{

    private static $db = [
        'Title' => 'Varchar',
        'ExternalID' => 'Varchar',
        'WholesalePrice' => 'Currency(19,4)',
        'VipPrice' => 'Currency(19,4)',
        'SpecialPrice' => 'Currency(19,4)',
    ];

    private static $has_many = [
        'Prices' => Price::class
    ];

}
