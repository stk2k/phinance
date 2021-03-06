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
    $ticker_bookticker = $client->getTickerBookTicker($symbol);

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'ticker_bookticker:' . print_r($ticker_bookticker, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
