<?php

namespace SilverStripers\Cin7\Connector\Loader;

use SilverStripe\View\Parsers\URLSegmentFilter;
use SilverStripers\Cin7\Model\ProductCategory;

class ProductCategoryLoader extends Loader
{

    private static $dynamic_categories = true;

    public function load($data)
    {
        $hash = $this->getHash($data);
        $category = ProductCategory::get()->find('ExternalID', $data['id']);
        if (!$category) {
            $parent = null;
            if ($data['parentId']) {
                $parent = ProductCategory::get()->find('ExternalID', $data['parentId']);
            }
            $category = ProductCategory::create([
                'ExternalID' => $data['id'],
                'ParentID' => $parent ? $parent->ID : 0,
                'URLSegment' => URLSegmentFilter::create()->filter($data['name'])
            ]);
        }
        if ($category->Hash != $hash) {
            $category->update([
                'Title' => $data['name'],
                'IsActive' => $data['isActive'],
                'Sort' => $data['sort'],
                'Description' => $data['description'],
                'Hash' => $hash
            ]);
            $category->write();
        }
    }

}
