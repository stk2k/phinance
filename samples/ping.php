<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

$client = new PhinanceClient();

try{
// call web API
    $client->ping();

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'PING success.' . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
