<?php

namespace SilverStripers\Cin7\Model;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\ORM\DataObject;

class Price extends DataObject
{

    private static $tax_included = false;

    private static $tax_rate = 15;

    private static $db = [
        'Price' => 'Currency(19,4)',
    ];

    private static $has_one = [
        'Product' => DataObject::class,
        'Variation' => Variation::class,
        'PriceOption' => PriceOption::class
    ];

    private static $table_name = 'Cin7_Price';

    public function getPriceInclTax()
    {
        if (!self::config()->get('tax_included')) {
            return $this->Price * (100 + self::config()->get('tax_rate')) / 100;
        }
        return $this->Price;
    }

}
