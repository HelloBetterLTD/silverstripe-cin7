<?php

namespace SilverStripers\Cin7\Connector\Loader;


use SilverStripers\Cin7\Model\Branch;

class BranchLoader extends Loader
{

    private function findBranch($data)
    {
        return Branch::get()->find('ExternalID', $data['id']);
    }

    public function load($data)
    {
        $branch = $this->findBranch($data);
        if (!$branch) {
            $branch = Branch::create();
        }
        $hash = $this->getHash($data);
        if ($branch->Hash != $hash) {
            $branch->update([
                'ExternalID' => $data['id'],
                'Hash' => $hash,
                'BranchType' => $data['branchType'],
                'StockControlOptions' => $data['stockControlOptions'],
                'IsActive' => $data['isActive'] ? 1 : 0,
                'Company' => $data['company'],
                'Mobile' => $data['mobile'],
                'Phone' => $data['phone'],
                'Fax' => $data['fax'],
                'Email' => $data['email'],
                'Address1' => $data['address1'],
                'Address2' => $data['address2'],
                'City' => $data['city'],
                'State' => $data['state'],
                'PostCode' => $data['postCode'],
                'Country' => $data['country'],
                'PostalAddress1' => $data['postalAddress1'],
                'PostalAddress2' => $data['postalAddress2'],
                'PostalCity' => $data['postalCity'],
                'PostalState' => $data['postalState'],
                'PostalPostCode' => $data['postalPostCode'],
                'PostalCountry' => $data['postalCountry'],
            ]);
            $branch->write();
        }
    }


}
