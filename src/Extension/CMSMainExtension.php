<?php

namespace SilverStripers\Cin7\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\ValidationResult;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductLoader;

class CMSMainExtension extends Extension
{

    private static $allowed_actions = [
        'doSyncWithAPI',
    ];

    public function doSyncWithAPI($data, $form)
    {
        $product = $this->owner->currentPage();
        if ($product->ExternalID) {
            $connector = Cin7Connector::init();
            $apiData = $connector->getProductData($product->ExternalID);
            if ($apiData) {
                $productLoader = ProductLoader::singleton();
                $productLoader->load($apiData, true);
            }
        }

        $message = _t(
            __CLASS__ . '.SAVED',
            "Synchronised '{title}' successfully.",
            ['title' => $product->Title]
        );

        $this->owner->getResponse()->addHeader('X-Status', rawurlencode($message ?? ''));
        return $this->owner->getResponseNegotiator()->respond($this->owner->getRequest());
    }

}
