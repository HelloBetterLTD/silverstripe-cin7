<?php

namespace SilverStripers\Cin7\Model;

use SilverStripe\ORM\DataObject;

class ProductCategory extends DataObject
{

    private static $db = [
        'Title' => 'Varchar',
        'ExternalID' => 'Int',
        'IsActive' => 'Boolean',
        'Sort' => 'Int',
        'Description' => 'Text',
        'Hash' => 'Varchar'
    ];

    private static $has_one = [
        'ProductCategory' => \SilverShop\Page\ProductCategory::class,
        'Parent' => ProductCategory::class
    ];

    private static $table_name = 'Cin7_ProductCategory';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName([
            'ParentID',
            'ProductCategory',
            'Hash',
            'Sort'
        ]);
//        $fields->addFieldsToTab('Root.Main', [
//            AutoCompl
//        ])
        return $fields;
    }

    public function canEdit($member = null)
    {
        return true;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canView($member = null)
    {
        return true;
    }


}
