<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

list($api_key, $api_secret) = binance_credentials();

$argdefs = array(
    'symbol' => 'string',
    'side' => 'string',
    'type' => 'string',
    'quantity' => 'string',
    '[fromid]' => 'integer',
    '[limit]' => 'integer',
);
list($symbol, $side, $type, $quantity, $fromid, $limit) = get_args($argdefs,__FILE__);

$client = new PhinanceClient($api_key, $api_secret);

try{
    $client->setServerTime();

// call web API
    $order_result = $client->sendOrder($symbol, $side, $type, $quantity);

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'order_result:' . print_r($order_result, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
