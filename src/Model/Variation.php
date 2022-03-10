<?php


namespace SilverStripers\Cin7\Model;

use SilverShop\Model\Variation\Variation as SS_Variation;

class Variation extends SS_Variation
{

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $attributes = $this->Product()->VariationAttributeTypes();
        if ($attributes->exists()) {
            foreach ($attributes as $attribute) {
                if ($field = $fields->dataFieldByName('ProductAttributes[' . $attribute->ID . ']')) {
                    if ($value = $this->AttributeValues()) {
                        $field->setValue($value->column('ID'));
                    }
                }
            }
        }

        return $fields;
    }

}
