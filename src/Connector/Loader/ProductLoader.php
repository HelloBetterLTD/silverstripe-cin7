<?php

namespace SilverStripers\Cin7\Connector\Loader;

use SilverShop\Page\Product;
use SilverStripers\Cin7\Model\ProductCategory;

class ProductLoader extends Loader
{

    private function findProduct($data)
    {
        return Product::get()->find('ExternalID', $data['id']);
    }

    private function createNewProduct($data)
    {

        $ids = $data['categoryIdArray'];
        $mainCategoryID = array_shift($ids);



        $product = Product::create([
            'ExternalID' => $data['id'],
            'ParentID' => $parentID
        ]);
        $product->write();



    }

    private function canImportProduct($data)
    {
        $ids = $data['categoryIdArray'];
        $categories = ProductCategory::get()
            ->filter('ID', $ids)
            ->where('ProductCategoryID IS NOT NULL');
        return $categories->count() > 0;
    }


    public function load($data)
    {
        if ($this->canImportProduct($data)) {
            echo 'Import product ' . $data['name'] . '<br>';
        }
    }

}
