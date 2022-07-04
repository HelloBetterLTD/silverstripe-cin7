<?php

namespace SilverStripers\Cin7\Form\Request;

use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\ORM\ValidationResult;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\ProductLoader;

class ProductGrid_ItemRequest extends GridFieldDetailForm_ItemRequest
{

    private static $allowed_actions = [
        'ItemEditForm'
    ];

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        $actions = $form->Actions();
        if ($this->record->exists()) {
            $actions->fieldByName('MajorActions')
                ->push(
                    FormAction::create('doSyncWithAPI', 'Sync with Cin7')
                        ->addExtraClass('btn btn-outline-primary')
                );
        }
        return $form;
    }

    public function doSyncWithAPI($data, $form)
    {
        $product = $this->record;
        if ($product->ExternalID) {
            $connector = Cin7Connector::init();
            $apiData = $connector->getProductData($product->ExternalID);
            if ($apiData) {
                $productLoader = ProductLoader::singleton();
                $productLoader->load($apiData, true);
            }
        }

        $link = '<a href="' . $this->Link('edit') . '">"'
            . htmlspecialchars($this->record->Title, ENT_QUOTES)
            . '"</a>';
        $message = _t(
            'SilverStripe\\Forms\\GridField\\GridFieldDetailForm.Synced',
            'Synchronised {name} {link}',
            [
                'name' => $this->record->i18n_singular_name(),
                'link' => $link
            ]
        );

        $form->sessionMessage($message, 'good', ValidationResult::CAST_HTML);
        // Redirect after save
        return $this->redirectAfterSave(false);
    }

}
