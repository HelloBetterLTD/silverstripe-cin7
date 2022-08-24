<?php

namespace SilverStripers\Cin7\Dev;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductCategoryLoader;

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
    }


}
