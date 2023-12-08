<?php

namespace SilverStripers\Cin7\Dev;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductCategoryLoader;
use SilverStripers\Cin7\Model\ProductCategory;

class ImportProductCategories extends BuildTask
{

    private static $segment = 'cin7-product-categories-import';

    protected $title = 'Cin7:Import Product Categories';

    protected $description = 'Import Product Categories';

    public function run($request)
    {
        $conn = Cin7Connector::init();
        $productCategories = $conn->getProductCategories();
        /* @var $loader ProductCategoryLoader */
        $loader = Injector::inst()->get(ProductCategoryLoader::class);
        foreach ($productCategories as $productCategory) {
            $loader->load($productCategory);
        }


        if (ProductCategoryLoader::config()->get('dynamic_categories')) {
            $this->createCategoryPages();
        }
    }

    public function createCategoryPages($ids = [])
    {
        $categories = ProductCategory::get()
            ->filter([
                'ParentID' => count($ids) ? $ids : 0
            ]);
        if ($categories->count()) {
            foreach ($categories as $category) {
                $category->createProductCategoryPage();
            }
            $this->createCategoryPages($categories->column());
        }
    }


}
