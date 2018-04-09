<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

list($api_key, $api_secret) = binance_credentials();

$argdefs = array(
    'symbol' => 'string',
    '[fromid]' => 'integer',
    '[limit]' => 'integer',
);
list($symbol, $fromid, $limit) = get_args($argdefs,__FILE__);

$client = new PhinanceClient($api_key, $api_secret);

try{
// call web API
    $historical_trades = $client->getHistoricalTrades($symbol, $fromid, $limit);

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'historical_trades:' . print_r($historical_trades, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
