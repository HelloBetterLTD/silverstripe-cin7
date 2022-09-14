<?php

namespace SilverStripers\Cin7\Model;

use SilverStripe\ORM\DataObject;

class PurchaseOrder extends DataObject
{

    private static $db = [
        'ExternalHash' => 'Varchar',
        'ExternalID' => 'Int',
        'Reference' => 'Varchar',
        'EDT' => 'Datetime',
        'Status' => 'Varchar',
        'Stage' => 'Varchar'
    ];

    private static $has_many = [
        'Items' => PurchaseOrderLineItem::class
    ];

    private static $table_name = 'Cin7_PurchaseOrder';

}
