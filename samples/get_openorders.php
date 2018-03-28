<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

list($api_key, $api_secret) = binance_credentials();

$argdefs = array(
    '[symbol]' => 'string',
    '[recvWindow]' => 'int',
);
list($symbol, $recvWindow) = get_args($argdefs,__FILE__);

$client = new PhinanceClient($api_key, $api_secret);

try{
// call web API
    $client->setServerTime();
    
    $open_orders = $client->getOpenOrders($symbol, $recvWindow);

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'open_orders:' . print_r($open_orders, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
