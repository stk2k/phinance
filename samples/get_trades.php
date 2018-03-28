<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

$argdefs = array(
    'symbol' => 'string',
    '[limit]' => 'integer',
);
list($symbol, $limit) = get_args($argdefs,__FILE__);

$client = new PhinanceClient();

try{
// call web API
    $trades = $client->getTrades($symbol, $limit);

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'trades:' . print_r($trades, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
