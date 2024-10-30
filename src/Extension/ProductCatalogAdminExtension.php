<?php


namespace SilverStripers\Cin7\Extension;

use SilverStripers\Aurora\Model\Shop\Product;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripers\Cin7\Form\Request\ProductGrid_ItemRequest;

class ProductCatalogAdminExtension extends Extension
{

    public function updateEditForm(Form $form)
    {
        $owner = $this->owner;
        $recordClass = $owner->getModelClass();
        if ($recordClass == Product::class) {
            $fields = $form->Fields();
            /* @var $grid GridField */
            $grid = $fields->dataFieldByName($this->sanitiseClassName($recordClass));
            $config = $grid->getConfig();
            /* @var $detailForm GridFieldDetailForm */
            $detailForm = $config->getComponentByType(GridFieldDetailForm::class);
            $detailForm->setItemRequestClass(ProductGrid_ItemRequest::class);
        }
    }

    protected function sanitiseClassName($class)
    {
        return str_replace('\\', '-', $class);
    }

}
