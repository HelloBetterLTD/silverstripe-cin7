<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductLoader;
use SilverStripers\Cin7\Form\Field\PricingOptionsField;
use SilverStripers\Cin7\Model\Price;

class ProductExtension extends DataExtension
{

    private static $db = [
        'ExternalHash' => 'Varchar',
        'ExternalID' => 'Int',
        'Brand' => 'Varchar',
        'StyleCode' => 'Varchar',
        'CustomFields' => 'Text', // JSON
        'NewStockETD' => 'Datetime',
        'NewStockQty' => 'Int',
        'Volume' => 'Decimal(12, 5)',
        'Cin7Categories' => 'Text'
    ];

    private static $has_many = [
        'Prices' => Price::class
    ];

    private static $many_many = [
        'Images' => Image::class
    ];

    private static $many_many_extraFields = [
        'Images' => [
            'SortOrder' => 'Int'
        ]
    ];

    public function updateCMSFields(FieldList $fields) : void
    {
        $product = $this->owner;
        $fields->removeByName([
            'Prices',
            'Volume',
            'Cin7Categories'
        ]);
        $fields->addFieldToTab('Root.Images', UploadField::create('Images'));

        $fields->insertAfter('Depth', TextField::create('Volume'));

        if (!$product->Variations()->count()) {
            $fields->addFieldToTab(
                'Root.Pricing',
                PricingOptionsField::create('PricingOptions', 'Prices')
                    ->setBuyable($this->owner)
            );
        }

    }

    public function getCustomField($fieldName)
    {
        $product = $this->owner;
        if ($product->CustomFields) {
            $data = json_decode($product->CustomFields, true);
            if (!empty($data[$fieldName])) {
                return $data[$fieldName];
            }
        }
        return null;
    }

    public function syncWithCin7()
    {
        $data = Cin7Connector::init()->getProductData($this->owner->ExternalID);
        $loader = ProductLoader::create();
        $loader->load($data, true);
    }

    public function updateCMSActions(FieldList $fields)
    {
        $fields->insertAfter(
            'action_publish',
            FormAction::create('doSyncWithAPI', 'Sync with Cin7')
                ->addExtraClass('btn btn-outline-primary')
                ->setUseButtonTag(true)
        );
    }

}
