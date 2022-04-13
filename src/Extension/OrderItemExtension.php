<?php

namespace SilverStripers\Cin7\Extension;

use SilverShop\Model\Product\OrderItem;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Security;
use SilverStripers\Cin7\Model\PriceOption;

class OrderItemExtension extends DataExtension
{

    public function updateUnitPrice(&$price)
    {
        $owner = $this->owner;
        $buyable = null;
        if (get_class($owner) === OrderItem::class) {
            $buyable = $owner->Product();
        } else {
            $buyable = $owner->ProductVariation();
        }

        $priceOptions = PriceOption::get();
        if ($member = Security::getCurrentUser()) {
            $groups = implode(',', array_merge([-1], $member->DirectGroups()->column('ID')));
            $priceOptions = $priceOptions->where('(
                NOT EXISTS (
                    SELECT 1 FROM Cin7_PriceOption_Groups
                        WHERE "Cin7_PriceOptionID" = "Cin7_PriceOption"."ID"
                        LIMIT 1
                )
                OR  EXISTS (
                    SELECT 1 FROM Cin7_PriceOption_Groups
                    WHERE
                        "Cin7_PriceOptionID" = "Cin7_PriceOption"."ID"
                        AND "GroupID" IN (' . $groups . ')
                        LIMIT 1
                )
            )');
        } else {
            $priceOptions = $priceOptions->where('NOT EXISTS (
                SELECT 1 FROM Cin7_PriceOption_Groups
                    WHERE "Cin7_PriceOptionID" = "Cin7_PriceOption"."ID"
                    LIMIT 1
            )');
        }

        if ($priceOptions->count()) {
            $priceList = $buyable
                ->Prices()
                ->sort('Price')
                ->filter('PriceOption.ID', $priceOptions->column('ID'));

            $quantity = $owner->Quantity;
            foreach ($priceList as $priceItem) {
                $can = true;
                if ($priceItem->PriceOption()->MinQuantity && $priceItem->PriceOption()->MinQuantity > $quantity) {
                    $can = false;
                }
                if ($priceItem->PriceOption()->MaxQuantity && $priceItem->PriceOption()->MaxQuantity < $quantity) {
                    $can = false;
                }
                if ($can) {
                    $price = $priceItem->Price;
                    break;
                }
            }
        }
    }


}
