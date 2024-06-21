<?php

namespace SilverStripers\Cin7\Extension;

use SilverShop\Model\Product\OrderItem;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Security;
use SilverStripers\Cin7\Model\PriceOption;

class OrderItemExtension extends DataExtension
{

    private static $db = [
        'MemberPriceColumn' => 'Varchar',
        'AppliedPriceColumn' => 'Varchar'
    ];

    public function updateUnitPrice(&$price)
    {
        /* @var $owner OrderItem */
        $owner = $this->owner;
        $order = $owner->Order();
        if (!$order->SplitParentID) {
            $member = $order->Member();
            $buyable = null;
            if (get_class($owner) === OrderItem::class) {
                $buyable = $owner->Product();
            } else {
                $buyable = $owner->ProductVariation();
            }

            $defaultPrice = PriceOption::get_default();
            $priceOptions = PriceOption::get();
            $matchedPriceOptions = null;
            if ($member && $member->exists()) {
                if ($col = $member->getAffectedPriceColumn()) {
                    $owner->MemberPriceColumn = $col;
                    $matchedPriceOptions = $priceOptions->where(
                        sprintf(
                            'LOWER(%s) = \'%s\'',
                            'Label',
                            strtolower($col)
                        )
                    );
                } else {
                    $groups = implode(',', array_merge([-1], $member->DirectGroups()->column('ID')));
                    $matchedPriceOptions = $priceOptions->where('(
                        EXISTS (
                            SELECT 1 FROM Cin7_PriceOption_Groups
                            WHERE
                                "Cin7_PriceOptionID" = "Cin7_PriceOption"."ID"
                                AND "GroupID" IN (' . $groups . ')
                                LIMIT 1
                        )
                    )');
                }
            }

            if ($matchedPriceOptions && $matchedPriceOptions->count()) {
                $priceOptions = $matchedPriceOptions;
            } else if ($defaultPrice) {
                $priceOptions = $priceOptions->filter('ID', $defaultPrice->ID);
            }

            if ($priceOptions->count()) {
                foreach ($priceOptions as $priceOption) {
                    $priceOptionPrice = $buyable->Prices()->find('PriceOptionID', $priceOption->ID);
                    if ($priceOptionPrice) {
                        $can = true;
                        if ($priceOption->MinQuantity && $priceOption->MinQuantity > $quantity) {
                            $can = false;
                        }
                        if ($priceOption->MaxQuantity && $priceOption->MaxQuantity < $quantity) {
                            $can = false;
                        }
                        if ($can) {
                            $owner->AppliedPriceColumn = $priceOption->Label;
                            $price = $priceOptionPrice->Price;
                            break;
                        }
                    }
                }
            } elseif ($defaultPrice) {
                $priceItem = $buyable
                    ->Prices()
                    ->find('PriceOption.ID', $defaultPrice->ID);
                if ($priceItem) {
                    $owner->MemberPriceColumn = '';
                    $owner->AppliedPriceColumn = $defaultPrice->Label;
                    $price = $priceItem->Price;
                }
            }
        }
    }


}
