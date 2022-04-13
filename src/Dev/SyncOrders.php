<?php

namespace SilverStripers\Cin7\Dev;

use SilverShop\Model\Order;
use SilverStripe\Dev\BuildTask;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripers\Cin7\Connector\Cin7Connector;

class SyncOrders extends BuildTask
{

    private static $segment = 'cin7-sync-orders';

    protected $title = 'Cin7:Sync Orders';

    protected $description = 'Sync orders with Cin7';

    public function run($request)
    {
        $config = SiteConfig::current_site_config();
        if ($config->SyncOrdersToAPI) {
            $orders = Order::get()->where(
                sprintf(
                    'ForceCin7Sync = 1 OR (Status IN (%s) AND ExternalID IS NULL)',
                    "'" . implode("', '", [
                        'Unpaid', 'Paid', 'Processing', 'Sent', 'Complete'
                    ]) . "'"
                )
            )->limit(3); // honor Cin7 throttle and process only three items
            $connector = Cin7Connector::init();
            foreach ($orders as $order) {
                $connector->syncOrder($order);
                sleep(1);
            }
        }
    }


}
