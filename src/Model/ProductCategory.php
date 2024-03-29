<?php

namespace SilverStripers\Cin7\Model;

use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use TractorCow\AutoComplete\AutoCompleteField;

class ProductCategory extends DataObject
{

    private static $db = [
        'Title' => 'Varchar',
        'ExternalID' => 'Int',
        'IsActive' => 'Boolean',
        'Sort' => 'Int',
        'Description' => 'Text',
        'Hash' => 'Varchar'
    ];

    private static $has_one = [
        'ProductCategory' => \SilverShop\Page\ProductCategory::class,
        'Parent' => ProductCategory::class
    ];

    private static $summary_fields = [
        'Title',
        'Parent.Title' => ['title' => 'Parent'],
        'ProductCategory.Title' => ['title' => 'Shop Category']
    ];

    private static $table_name = 'Cin7_ProductCategory';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName([
            'ParentID',
            'ProductCategory',
            'ExternalID',
            'Hash',
            'Sort'
        ]);
        $fields->addFieldsToTab('Root.Main', [
            AutoCompleteField::create('ParentID', 'Parent category')
                ->setSourceFields(['Title'])
                ->setSourceClass(ProductCategory::class),
            TreeDropdownField::create(
                'ProductCategoryID',
                'Shop Product Category',
                \SilverShop\Page\ProductCategory::class
            )
        ], 'Description');
        return $fields;
    }

    public function createProductCategoryPage()
    {
        if ($this->ProductCategoryID == 0) {
            $parentId = 0;
            if (($parent = $this->Parent()) && $parent->exists()) {
                $parentId = $parent->ProductCategoryID;
            }
            $page = \SilverShop\Page\ProductCategory::create([
                'ParentID' => $parentId,
                'Title' => $this->Title,
                'Sort' => $this->Sort
            ]);
            $page->writeToStage(Versioned::DRAFT);

            $this->ProductCategoryID = $page->ID;
            $this->write();
            return $page;
        }
        return $this->ProductCategory();
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
