<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';

use Phinance\PhinanceClient;

$client = new PhinanceClient();

try{
// call web API
    $time = $client->getTime();

// show request URI
    $uri = $client->getLastRequest()->getUrl();
    echo 'URI:' . PHP_EOL;
    echo ' ' . $uri . PHP_EOL;

    echo 'Time:' . print_r($time, true) . PHP_EOL;
}
catch(\Exception $e)
{
    print_stacktrace($e);
}
