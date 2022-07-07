<?php


namespace SilverStripers\Cin7\Extension;

use SilverStripe\ORM\DataExtension;
use SilverStripers\Cin7\Connector\Cin7Connector;

class MemberExtension extends DataExtension
{

    private static $db = [
        'Company' => 'Varchar',
        'PhoneNumber' => 'Varchar',
        'Mobile' => 'Varchar',
        'ExternalID' => 'Int',
        'PriceColumn' => 'Varchar',
        'Cin7Data' => 'Text'
    ];

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
            $this->syncFromCin7();
        } catch (\Exception $e) {}
    }

}
