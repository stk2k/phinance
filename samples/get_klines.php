<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

$argdefs = array(
    'symbol' => 'string',
    'interval' => 'string',
    '[limit]' => 'integer',
    '[startTime]' => 'integer',
    '[endTime]' => 'integer',
);
list($symbol, $interval, $limit, $startTime, $endTime) = get_args($argdefs,__FILE__);

$client = new PhinanceClient();

try{
// call web API
    $klines = $client->getKlines($symbol, $interval, $limit, $startTime, $endTime);

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'klines:' . print_r($klines, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
