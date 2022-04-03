<?php

namespace SilverStripers\Cin7\Connector\Loader;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripers\Cin7\Model\Branch;
use SilverStripers\Cin7\Model\Stock;

class StockLoader extends Loader
{

    public function load($data)
    {
        $branch = Branch::get()->find('ExternalID', $data['branchId']);
        $product = Product::get()->find('ExternalID', $data['productId']);
        $variation = Variation::get()->find('ExternalID', $data['productOptionId']);
        if ($branch && $product) {
            $filters = [
                'BranchID' => $branch->ID,
                'ProductID' => $product->ID
            ];
            if ($data['productOptionId'] && $variation) {
                $filters['VariationID'] = $variation->ID;
                $stock = Stock::get()->filter($filters)->first();
                if (!$stock) {
                    $stock = Stock::create($filters);
                }
                $stock->Available = $data['available'];
                $stock->StockOnHand = $data['stockOnHand'];
                $stock->write();
            }
        }
    }

}
