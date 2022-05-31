<?php

namespace SilverStripers\Cin7\Model;

use SilverShop\Page\Product;
use SilverStripe\ORM\DataObject;

class Stock extends DataObject
{

    private static $db = [
        'Available' => 'Int',
        'StockOnHand' => 'Int',
        'ETD' => 'Datetime'
    ];

    private static $has_one = [
        'Product' => Product::class,
        'Variation' => \SilverShop\Model\Variation\Variation::class,
        'Branch' => Branch::class
    ];

    private static $table_name = 'Cin7_Stock';

}
