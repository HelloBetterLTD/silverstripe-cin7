<?php

namespace SilverStripers\Cin7\Model;

use SilverShop\Page\Product;
use SilverStripe\ORM\DataObject;

class PurchaseOrderLineItem extends DataObject
{

    private static $db = [
        'ExternalID' => 'Int',
        'Quantity' => 'Int'
    ];

    private static $has_one = [
        'Product' => Product::class,
        'Variation' => \SilverShop\Model\Variation\Variation::class,
        'PurchaseOrder' => PurchaseOrder::class
    ];

    private static $table_name = 'Cin7_PurchaseOrderLineItem';

}
