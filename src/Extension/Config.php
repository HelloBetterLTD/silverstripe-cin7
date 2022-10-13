<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripers\Cin7\Model\Branch;
use SilverStripers\Cin7\Model\PriceOption;
use SilverStripers\Cin7\Model\ProductCategory;

class Config extends DataExtension
{

    private static $db = [
        'APIUsername' => 'Varchar',
        'APIConnectionKey' => 'Varchar',

        'DevAPIUsername' => 'Varchar',
        'DevAPIConnectionKey' => 'Varchar',

        'ProductLastImported' => 'Datetime',
        'StockLastImported' => 'Datetime',
        'POLastImported' => 'Datetime',
        'OrdersLastImported' => 'Datetime',

        'SyncOrdersToAPI' => 'Boolean'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Cin7.Main', [
            CheckboxField::create('SyncOrdersToAPI', 'Sync Orders to Cin7 API?'),
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
                ->setConfig(GridFieldConfig_RecordEditor::create(50))
        ]);

        $fields->addFieldsToTab('Root.Cin7.PricingOptions', [
            GridField::create('Cin7Pricing', 'Pricing Options')
                ->setList(PriceOption::get())
                ->setConfig(GridFieldConfig_RecordEditor::create(50))
        ]);

        $fields->addFieldsToTab('Root.Cin7.Branches', [
            GridField::create('Branches', 'Branches')
                ->setList(Branch::get())
                ->setConfig(GridFieldConfig_RecordEditor::create(50))
        ]);
    }

}
