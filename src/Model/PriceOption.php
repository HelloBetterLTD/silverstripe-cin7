<?php

namespace SilverStripers\Cin7\Model;

use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Group;
use SilverStripe\SiteConfig\SiteConfig;

class PriceOption extends DataObject
{

    private static $db = [
        'Label' => 'Varchar',
        'Default' => 'Boolean',
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

    public static function get_default()
    {
        return self::singleton()->getDefaultOption();
    }

    public function getDefaultOption()
    {
        return PriceOption::get()->find('Default', 1);
    }

    public function validate() : ValidationResult
    {
        $valid = parent::validate();
        if ($this->Default) {
            $anyothers = PriceOption::get()->exclude('ID', $this->ID)->filter('Default', 1);
            if ($anyothers->count()) {
                $valid->addFieldError('Default', 'There is another price option marked as default');
            }
        }
        return $valid;
    }

    public static function find_or_make($label)
    {
        $option = PriceOption::get()->find('Label', $label);
        if (!$option) {
            $option = PriceOption::create([
                'Label' => $label
            ]);
            $option->write();
        }
        return $option;
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
