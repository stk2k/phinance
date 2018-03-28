<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

$argdefs = array(
    '[symbol]' => 'string',
);
list($symbol) = get_args($argdefs,__FILE__);

$client = new PhinanceClient();

try{
// call web API
    $ticker_24hr = $client->getTicker24hr($symbol);

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'ticker_24hr:' . print_r($ticker_24hr, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
