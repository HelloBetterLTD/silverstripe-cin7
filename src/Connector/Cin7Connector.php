<?php

namespace SilverStripers\Cin7\Connector;

use GuzzleHttp\Client;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\SiteConfig\SiteConfig;

class Cin7Connector
{

    use Configurable;
    use Injectable;

    const API_BASE = 'https://api.cin7.com/api/';

    const PRODUCT_CATEGORIES_ENDPOINT = 'v1/ProductCategories';
    const PRODUCTS_ENDPOINT = 'v1/Products';

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
                'timeout' => 2.0,
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

    public function getProducts() : array
    {
        $config = SiteConfig::current_site_config();
        $response = $this->get(self::PRODUCTS_ENDPOINT, [
            'rows' => 10,
            'page' => $config->CurrentProductPage ?: 1
        ]);
        if (empty($response)) {
            $config->CurrentProductPage = 1;
        } else {
            $config->CurrentProductPage += 1;
        }
        $config->write();
        return $response;
    }

    public function get($path, $data = null) : array
    {
        $params = null;
        if ($data) {
            $params = [
                'query' => $data
            ];
        }
        $response = $this->getClient()->get($path, $params);
        $json = $response->getBody()->getContents();
        return json_decode($json, true);
    }

}
