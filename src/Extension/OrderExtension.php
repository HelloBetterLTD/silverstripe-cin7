<?php

namespace SilverStripers\Cin7\Extension;

use SilverShop\Discounts\Model\Modifiers\OrderDiscountModifier;
use SilverShop\Model\Modifiers\Shipping\Base;
use SilverShop\Model\Order;
use SilverShop\Model\Variation\OrderItem;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataExtension;
use SilverStripers\Cin7\Model\Branch;

class OrderExtension extends DataExtension
{

    private static $db = [
        'ExternalID' => 'Varchar',
        'ForceCin7Sync' => 'Boolean',
        'PurchaseOrderNumber' => 'Varchar'
    ];

    private static $has_one = [
        'Branch' => Branch::class
    ];

    public function onStatusChange()
    {
        $this->owner->ForceCin7Sync = 1; // update Cin7 for any status changes
    }

    public function getProductTotal()
    {
        /* @var $order Order */
        $order = $this->owner;
        return $order->Items()->sum('CalculatedTotal');
    }

    public function getDiscountTotal()
    {
        /* @var $order Order */
        $order = $this->owner;
        $total = 0;
        $discount = OrderDiscountModifier::get()->filter('OrderID', $order->ID)->first();
        if ($discount && $discount->ShowInTable()) {
            $total = $discount->CalculatedTotal;
        }
        $this->owner->invokeWithExtensions('updateDiscountTotal', $total);
        return $total;
    }

    public function getDiscountDescription()
    {
        /* @var $order Order */
        $order = $this->owner;
        $desc = '';
        $discount = OrderDiscountModifier::get()->filter('OrderID', $order->ID)->first();
        if ($discount && $discount->ShowInTable()) {
            $desc = $discount->Title;
        }
        $this->owner->invokeWithExtensions('updateDiscountDescription', $desc);
        return $desc;
    }


    public function getFreightTotal()
    {
        /* @var $order Order */
        $order = $this->owner;
        $total = 0;
        $shipping = Base::get()->filter('OrderID', $order->ID)->first();
        if ($shipping) {
            $total = $shipping->CalculatedTotal;
        }
        $this->owner->invokeWithExtensions('updateFreightTotal', $total);
        return $total;
    }

    public function getFreightDescription()
    {
        /* @var $order Order */
        $order = $this->owner;
        $desc = '';
        $shipping = Base::get()->filter('OrderID', $order->ID)->first();
        if ($shipping) {
            $desc = $shipping->Title;
        }
        $this->owner->invokeWithExtensions('updateFreightDescription', $desc);
        return $desc;
    }

    public function getSurchargeDescription()
    {
        $desc = null;
        $this->owner->invokeWithExtensions('updateSurchargeDescription', $desc);
        return $desc;
    }

    public function getSurcharge()
    {
        $ret = 0;
        $this->owner->invokeWithExtensions('updateSurcharge', $ret);
        return $ret;
    }

    public function getCin7OrderStatus()
    {
        $order = $this->owner;
        if (in_array($order->Status, ['Unpaid','Paid','Processing','Sent','Complete'])) {
            return 'Approved';
        }
        if (in_array($order->Status, ['AdminCancelled','MemberCancelled'])) {
            return 'Void';
        }
        return 'Draft';
    }

    public function toCin7()
    {
        /* @var $order Order */
        $order = $this->owner;
        $member = $order->Member();

        $shipping = $order->ShippingAddress();
        $billing = $order->BillingAddress();
        $lineItems = [];
        $count = 0;
        foreach ($order->Items() as $orderItem) {
            $count += 1;
            $product = $orderItem->Product();
            $buyable = get_class($orderItem) === OrderItem::class ? $orderItem->ProductVariation() : $orderItem->Product();

            $lineItems[] = [
                // 'id' => $orderItem->ID,
                'styleCode' => ClassInfo::hasMethod($buyable, 'getColorCode') ? $buyable->getColorCode() : '',
                'sizeCodes' => ClassInfo::hasMethod($buyable, 'getSizeCode') ? $buyable->getSizeCode() : '',
                'createdDate' => $order->dbObject('Placed')->Cin7Date(),
                'transactionId' => $order->Reference,
                'parentId' => null, //
                'productId' => $product->ExternalID,
                'productOptionId' => ClassInfo::hasMethod($buyable, 'getProductOptionId') ? $buyable->getProductOptionId() : '',
                'integrationRef' => $buyable->ExternalID,
                'sort' => $count,
                'code' => $buyable->InternalItemID,
                'name' => $product->Title,
                'qty' => $orderItem->Quantity,
                'barcode' => $buyable->Barcode,
                'unitCost' => $orderItem->UnitPrice,
                'unitPrice' => $orderItem->UnitPrice,
                'option1' => ClassInfo::hasMethod($buyable, 'getColorCode') ? $buyable->getColorCode() : '',
                'option2' => ClassInfo::hasMethod($buyable, 'getSizeCode') ? $orderItem->Quantity . ' x ' .$buyable->getSizeCode() : '',
//                'discount' => 0, // TODO
//                'holdingQty' => 0,
//                'accountCode' => 0,
//                'stockControl' => 0,
            ];
        }

        $data = [];
        $data[] = [
            'id' => $order->ExternalID ? $order->ExternalID : null, //TODO:
            'createdDate' => $order->dbObject('Placed')->Cin7Date(),
            'modifiedDate' => $order->dbObject('LastEdited')->Cin7Date(),
            'createdBy' => null,
            'processedBy' => null,
            'isApproved' => true,
            'reference' => $order->Reference,
            'memberId' => '',
            'memberEmail' => $member->exists() ? $member->Email : $order->Email,
            'firstName' => $member->exists() ? $member->FirstName : $order->FirstName,
            'lastName' => $member->exists() ? $member->Surname : $order->Surname,
            'email' => $member->exists() ? $member->Email : $order->Email,
            'company' => $member->exists() ? $member->Company : '',
            'phone' => $member->exists() ? $member->PhoneNumber : '',
            'mobile' => $member->exists() ? $member->Mobile : '',
            'deliveryFirstName' => $shipping->FirstName ? : ($member->exists() ? $member->FirstName : $order->FirstName),
            'deliveryLastName' => $shipping->Surname ? : ($member->exists() ? $member->Surname : $order->Surname),
            'deliveryCompany' => $shipping->Company ? : ($member->exists() ? $member->Company : ''),
            'deliveryAddress1' => $shipping->Address,
            'deliveryAddress2' => $shipping->AddressLine2,
            'deliveryCity' => $shipping->City,
            'deliveryState' => $shipping->State,
            'deliveryPostalCode' => $shipping->PostalCode,
            'deliveryCountry' => $shipping->Country,
            'deliveryPostalCode' => $shipping->PostalCode,
            'deliveryPostalCode' => $shipping->PostalCode,


            'billingFirstName' => $billing->FirstName ? : ($member->exists() ? $member->FirstName : $order->FirstName),
            'billingLastName' => $billing->Surname ? : ($member->exists() ? $member->Surname : $order->Surname),
            'billingCompany' => $billing->Company ? : ($member->exists() ? $member->Company : ''),
            'billingAddress1' => $billing->Address,
            'billingAddress2' => $billing->AddressLine2,
            'billingCity' => $billing->City,
            'deliveryState' => $billing->State,
            'deliveryPostalCode' => $billing->PostalCode,
            'deliveryCountry' => $billing->Country,
            'billingPostalCode' => $billing->PostalCode,
            'billingState' => $billing->State,
            'billingCountry' => $billing->Country,
            'branchId' => $order->Branch()->exists() ? $order->Branch()->ExternalID : null,
            'branchEmail' => null,
            'projectName' => null,
            'trackingCode' => null,
            'internalComments' => $order->Notes,
            'productTotal' => $this->getProductTotal(),
            'freightTotal' => $this->getFreightTotal(),
            'freightDescription' => $this->getFreightDescription(),
            'surcharge' => $this->getSurcharge(),
            'surchargeDescription' => $this->getSurchargeDescription(),
            'discountTotal' => $this->getDiscountTotal(),
            'discountDescription' => $this->getDiscountDescription(),
            'total' => $order->Total,
            'currencyCode' => 'NZD',
            'currencyRate' => $order->Total,
            'currencySymbol' => '$',
            'taxStatus' => null,
            'taxRate' => 0, // TODO: tax
            'source' => 'Websale',
            'isVoid' => in_array($order->Status, ['AdminCancelled', 'MemberCancelled']),
            'memberCostCenter' => null, // TODO:
            'memberAlternativeTaxRate' => null, // TODO:
            'costCenter' => null, //TODO
            'alternativeTaxRate' => null,
            'estimatedDeliveryDate' => null, //
            'salesPersonId' => null, //
            'salesPersonEmail' => null, //
            'paymentTerms' => null, //
            'salesPersonEmail' => null, //
            'customerOrderNo' => $order->PurchaseOrderNumber, //
            'voucherCode' => null, //
            'deliveryInstructions' => null, //
            'status' => $this->getCin7OrderStatus(), //
            'stage' => null, //
            'invoiceDate' => null, //
            'invoiceNumber' => $order->Reference,
            'dispatchedDate' => null, //
            'logisticsCarrier' => null, //
            'logisticsStatus' => null, //
            'distributionBranchId' => $order->Branch()->ExternalID, //
            'lineItems' => $lineItems
        ];

        $order->invokeWithExtensions('updateToCin7', $data);
        return $data;

    }

}
