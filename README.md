phinance, binance API PHP client
=======================

## Description

phinance is a PHP library which provides calling binance-API.

## Feature

- simple interface

## Demo

### simple and fastest sample:
```php
use Phinance\PhinanceClient;
 
$client = new PhinanceClient();
 
$exchange_info = $client->getExchangeInfo();
 
foreach($exchange_info->symbols as $idx => $symbol){
    echo $idx . '.' . PHP_EOL;
    echo 'symbol:' . $symbol->symbol . PHP_EOL;
}
 
```

## Usage

1. create PhinanceClient object.
2. call API method.
3. The method returns array or object(stdClass).

## Requirement

PHP 5.5 or later


## Installing phinance

The recommended way to install phinance is through
[Composer](http://getcomposer.org).

```bash
composer require stk2k/phinance
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## License
[MIT](https://github.com/stk2k/phinance/blob/master/LICENSE)

## Author

[stk2k](https://github.com/stk2k)

## Disclaimer

This software is no warranty.

We are not responsible for any results caused by the use of this software.

Please use the responsibility of the your self.


## Donation

-Bitcoin: 3HCw9pp6dSq1xU9iPoPKVFyVbM8iBrrinn
