<?php


namespace SilverStripers\Cin7\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripers\Cin7\Connector\Cin7Connector;

class MemberExtension extends DataExtension
{

    private static $db = [
        'Company' => 'Varchar',
        'PhoneNumber' => 'Varchar',
        'Mobile' => 'Varchar',
        'ExternalID' => 'Int',
        'PriceColumn' => 'Varchar',
        'Cin7Data' => 'Text',
        'LastSynced' => 'Datetime'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'Cin7Data'
        ]);
    }

    public function toCin7()
    {
        $member = $this->owner;
        $data = [
            'id' => $member->ExternalID ? $member->ExternalID : null,
            'type' => 'Customer',
            'billingCompany' => $member->Company,
            'firstName' => $member->FirstName,
            'lastName' => $member->Surname,
            'accountsFirstName' => $member->FirstName,
            'accountsLastName' => $member->Surname,
            'billingEmail' => $member->Email,
            'accountsPhone' => $member->PhoneNumber,
            'phone' => $member->PhoneNumber,
            'mobile' => $member->Mobile,
        ];
        $member->invokeWithExtensions('updateToCin7', $data);
        return $data;
    }

    public function syncToCin7()
    {
        $member = $this->owner;
        $connector = Cin7Connector::init();
        return $connector->syncMember($member);
    }

    public function syncFromCin7()
    {
        $member = $this->owner;
        $connector = Cin7Connector::init();
        return $connector->copyMember($member);
    }

    public function afterMemberLoggedIn()
    {
        try {
            if ($account = $this->owner->getAccount()) {

                $lastChecked = $account->PriceColumnLastChecked;
                if ($lastChecked && strtotime($lastChecked) > strtotime('-1 day')) {
                    return;
                }

                $this->syncFromCin7();

            }
        } catch (\Exception $e) {}
    }

    public function getAffectedPriceColumn()
    {
        $member = $this->owner;
        $col = $member->PriceColumn;
        $member->invokeWithExtensions('updateAffectedPriceColumn', $col);
        return $col;
    }

}
