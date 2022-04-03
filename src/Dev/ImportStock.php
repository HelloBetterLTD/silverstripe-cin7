<?php

namespace SilverStripers\Cin7\Dev;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\StockLoader;

class ImportStock extends BuildTask
{

    //CurrentProductPage
    private static $segment = 'cin7-stock-import';

    protected $title = 'Cin7:Import Stocks';

    protected $description = 'Import Stocks';

    public function run($request)
    {
        $conn = Cin7Connector::init();
        $stocks = $conn->getStocks();
        /* @var $loader StockLoader */
        $loader = Injector::inst()->get(StockLoader::class);
        foreach ($stocks as $stock) {
            $loader->load($stock);
        }
    }

}
