<?php

namespace SilverStripers\Cin7\Dev;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Versioned\Versioned;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductLoader;

class ImportPurchaseOrders extends BuildTask
{

    //CurrentProductPage
    private static $segment = 'cin7-purchase-orders-import';

    protected $title = 'Cin7:Import Product Availability Dates';

    protected $description = 'Import Product Availability Dates from incoming purchase orders';

    public function run($request)
    {
        $stage = Versioned::get_stage();
        Versioned::set_stage(Versioned::DRAFT);
        $conn = Cin7Connector::init();
        $page = $request->getVar('page') ? $request->getVar('page') : 0;
        $pos = $conn->getPurchaseOrders($page);
        foreach ($pos as $po) {
            $etd = $po['estimatedDeliveryDate'];
            if (!$etd && $po['estimatedArrivalDate']) {
                $etd = $po['estimatedArrivalDate'];
            }
            if ($etd) {
                foreach ($po['lineItems'] as $lineItem) {
                    $product = null;
                    if ($lineItem['productId'] && $lineItem['productOptionId']) {
                        $product = Variation::get()->find('ExternalID', $lineItem['productOptionId']);
                    } else {
                        $product = Product::get()->find('ExternalID', $lineItem['productId']);
                    }

                    if ($product) {
                        $product->NewStockETD = $conn->cin7DateToDt($etd);
                        $product->NewStockQty = $lineItem['qty'];
                        $product->write();
                        if ($product->isPublished()) { // publish only previously published products
                            $product->publishRecursive();
                        }
                    }
                }
            }
        }
        Versioned::set_stage($stage);
    }

}
