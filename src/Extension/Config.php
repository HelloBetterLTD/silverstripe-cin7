<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripers\Cin7\Model\ProductCategory;

class Config extends DataExtension
{

    private static $db = [
        'APIUsername' => 'Varchar',
        'APIConnectionKey' => 'Varchar',

        'DevAPIUsername' => 'Varchar',
        'DevAPIConnectionKey' => 'Varchar',

        'CurrentProductPage' => 'Int'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Cin7.Main', [
            HeaderField::create('Cin7DevAPI', 'Dev api credentials')->setHeadingLevel(3),
            TextField::create('DevAPIUsername', 'API Username'),
            TextField::create('DevAPIConnectionKey', 'API Username'),
            HeaderField::create('Cin7API', 'Production api credentials')->setHeadingLevel(3),
            TextField::create('APIUsername', 'API Username'),
            TextField::create('APIConnectionKey', 'API Username'),
        ]);

        $fields->addFieldsToTab('Root.Cin7.Categories', [
            GridField::create('Cin7Categories', 'Categories')
                ->setList(ProductCategory::get())
                ->setConfig(GridFieldConfig_RecordEditor::create())
        ]);
    }

}
