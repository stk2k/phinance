<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

list($api_key, $api_secret) = binance_credentials();

$argdefs = array(
    'symbol' => 'string',
    '[limit]' => 'int',
    '[fromId]' => 'int',
    '[recvWindow]' => 'int',
);
list($symbol, $limit, $fromId, $recvWindow) = get_args($argdefs,__FILE__);

$client = new PhinanceClient($api_key, $api_secret);

try{
// call web API
    $client->setServerTime();
    
    $my_trades = $client->getMyTrades($symbol, $limit, $fromId, $recvWindow);

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'my_trades:' . print_r($my_trades, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
