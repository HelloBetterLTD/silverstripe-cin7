<?php

namespace SilverStripers\Cin7\Model;

use SilverShop\Model\Variation\AttributeType as SS_AttributeType;
use SilverStripe\TagField\TagField;
use TractorCow\AutoComplete\AutoCompleteField;

class AttributeType extends SS_AttributeType
{

    public function getDropDownField($emptystring = null, $values = null)
    {
        $values = ($values) ? $values : $this->Values()->sort(['Sort' => 'ASC', 'Value' => 'ASC']);

        if ($values->exists()) {
            $field = TagField::create(
                'ProductAttributes',
                $this->Label,
                []
            );
            $field->setSource($this->Values());
            $field->setCanCreate(false);
            $field->setShouldLazyLoad(true);
            $field->setTitleField('Value');
            return $field;
        }

        return null;
    }

}
