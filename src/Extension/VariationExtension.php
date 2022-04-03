<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripers\Cin7\Form\Field\PricingOptionsField;
use SilverStripers\Cin7\Model\Price;

class VariationExtension extends DataExtension
{

    private static $db = [
        'Title' => 'Varchar',
        'ExternalID' => 'Varchar',
        'Barcode' => 'Varchar',
        'WholesalePrice' => 'Currency(19,4)',
        'VipPrice' => 'Currency(19,4)',
        'SpecialPrice' => 'Currency(19,4)',
        'StockAvailable' => 'Int',
        'StockOnHand' => 'Int',
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

}
