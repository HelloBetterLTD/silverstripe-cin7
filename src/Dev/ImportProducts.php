<?php

namespace SilverStripers\Cin7\Dev;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductLoader;

class ImportProducts extends BuildTask
{

    //CurrentProductPage
    private static $segment = 'cin7-product-import';

    protected $title = 'Cin7:Import Products';

    protected $description = 'Import Products';

    public function run($request)
    {
        $conn = Cin7Connector::init();
        $products = $conn->getProducts();
        /* @var $loader ProductLoader */
        $loader = Injector::inst()->get(ProductLoader::class);
        foreach ($products as $product) {
            $loader->load($product);
        }



    }

}
