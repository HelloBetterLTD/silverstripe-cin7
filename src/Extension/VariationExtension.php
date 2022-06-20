<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripers\Cin7\Form\Field\PricingOptionsField;
use SilverStripers\Cin7\Model\Price;

class VariationExtension extends DataExtension
{
    const PRIMARY = 'Primary';
    const ACTIVE = 'Active';

    private static $db = [
        'Title' => 'Varchar',
        'Status' => 'Varchar',
        'ExternalID' => 'Varchar',
        'Barcode' => 'Varchar',
        'WholesalePrice' => 'Currency(19,4)',
        'VipPrice' => 'Currency(19,4)',
        'SpecialPrice' => 'Currency(19,4)',
        'StockAvailable' => 'Int',
        'StockOnHand' => 'Int',
        'NewStockETD' => 'Datetime',
        'NewStockQty' => 'Int'
    ];

    private static $has_many = [
        'Prices' => Price::class
    ];

    public function updateCMSFields(FieldList $fields) : void
    {
        $fields->addFieldToTab(
            'Root.AdvancedPricing',
            PricingOptionsField::create('PricingOptions', 'Prices')
                ->setBuyable($this->owner)
        );
    }

    public function getSizeCode()
    {
        $sizeType = AttributeTypeExtension::find_or_make_size();
        if ($val = $this->owner->AttributeValues()->find('TypeID', $sizeType->ID)) {
            return $val->Value;
        }
    }

    public function getColorCode()
    {
        $colorType = AttributeTypeExtension::find_or_make_color();
        if ($val = $this->owner->AttributeValues()->find('TypeID', $colorType->ID)) {
            return $val->Value;
        }
    }

}
