<?php

namespace SilverStripers\Cin7\Dev;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Versioned\Versioned;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductLoader;
use SilverStripers\Cin7\Extension\AttributeTypeExtension;
use SilverStripers\Cin7\Model\PurchaseOrder;
use SilverStripers\Cin7\Model\PurchaseOrderLineItem;
use SilverStripers\Out\System\Log;

class ImportPurchaseOrders extends BuildTask
{

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
            if (count($pos)) {
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
                            $product = Product::get()->find('ExternalID', $li['productId']);
                            if (!empty($li['option1']) && !empty($li['option3']) && $product) {
                                $color = AttributeTypeExtension::find_or_make_color_attribute($li['option1']);
                                $contents = explode("\n", $li['option3']);


                                foreach ($contents as $contentItem) {
                                    $item = PurchaseOrderLineItem::get()
                                        ->filter([
                                            'ExternalID' => $li['id'],
                                            'IdentifierData' => $contentItem,
                                            'PurchaseOrderID' => $order->ID
                                        ])->first();

                                    $parts = explode(' x ', $contentItem);
                                    $size = AttributeTypeExtension::find_or_make_size_attribute($parts[1]);

                                    $variation = Variation::get()
                                        ->filter([
                                            'ProductID' => $product->ID,
                                            'ExternalID' => $li['productOptionId'] . '//' . trim($parts[1])
                                        ])->first();

                                    if (!$item) {
                                        $item = new PurchaseOrderLineItem();
                                    }

                                    $item->update([
                                        'ExternalID' => $li['id'],
                                        'IdentifierData' => $contentItem,
                                        'PurchaseOrderID' => $order->ID,
                                        'ProductID' => $product ? $product->ID : 0,
                                        'VariationID' => $variation ? $variation->ID : 0,
                                        'Quantity' => $parts[0]
                                    ]);
                                    $item->write();
                                }
                            }
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
