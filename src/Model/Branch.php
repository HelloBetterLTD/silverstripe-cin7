<?php

namespace SilverStripers\Cin7\Model;

use SilverStripe\ORM\DataObject;

class Branch extends DataObject
{

    private static $db = [
        'ExternalID' => 'Varchar',
        'Hash' => 'Text',
        'BranchType' => 'Varchar',
        'StockControlOptions' => 'Varchar',
        'IsActive' => 'Boolean',
        'Company' => 'Varchar',
        'Mobile' => 'Varchar',
        'Phone' => 'Varchar',
        'Fax' => 'Varchar',
        'Email' => 'Varchar',
        'Address1' => 'Varchar',
        'Address2' => 'Varchar',
        'City' => 'Varchar',
        'State' => 'Varchar',
        'PostCode' => 'Varchar',
        'Country' => 'Varchar',
        'PostalAddress1' => 'Varchar',
        'PostalAddress2' => 'Varchar',
        'PostalCity' => 'Varchar',
        'PostalState' => 'Varchar',
        'PostalPostCode' => 'Varchar',
        'PostalCountry' => 'Varchar'
    ];

    private static $summary_fields = [
        'Company',
        'IsActive' => ['title' => 'Active'],
        'Mobile',
        'Phone',
        'Address1' => ['title' => 'Address 1'],
        'Address2' => ['title' => 'Address 2'],
        'City',
        'State',
        'PostCode' => ['title' => 'Post code'],
        'Country'
    ];

    private static $table_name = 'Cin7_Branch';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Hash');
        return $fields;
    }

}
