<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';

use Phinance\PhinanceClient;

$client = new PhinanceClient();

try{
// call web API
    $exchange_info = $client->getExchangeInfo();

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'ExchangeInfo:' . print_r($exchange_info, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
