<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require dirname(__FILE__) . '/include/autoload.php';
require dirname(__FILE__) . '/include/sample.inc.php';

use Phinance\PhinanceClient;

echo '===[ simple and fastest sample ]===', PHP_EOL;

try{
    $client = new PhinanceClient();
    
    $exchange_info = $client->getExchangeInfo();
    
    foreach($exchange_info['symbols'] as $idx => $symbol){
        echo $idx . '.' . PHP_EOL;
        echo 'symbol:' . $symbol['symbol'] . PHP_EOL;
    }
}
catch(\Exception $e)
{
    print_stacktrace($e);
}


