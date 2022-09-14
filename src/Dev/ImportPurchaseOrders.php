<?php

namespace SilverStripers\Cin7\Dev;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Versioned\Versioned;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductLoader;
use SilverStripers\Cin7\Model\PurchaseOrder;
use SilverStripers\Cin7\Model\PurchaseOrderLineItem;
use SilverStripers\Out\System\Log;

class ImportPurchaseOrders extends BuildTask
{

    //CurrentProductPage
    private static $segment = 'cin7-purchase-orders-import';

    protected $title = 'Cin7:Import Product Availability Dates';

    protected $description = 'Import Product Availability Dates from incoming purchase orders';

    private static $delay = 30;


    public function run($request)
    {
        set_time_limit(0);
        $config = SiteConfig::current_site_config();
        $stage = Versioned::get_stage();
        Versioned::set_stage(Versioned::DRAFT);
        $conn = Cin7Connector::init();

        $run = true;
        $page = 1;
        while($run) {
            Log::printLn('Querying purchase orders page: ' . $page);
            $pos = $conn->getPurchaseOrders($page, $config->POLastImported);

            foreach ($pos as $po) {
                $id = $po['id'];
                $hash = md5(json_encode($po));
                $order = PurchaseOrder::get()->find('ExternalID', $id);

                $etd = $po['estimatedDeliveryDate'];
                if (!$etd && $po['estimatedArrivalDate']) {
                    $etd = $po['estimatedArrivalDate'];
                }

                if (!$order || $order->ExternalHash != $hash) {

                    if (!$order) {
                        $order = new PurchaseOrder();
                    }
                    $order->update([
                        'ExternalHash' => $hash,
                        'ExternalID' => $po['id'],
                        'Reference' => $po['id'],
                        'Status' => $po['id'],
                        'Stage' => $po['id']
                    ]);

                    if ($etd) {
                        $order->EDT = $conn->cin7DateToDt($etd);
                    }
                    $order->write();


                    foreach ($po['lineItems'] as $li) {

                        $variation = null;
                        $product = Product::get()->find('ExternalID', $li['productId']);
                        if ($li['productId'] && $li['productOptionId'] && $li['sizeCodes']) {
                            $codes = explode('|', $li['sizeCodes']);
                            foreach ($codes as $code) {
                                $variation = Variation::get()->find('ExternalID', $li['productOptionId'] . '//' . $code);
                                if ($variation) {
                                    break;
                                }
                            }
                        }

                        if ($product) {

                            $item = PurchaseOrderLineItem::get()
                                ->filter([
                                    'ExternalID' => $li['id'],
                                    'PurchaseOrderID' => $order->ID
                                ])->first();

                            if (!$item) {
                                $item = new PurchaseOrderLineItem();
                            }
                            $item->update([
                                'ExternalID' => $li['id'],
                                'PurcahseOrderID' => $order->ID,
                                'Product' => $product ? $product->ID : 0,
                                'Variation' => $variation ? $variation->ID : 0,
                                'Quantity' => $li['qty']
                            ]);
                            $item->write();
                        }
                    }
                }
            }

            $page += 1;
            sleep(self::config()->get('delay')); // obey the throttle
            if (count($pos) < 20) {
                $run = false;
            }
        }

        $config->POLastImported = DBDatetime::now()->getValue();
        $config->write();
        Versioned::set_stage($stage);
    }

}
