<?php

namespace SilverStripers\Cin7\Dev;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\StockLoader;
use SilverStripers\Out\System\Log;

class ImportStock extends BuildTask
{

    //CurrentProductPage
    private static $segment = 'cin7-stock-import';

    protected $title = 'Cin7:Import Stocks';

    protected $description = 'Import Stocks';

    private static $delay = 30;

    public function run($request)
    {
        set_time_limit(0);
        $conn = Cin7Connector::init();
        $config = SiteConfig::current_site_config();
        $stocks = $conn->getStocks();
        /* @var $loader StockLoader */
        $loader = Injector::inst()->get(StockLoader::class);

        $run = true;
        $page = 1;
        while($run) {
            Log::printLn('Querying stock page ' . $page);
            $products = $conn->getStocks($page, $config->StockLastImported);
            foreach ($products as $product) {
                Log::printLn('Importing stock');
                $loader->load($product);
            }
            $page += 1;
            sleep(self::config()->get('delay')); // obey the throttle
            if (count($products) < 250) {
                $run = false;
            }
        }
        $config->StockLastImported = DBDatetime::now()->getValue();
        $config->write();
    }

}
