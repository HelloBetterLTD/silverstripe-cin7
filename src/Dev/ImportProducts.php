<?php

namespace SilverStripers\Cin7\Dev;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductLoader;
use SilverStripers\Out\System\Log;

class ImportProducts extends BuildTask
{

    //CurrentProductPage
    private static $segment = 'cin7-product-import';

    protected $title = 'Cin7:Import Products';

    protected $description = 'Import Products';

    public function run($request)
    {
        set_time_limit(0);
        $config = SiteConfig::current_site_config();
        $conn = Cin7Connector::init();
        /* @var $loader ProductLoader */
        $loader = Injector::inst()->get(ProductLoader::class);

        $run = true;
        $page = 1;
        while($run) {
            Log::printLn('Querying product page: ' . $page);
            $products = $conn->getProducts($page, $config->ProductLastImported);
            foreach ($products as $product) {
                Log::printLn('Imported product ' . $data['id']);
                $loader->load($product);
            }
            $page += 1;
            sleep(2); // obey the throttle
            if (count($products) < 20) {
                $run = false;
            }
        }

        $config->ProductLastImported = DBDatetime::now()->getValue();
        $config->write();
    }

}
