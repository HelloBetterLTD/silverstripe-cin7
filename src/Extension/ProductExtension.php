<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class ProductExtension extends DataExtension
{

    private static $db = [
        'ExternalHash' => 'Varchar',
        'ExternalID' => 'Int',
        'Brand' => 'Varchar'
    ];

    private static $many_many = [
        'Images' => Image::class
    ];

    private static $many_many_extraFields = [
        'Images' => [
            'SortOrder' => 'Int'
        ]
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Images', UploadField::create('Images'));
    }

}
