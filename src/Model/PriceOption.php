<?php

namespace SilverStripers\Cin7\Model;

use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;

class PriceOption extends DataObject
{

    private static $db = [
        'Label' => 'Varchar',
        'MinQuantity' => 'Int',
        'MaxQuantity' => 'Int',
    ];

    private static $many_many = [
        'Groups' => Group::class
    ];

    private static $table_name = 'Cin7_PriceOption';

    private static $labels = [];

    private static $summary_fields = [
        'Label',
        'MinQuantity',
        'MaxQuantity'
    ];

    public function getCMSFields() : FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Groups');
        $fields->addFieldToTab(
            'Root.Main',
            CheckboxSetField::create('Groups')
                ->setSource(Group::get()->sort('Title')->map()->toArray())
        );
        return $fields;
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        foreach (self::config()->get('labels') as $label) {
            $option = PriceOption::get()->find('Label', $label);
            if (!$option) {
                $option = PriceOption::create([
                    'Label' => $label
                ]);
                $option->write();
            }
        }
    }

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canEdit($member = null)
    {
        return true;
    }

    public function canView($member = null)
    {
        return true;
    }

}
