<?php

namespace SilverStripers\Cin7\Extension;

use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\AttributeValue;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class AttributeTypeExtension extends DataExtension
{

    const SIZE_LABEL = 'Size';
    const COLOUR_LABEL = 'Colors';

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->Label == self::SIZE_LABEL) {
            $fields->dataFieldByName('Label')->setReadonly(true);
        }
        if ($this->owner->Label == self::COLOUR_LABEL) {
            $fields->dataFieldByName('Label')->setReadonly(true);
        }
    }

    public function requireDefaultRecords()
    {
        self::find_or_make_size();
        self::find_or_make_color();
    }

    public static function get_size_type()
    {
        return self::find_or_make_size();
    }

    public static function get_color_type()
    {
        return self::find_or_make_color();
    }

    public static function find_or_make_size() : AttributeType
    {
        $type = AttributeType::get()->find('Label', self::SIZE_LABEL);
        if (!$type) {
            $type = AttributeType::create([
                'Label' => self::SIZE_LABEL
            ]);
            $type->write();
        }
        return $type;
    }

    public static function find_or_make_color() : AttributeType
    {
        $type = AttributeType::get()->find('Label', self::COLOUR_LABEL);
        if (!$type) {
            $type = AttributeType::create([
                'Label' => self::COLOUR_LABEL
            ]);
            $type->write();
        }
        return $type;
    }

    public static function find_or_make_size_attribute($size)
    {
        $type = self::get_size_type();
        $value = AttributeValue::get()->filter([
            'Value' => $size,
            'TypeID' => $type->ID
        ])->first();
        if (!$value) {
            $value = AttributeValue::create([
                'Value' => $size,
                'TypeID' => $type->ID
            ]);
            $value->write();
        }
        return $value;
    }

    public static function find_or_make_color_attribute($color)
    {
        $type = self::get_color_type();
        $value = AttributeValue::get()->filter([
            'Value' => $color,
            'TypeID' => $type->ID
        ])->first();
        if (!$value) {
            $value = AttributeValue::create([
                'Value' => $color,
                'TypeID' => $type->ID
            ]);
            $value->write();
        }
        return $value;
    }



}
