<?php


namespace SilverStripers\Cin7\Connector\Loader;


use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

class Loader
{

    use Injectable;
    use Configurable;

    public function load($data)
    {
        user_error('Loader:load function needs to be updated');
    }

    public function getHash($data)
    {
        return md5(json_encode($data));
    }

}
