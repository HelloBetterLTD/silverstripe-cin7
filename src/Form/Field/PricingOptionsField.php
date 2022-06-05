<?php

namespace SilverStripers\Cin7\Form\Field;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\View\ArrayData;
use SilverStripers\Cin7\Model\Price;
use SilverStripers\Cin7\Model\PriceOption;

class PricingOptionsField extends FormField
{

    private $buyable = null;

    public function setBuyable($buyable)
    {
        $this->buyable = $buyable;
        return $this;
    }

    public function getPricingOptions()
    {
        return PriceOption::get();
    }

    public function getPriceList()
    {
        if ($this->buyable) {
            if (is_a($this->buyable, Variation::class)) {
                return Price::get()->filter('VariationID', $this->buyable->ID);
            } else if (is_a($this->buyable, Product::class)) {
                return Price::get()->filter([
                    'ProductID' => $this->buyable->ID,
                    'VariationID' => 0
                ]);
            }
        }
        return null;
    }

    public function ProcessedPriceOptions()
    {
        $list = $this->getPriceList();
        $ret = new ArrayList();
        foreach (PriceOption::get() as $option) {
            $price = ($list) ? $list->find('PriceOptionID', $option->ID) : null;
            $ret->push(ArrayData::create([
                'Title' => $option->Label,
                'Value' => $price ? $price->Price : '',
                'Name' => sprintf('%s[%s]', $this->getName(), $option->ID)
            ]));
        }
        return $ret;
    }

    public function saveInto(DataObjectInterface $record)
    {
        if ($this->readonly) {
            return;
        }

        $buyable = $this->buyable;
        if (!$buyable->ID) {
            $buyable->write();
        }

        $fieldName = 'ProductID';
        if (is_a($this->buyable, Variation::class)) {
            $fieldName = 'VariationID';
        }

        $values = $this->Value();
        $list = $this->getPriceList();
        foreach (PriceOption::get() as $option) {
            $price = ($list) ? $list->find('PriceOptionID', $option->ID) : null;
            if (empty($values[$option->ID])) {
                if ($price) {
                    $price->delete();
                }
            } else {
                if ($price) {
                    $price->Price = $values[$option->ID];
                } else {
                    $price = Price::create([
                        $fieldName => $buyable->ID,
                        'PriceOptionID' => $option->ID,
                        'Price' => $values[$option->ID],
                    ]);
                }
                $price->write();
            }
        }
    }

    public function performReadonlyTransformation()
    {
        $clone = clone $this;
        $clone->setReadonly(true);
        return $clone;
    }

}
