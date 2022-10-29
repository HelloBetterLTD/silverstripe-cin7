<?php

namespace SilverStripers\Cin7\Connector;

use GuzzleHttp\Client;
use SilverShop\Model\Order;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripers\Out\System\Log;

class Cin7Connector
{

    use Configurable;
    use Injectable;

    const API_BASE = 'https://api.cin7.com/api/';

    const PRODUCT_CATEGORIES_ENDPOINT = 'v1/ProductCategories';
    const PRODUCTS_ENDPOINT = 'v1/Products';
    const BRANCHES_ENDPOINT = 'v1/Branches';
    const STOCK_ENDPOINT = 'v1/Stock';
    const POST_ORDER = 'v1/SalesOrders';
    const POST_CONTACTS = 'v1/Contacts';
    const PURCHASE_ORDERS = 'v1/PurchaseOrders';
    const GET_SALES_ORDERS = 'v1/SalesOrders';

    private static $conn;

    private $username = null;
    private $password = null;
    private $client = null;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public static function init() : Cin7Connector
    {
        if (!self::$conn) {
            $isDev = Director::isDev();
            $config = SiteConfig::current_site_config();
            $username = $isDev ? $config->DevAPIUsername : $config->APIUsername;
            $password = $isDev ? $config->DevAPIConnectionKey : $config->APIConnectionKey;

            if (empty($username) || empty($password)) {
                throw new \Exception('$username or $password cannot be empty');
            }
            $conn = new Cin7Connector($username, $password);
            self::$conn = $conn;
        }
        return self::$conn;
    }

    public function getClient() : Client
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => self::API_BASE,
                'timeout' => 30,
                'read_timeout' => 30,
                'connect_timeout' => 30,
                'auth' => [
                    $this->username,
                    $this->password
                ]
            ]);
        }
        return $this->client;
    }

    /**
     * @return array
     */
    public function getProductCategories() : array
    {
        return $this->get(self::PRODUCT_CATEGORIES_ENDPOINT, ['rows' => 250]); // TODO: pagination
    }

    public function getProducts($page = 1, $lastImported = null) : array
    {
        $params = [
            'rows' => 20,
            'page' => $page ? : 1
        ];
        if ($lastImported) {
            $params['where'] = sprintf(
                "modifiedDate>='%s'",
                $this->dateToCin7Date($lastImported)
            );
        }
        $response = $this->get(self::PRODUCTS_ENDPOINT, $params);
        return $response;
    }

    public function getProductData($id)
    {
        $response = $this->get(self::PRODUCTS_ENDPOINT . '/' . $id, []);
        return $response;
    }


    public function getBranches() : array
    {
        $config = SiteConfig::current_site_config();
        $response = $this->get(self::BRANCHES_ENDPOINT, [
            'rows' => 100,
            'page' => 1
        ]);
        return $response;
    }

    public function get($path, $data = null) : array
    {
        $params = [];
        if ($data) {
            $params = [
                'query' => $data
            ];
        }
        try {
            $response = $this->getClient()->get($path, $params);
            $json = $response->getBody()->getContents();
            return json_decode($json, true);
        } catch (\Exception $e) {
            Log::printLn($e->getMessage());
        }
        return [];
    }

    public function post($path, $json) : array
    {
        try {
            $response = $this->getClient()->request('POST', $path, [
                'body' => $json,
                'headers' => [
                    'Content-type' => 'application/json'
                ]
            ]);
            $json = $response->getBody()->getContents();
            return json_decode($json, true);
        } catch (\Exception $e) {
            Log::printLn($e->getMessage());
        }
        return [];
    }

    public function put($path, $json) : array
    {
        try {
            $response = $this->getClient()->request('PUT', $path, [
                'body' => $json
            ]);
            $json = $response->getBody()->getContents();
            return json_decode($json, true);
        } catch (\Exception $e) {
            Log::printLn($e->getMessage());
        }
        return [];
    }

    public function getStockForProduct($sku) : array
    {
        return $this->get(self::STOCK_ENDPOINT, [
            'where' => "code='$sku'"
        ]);
    }

    public function getStockForBranch($sku, $branchId) : int
    {
        $stocks = $this->getStockForProduct($sku);
        foreach ($stocks as $stock) {
            if ($stock['branchId'] == $branchId) {
                return $stock['stockOnHand'];
            }
        }
        return 0;
    }

    public function getStocks($page = 1, $lastImported = null)
    {
        $params = [
            'rows' => 250,
            'page' => $page ?: 1
        ];
        if ($lastImported) {
            $params['where'] = sprintf(
                "modifiedDate>='%s'",
                $this->dateToCin7Date($lastImported)
            );
        }
        $response = $this->get(self::STOCK_ENDPOINT, $params);
        return $response;
    }

    public function syncOrder(Order $order)
    {
        $data = $order->toCin7();
        if (!$order->ExternalID) {
            $response = $this->post(self::POST_ORDER, json_encode($data));
            $order->SyncResponse = json_encode($response);
            if ($response) {
                $order->ExternalID = $response[0]['id'];
            }
            $order->write();

            $order->extend('onAfterSyncOrder', $response);

            return $response;
        } else {
            $response = $this->put(self::POST_ORDER, json_encode($data));
            $order->extend('onAfterSyncOrder', $response);
            return $response;
        }
    }

    public function syncMember(Member $member)
    {
        $data = $member->toCin7();
        if (!$member->ExternalID) {
            $response = $this->post(self::POST_CONTACTS, json_encode([$data]));
            if ($response) {
                $member->ExternalID = $response[0]['id'];
                $member->write();
            }
            return $response;
        } else {
            return $this->put(self::POST_CONTACTS, json_encode([$data]));
        }
    }

    public function copyMember(Member $member)
    {
        $data = $member->toCin7();
        if ($member->ExternalID) {
            $response = $this->get(self::POST_CONTACTS . '/' . $member->ExternalID);
            $member->Cin7Data = json_encode($response);
            if ($response && array_key_exists('priceColumn', $response)) {
                $member->PriceColumn = $response['priceColumn'];
            }
            $member->write();
        }
        return $member;
    }

    public function getPurchaseOrders($page = 0, $lastImported = null)
    {
        $params = [
            'rows' => 250,
            'page' => $page ?: 1
        ];
        if ($lastImported) {
            $params['where'] = sprintf(
                "modifiedDate>='%s'",
                $this->dateToCin7Date($lastImported)
            );
        }
        $response = $this->get(self::PURCHASE_ORDERS, $params);
        return $response;
    }

    public function cin7DateToDt($date)
    {
        $dt = new \DateTime(
            str_replace('Z', '', str_replace('T', ' ', $date)),
            new \DateTimeZone('UTC')
        );
        $dt->setTimezone(new \DateTimeZone('Pacific/Auckland'));
        return $dt->format('Y-m-d H:i:s');
    }

    public function dateToCin7Date($date)
    {
        $dt = strtotime($date);
        return gmdate('Y-m-d\TH:i:s\Z', $dt);
    }

    public function getOrders($rows = 250, $page = 1, $date = null)
    {
        $params = [
            'rows' => $rows,
            'page' => $page
        ];

        if ($date) {
            $params['where'] = sprintf(
                "modifiedDate>='%s'",
                $this->dateToCin7Date($date)
            );
        }
        return $this->get(self::GET_SALES_ORDERS, $params);
    }
}
