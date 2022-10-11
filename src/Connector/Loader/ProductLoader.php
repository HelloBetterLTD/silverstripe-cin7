<?php

namespace SilverStripers\Cin7\Connector\Loader;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\View\Parsers\URLSegmentFilter;
use SilverStripers\Cin7\Extension\AttributeTypeExtension;
use SilverStripers\Cin7\Extension\VariationExtension;
use SilverStripers\Cin7\Model\Price;
use SilverStripers\Cin7\Model\PriceOption;
use SilverStripers\Cin7\Model\ProductCategory;
use SilverStripers\Out\System\Log;

class ProductLoader extends Loader
{

    private $isNew = true;

    private static $archive_others = true;

    private function findProduct($data)
    {
        return Product::get()->find('ExternalID', $data['id']);
    }

    private function findMainCategoryID($ids)
    {
        foreach ($ids as $id) {
            $mainCategory = ProductCategory::get()
                ->filter('ExternalID', $id)
                ->where('ProductCategoryID IS NOT NULL')->first();
            if ($mainCategory) {
                return $id;
            }
        }
        return 0;
    }

    private function findShopCategoryId($id)
    {
        $category = ProductCategory::get()->find('ExternalID', $id);
        return $category && $category->ProductCategoryID ? $category->ProductCategoryID : 0;
    }

    private function createNewProduct($data)
    {
        $product = Product::create([
            'ExternalID' => $data['id'],
            'URLSegment' => URLSegmentFilter::create()->filter($data['name'])
        ]);
        $product->write();
        // load product images only for new pages
        if ($this->isNew) {
            foreach ($data['images'] as $image) {
                if ($image['link'] && ($file = ImageLoader::load($image['link'])) && $file->exists()) {
                    if (!$product->ImageID) {
                        $product->ImageID = $file->ID;
                        $product->write();
                    }
                    $product->Images()->add($file->ID);
                }
            }
        }
        return $product;
    }

    private function assignCategoriesToProduct($data, Product $product)
    {
        $ids = $data['categoryIdArray'];
        $mainCategoryID = $this->findMainCategoryID($ids);
        if ($this->isNew) {
            $product->ParentID = $this->findShopCategoryId($mainCategoryID);
        }
        foreach ($ids as $id) {
            if ($shopId = $this->findShopCategoryId($id)) {
                $product->ProductCategories()->add($shopId);
            }
        }
        return $product;
    }

    private function canImportProduct($data)
    {
        $ids = $data['categoryIdArray'];
        $categories = ProductCategory::get()
            ->filter('ExternalID', $ids)
            ->where('ProductCategoryID > 0');
        return !empty($data['status']) && $data['status'] == 'Public' && $categories->count() > 0;
    }


    public function load($data, $force = false)
    {
        /* @var $product Product */
        $product = $this->findProduct($data);
        if ($this->canImportProduct($data)) {
            Log::printLn('Can import product ' . $data['id']);
            $this->isNew = false;
            if (!$product) {
                $this->isNew = true;
                $product = $this->createNewProduct($data);
                Log::printLn('Created new product for ' . $data['id']);
            }
            if ($force || $product->ExternalHash != $this->getHash($data)) {
                Log::printLn('Importing product ' . $data['id']);
                $this->assignCategoriesToProduct($data, $product);
                $this->importBasicData($data, $product);
                $this->processVariations($data, $product);
                $product->ExternalHash = $this->getHash($data);
                $product->write();
                if ($product->isPublished()) { // publish only previously published products
                    $product->publishRecursive();
                }
            }
        } else if (self::config()->get('archive_others') && $product) {
            $product->doUnpublish();
            $product->doArchive();
        }
    }

    private function importBasicData($data, Product $product)
    {
        $product->update([
            'Title' => $data['name'],
            'MenuTitle' => $data['name'],
            'Weight' => $data['weight'],
            'Height' => $data['height'],
            'Width' => $data['width'],
            'Depth' => $data['length'],
            'Volume' => $data['volume'],
            'Brand' => $data['brand'],
            'StyleCode' => $data['styleCode'],
            'Model' => $data['styleCode'],
            'Content' => $data['description'],
            'StyleCode' => $data['styleCode'],
            'Cin7Categories' => implode(',', $data['categoryIdArray']),
            'CustomFields' => json_encode(!empty($data['customFields']) ? $data['customFields'] : []),
        ]);
        $product->write();
    }

    private function processVariations($data, Product $product)
    {
        $variatonIDs = $product->Variations()->map('ID', 'ID')->toArray();
        if ($data['productOptions']) {
            $sizeType = AttributeTypeExtension::get_size_type();
            $product->VariationAttributeTypes()->add($sizeType);
        }
        foreach ($data['productOptions'] as $optionData) {
            if (in_array($optionData['status'], [VariationExtension::PRIMARY, VariationExtension::ACTIVE])) {
                $variation = $this->importVariation($optionData, $product);
                if ($variation) {
                    unset($variatonIDs[$variation->ID]);
                }
            }
        }
        foreach ($variatonIDs as $id) {
            /* @var $variation Variation */
            if ($variation = Variation::get()->byID($id)) {
                $variation->doArchive();
            }
        }
    }


    public function importVariation($data, Product $product)
    {
        $variation = $this->findVariation($data, $product);
        if (!$variation) {
            $variation = $this->createVariation($data, $product);
        }
        $variation->update([
            'Title' => !empty($data['option1']) ? $data['option1'] : '',
            'Status' => $data['status'],
            'InternalItemID' => $data['code'],
            'Price' => $data['retailPrice'],
            'Barcode' => $data['barcode'],
            'WholesalePrice' => $data['wholesalePrice'],
            'VipPrice' => $data['vipPrice'],
            'SpecialPrice' => $data['specialPrice'],
            'StockAvailable' => $data['stockAvailable'],
            'StockOnHand' => $data['stockOnHand'],
        ]);
        $variation->write();

        $variation->AttributeValues()->removeAll(); // remove all first and then add again.

        if (!empty($data['size'])) {
            $variation->AttributeValues()
                ->add(AttributeTypeExtension::find_or_make_size_attribute($data['size']));
        }
        if (!empty($data['option1'])) {
            $product->VariationAttributeTypes()->add(AttributeTypeExtension::get_color_type());
            $variation->AttributeValues()
                ->add(AttributeTypeExtension::find_or_make_color_attribute($data['option1']));
        }

        $priceIds = Price::get()->filter('VariationID', $variation->ID)->map('ID', 'ID')->toArray();
        if (!empty($data['priceColumns'])) {
            foreach ($data['priceColumns'] as $label => $priceColumnPrice) {
                $priceOption = PriceOption::find_or_make($label);
                $price = Price::get()->filter([
                    'PriceOptionID' => $priceOption->ID,
                    'VariationID' => $variation->ID,
                ])->first();
                if (!$price) {
                    $price = Price::create([
                        'PriceOptionID' => $priceOption->ID,
                        'VariationID' => $variation->ID,
                    ]);
                }
                $price->Price = $priceColumnPrice;
                $price->write();
                unset($priceIds[$price->ID]);
            }
        }

        foreach ($priceIds as $priceId) {
            if ($price = Price::get()->byID($priceId)) {
                $price->delete();
            }
        }
        return $variation;
    }


    private function getVariationID($data)
    {
        return implode('//', [
            $data['id'],
            $data['size']
        ]);
    }

    private function findVariation($data, Product $product)
    {
        return Variation::get()->filter([
            'ProductID' => $product->ID,
            'ExternalID' => $this->getVariationID($data)
        ])->first();
    }


    private function createVariation($data, Product $product)
    {
        $variation = Variation::create([
            'ProductID' => $product->ID,
            'ExternalID' => $this->getVariationID($data)
        ]);
        if ($this->isNew) { // only import images to new
            if (!empty($data['image']) && !empty($data['image']['link'])) {
                if ($image = ImageLoader::load($data['image']['link'])) {
                    $variation->ImageID = $image->ID;
                }
            }
        }
        $variation->write();
        return $variation;
    }
}
