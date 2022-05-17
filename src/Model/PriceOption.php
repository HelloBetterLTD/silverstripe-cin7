<?php

namespace SilverStripers\Cin7\Model;

use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Group;

class PriceOption extends DataObject
{

    private static $db = [
        'Label' => 'Varchar',
        'Currency' => 'Varchar',
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

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $optionIds = PriceOption::get()->map('ID', 'ID')->toArray();
        foreach (self::config()->get('labels') as $config) {
            $option = PriceOption::get()->find('Label', $config['label']);
            if (!$option) {
                $option = PriceOption::create();
            }
            $option->update([
                'Label' => $config['label'],
                'Currency' => $config['currency'],
            ]);
            $option->write();
            unset($optionIds[$option->ID]);
        }

        // delete options
        foreach ($optionIds as $id) {
            if ($option = PriceOption::get()->byID($id)) {
               $option->delete();
            }
        }
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

    public function getCin7Label()
    {
        return lcfirst($this->Label) . $this->Currency;
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
