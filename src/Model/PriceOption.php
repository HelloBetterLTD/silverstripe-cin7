<?php

namespace SilverStripers\Cin7\Model;

use SilverStripe\ORM\DataObject;

class PriceOption extends DataObject
{

    private static $db = [
        'Label' => 'Varchar',
        'MinQuantity' => 'Int',
        'MaxQuantity' => 'Int',
    ];

    private static $table_name = 'Cin7_PriceOption';

    private static $labels = [];

    private static $summary_fields = [
        'Label',
        'MinQuantity',
        'MaxQuantity'
    ];

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
