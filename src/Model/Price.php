<?php

namespace SilverStripers\Cin7\Model;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\ORM\DataObject;

class Price extends DataObject
{

    private static $db = [
        'Price' => 'Currency(19,4)',
    ];

    private static $has_one = [
        'Product' => Product::class,
        'Variation' => Variation::class,
        'PriceOption' => PriceOption::class
    ];

    private static $table_name = 'Cin7_Price';

}
