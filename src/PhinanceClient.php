<?php
namespace Phinance;

use NetDriver\NetDriverInterface;
use NetDriver\NetDriverHandleInterface;
use NetDriver\Http\HttpRequest;
use NetDriver\Http\HttpGetRequest;
use NetDriver\Http\HttpPostRequest;
use NetDriver\Http\HttpDeleteRequest;
use NetDriver\NetDriver\Curl\CurlNetDriver;

use Phinance\Exception\PhinanceClientException;
use Phinance\Exception\PhinanceClientExceptionInterface;
use Phinance\Exception\ServerResponseFormatException;
use Phinance\Exception\WebApiCallException;
use Phinance\Enum\EnumSecurityType;

/**
 * Phinance client class
 */
class PhinanceClient implements PhinanceClientInterface
{
    /** @var null|string  */
    private $api_key;

    /** @var null|string  */
    private $api_secret;

    /** @var NetDriverHandleInterface  */
    private $netdriver_handle;

    /** @var HttpRequest */
    private $last_request;

    /** @var int  */
    private $time_offset;

    /** @var NetDriverInterface */
    private $net_driver;

    /** @var NetDriverChangeListenerInterface[] */
    private $netDriverChangeListeners;

    /** @var HttpRequestCreateListenerInterface[] */
    private $httpRequestCreateListeners;

    /**
     * construct
     *
     * @param string|null $api_key
     * @param string|null $api_secret
     */
    public function __construct($api_key = null, $api_secret = null){
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->netdriver_handle = null;
        $this->last_request = null;
        $this->time_offset = 0;
        $this->netDriverChangeListeners = [];
        $this->httpRequestCreateListeners = [];
    }

    /**
     * get last request
     *
     * @return HttpRequest
     */
    public function getLastRequest()
    {
        return $this->last_request;
    }

    /**
     * add net driver change listener
     *
     * @param NetDriverChangeListenerInterface|callable $listener
     */
    public function addNetDriverChangeListener($listener)
    {
        if (is_callable($listener) || $listener instanceof NetDriverChangeListenerInterface){
            $this->netDriverChangeListeners[] = $listener;
        }
    }

    /**
     * add request create listener
     *
     * @param HttpRequestCreateListenerInterface|callable $listener
     */
    public function addRequestCreateListener($listener)
    {
        if (is_callable($listener) || $listener instanceof HttpRequestCreateListenerInterface){
            $this->httpRequestCreateListeners[] = $listener;
        }
    }

    /**
     * set net driver
     *
     * @param NetDriverInterface $net_driver
     */
    public function setNetDriver(NetDriverInterface $net_driver)
    {
        $this->net_driver = $net_driver;

        // callback
        $this->fireNetDriverChangeEvent($net_driver);
    }

    /**
     * net driver change callback
     *
     * @param NetDriverInterface $net_driver
     */
    private function fireNetDriverChangeEvent(NetDriverInterface $net_driver)
    {
        foreach($this->netDriverChangeListeners as $l) {
            if ($l instanceof NetDriverChangeListenerInterface) {
                $l->onNetDriverChanged($net_driver);
            }
            else if (is_callable($l)) {
                $l($net_driver);
            }
        }
    }

    /**
     * http request create callback
     *
     * @param HttpRequest $request
     */
    private function fireHttpRequestCreateEvent(HttpRequest $request)
    {
        foreach($this->httpRequestCreateListeners as $l) {
            if ($l instanceof HttpRequestCreateListenerInterface) {
                $l->onHttpRequestCreated($request);
            }
            else if (is_callable($l)) {
                $l($request);
            }
        }
    }

    /**
     * get net friver
     *
     * @return CurlNetDriver|NetDriverInterface
     */
    public function getNetDriver()
    {
        if ($this->net_driver){
            return $this->net_driver;
        }
        $this->net_driver = new CurlNetDriver();
        // callback
        $this->fireNetDriverChangeEvent($this->net_driver);
        return $this->net_driver;
    }

    /**
     * get net driver handle
     *
     * @return NetDriverHandleInterface|null
     */
    public function getNetDriverHandle()
    {
        if ($this->netdriver_handle){
            return $this->netdriver_handle;
        }
        $this->netdriver_handle = $this->getNetDriver()->newHandle();
        return $this->netdriver_handle;
    }

    /**
     * make request URL
     *
     * @param string $api
     * @param array $query_data
     *
     * @return string
     */
    private static function getURL($api, array $query_data = null)
    {
        $url = PhinanceApi::ENDPOINT . $api;
        if ($query_data){
            $glue = strpos($url,'?') === false ? '?' : '&';
            $url .= $glue . http_build_query($query_data);
        }
        return $url;
    }

    /**
     * call web API by HTTP/GET
     *
     * @param string $api
     * @param array|null $query_data
     *
     * @return mixed
     *
     * @throws PhinanceClientExceptionInterface
     */
    private function get($api, array $query_data = [])
    {
        $query_data = array_filter($query_data, function($v){
            return $v !== null;
        });

        $url = self::getURL($api, $query_data);
        $request = new HttpGetRequest($this->getNetDriver(), $url);

        return $this->executeRequest($request);
    }

    /**
     * call web API(private) by HTTP/GET
     *
     * @param string $api
     * @param string $security_type
     * @param array|null $query_data
     *
     * @return mixed
     *
     * @throws PhinanceClientExceptionInterface
     */
    private function privateGet($api, $security_type, array $query_data = [])
    {
        $query_data = array_filter($query_data, function($v){
            return $v !== null;
        });

        $options = [];
        switch($security_type){
            case EnumSecurityType::TRADE:
            case EnumSecurityType::USER_DATA:
                $ts = (microtime(true)*1000) + $this->getServerTime();
                $query_data['timestamp'] = number_format($ts,0,'.','');
                $query = http_build_query($query_data, '', '&');
                $signature = hash_hmac('sha256', $query, $this->api_secret);
                $options['http-headers'] = [
                    'X-MBX-APIKEY' => $this->api_key,
                ];
                $url = self::getURL($api) . '?' . $query . '&signature=' . $signature;
                break;

            case EnumSecurityType::USER_STREAM:
            case EnumSecurityType::MARKET_DATA:
                $query = http_build_query($query_data, '', '&');
                $options['http-headers'] = [
                    'X-MBX-APIKEY' => $this->api_key,
                ];
                $url = self::getURL($api) . '?' . $query;
                break;
            default:
                throw new \LogicException('Invalid security type:' . $security_type);
                break;
        }

        $request = new HttpGetRequest($this->getNetDriver(), $url, $options);

        $this->fireHttpRequestCreateEvent($request);

        return $this->executeRequest($request);
    }

    /**
     * call web API(private) by HTTP/POST
     *
     * @param string $api
     * @param string $security_type
     * @param array $post_data
     *
     * @return mixed
     *
     * @throws PhinanceClientExceptionInterface
     */
    private function privatePost($api, $security_type, array $post_data = [])
    {
        $post_data = array_filter($post_data, function($v){
            return $v !== null;
        });

        $options = [];
        switch($security_type){
            case EnumSecurityType::TRADE:
            case EnumSecurityType::USER_DATA:
                $ts = (microtime(true)*1000) + $this->getServerTime();
                $post_data['timestamp'] = number_format($ts,0,'.','');
                $query = http_build_query($post_data, '', '&');
                $signature = hash_hmac('sha256', $query, $this->api_secret);
                $options['http-headers'] = [
                    'X-MBX-APIKEY' => $this->api_key,
                ];
                $url = self::getURL($api) . '?' . $query . '&signature=' . $signature;
                break;

            case EnumSecurityType::USER_STREAM:
            case EnumSecurityType::MARKET_DATA:
                $options['http-headers'] = [
                    'X-MBX-APIKEY' => $this->api_key,
                ];
                $url = self::getURL($api);
                break;
            default:
                throw new \LogicException('Invalid security type:' . $security_type);
                break;
        }

        $request = new HttpPostRequest($this->getNetDriver(), $url, [], $options);

        $this->fireHttpRequestCreateEvent($request);

        return $this->executeRequest($request);
    }

    /**
     * call web API(private) by HTTP/GET
     *
     * @param string $api
     * @param array|null $query_data
     *
     * @return mixed
     *
     * @throws PhinanceClientExceptionInterface
     */
    private function privateDelete($api, array $query_data = [])
    {
        $query_data = array_filter($query_data, function($v){
            return $v !== null;
        });

        $ts = (microtime(true)*1000) + $this->getServerTime();
        $query_data['timestamp'] = number_format($ts,0,'.','');
        $query = http_build_query($query_data, '', '&');
        $signature = hash_hmac('sha256', $query, $this->api_secret);

        $options['http-headers'] = array(
            'X-MBX-APIKEY' => $this->api_key,
        );
        //$options['verbose'] = 1;

        $url = self::getURL($api) . '?' . $query . '&signature=' . $signature;
        $request = new HttpDeleteRequest($this->getNetDriver(), $url,  $options);

        $this->fireHttpRequestCreateEvent($request);

        return $this->executeRequest($request);
    }

    /**
     * execute request
     *
     * @param HttpRequest $request
     *
     * @return mixed
     *
     * @throws PhinanceClientExceptionInterface
     */
    private function executeRequest($request)
    {
        try{
            $response = $this->net_driver->sendRequest($this->getNetDriverHandle(), $request);

            $this->last_request = $request;

            $json = @json_decode($response->getBody(), true);
            if ($json === null){
                throw new WebApiCallException(json_last_error_msg());
            }
            return $json;
        }
        catch(\Throwable $e)
        {
            throw new PhinanceClientException('NetDriver#sendRequest() failed: ' . $e->getMessage(), $e);
        }
    }

    /**
     * [public] send ping
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function ping()
    {
        // HTTP GET
        $json = $this->get(PhinanceApi::PING);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
    }

    /**
     * [public] set server time offset
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function setServerTime()
    {
        $server_time = $this->getTime();
        $this->time_offset = $server_time - (microtime(true)*1000);
    }

    /**
     * [public] set server time offset
     *
     * @throws PhinanceClientExceptionInterface
     */
    protected function getServerTime()
    {
        if (!$this->time_offset){
            $this->setServerTime();
        }
        return $this->time_offset;
    }

    /**
     * [public] get server time
     *
     * @return int
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getTime()
    {
        // HTTP GET
        $json = $this->get(PhinanceApi::TIME);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json['serverTime'];
    }

    /**
     * [public] get exchange info
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getExchangeInfo()
    {
        // HTTP GET
        $json = $this->get(PhinanceApi::EXCHANGEINFO);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] Order book
     *
     * @param string $symbol
     * @param int $limit
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getDepth($symbol, $limit = NULL)
    {
        $data = array(
            'symbol' => $symbol,
        );
        if ($limit){
            $data['limit'] = $limit;
        }

        // HTTP GET
        $json = $this->get(PhinanceApi::DEPTH, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] Get recent trades
     *
     * @param string $symbol
     * @param int $limit
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getTrades($symbol, $limit = NULL)
    {
        $data = array(
            'symbol' => $symbol,
        );
        if ($limit){
            $data['limit'] = $limit;
        }

        // HTTP GET
        $json = $this->get(PhinanceApi::TRADES, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }


    /**
     * [public] Get older trades.
     *
     * @param string $symbol
     * @param int $fromId
     * @param int $limit
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getHistoricalTrades($symbol, $fromId = NULL, $limit = NULL)
    {
        $data = array(
            'symbol' => $symbol,
        );
        if ($fromId){
            $data['fromId'] = $fromId;
        }
        if ($limit){
            $data['limit'] = $limit;
        }

        // HTTP GET
        $json = $this->privateGet(PhinanceApi::HISTORICALTRADES, EnumSecurityType::MARKET_DATA, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] Kline/candlestick bars for a symbol.
     *
     * @param string $symbol
     * @param string $interval
     * @param int $limit
     * @param int $startTime
     * @param int $endTime
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getKlines($symbol, $interval, $limit = NULL, $startTime = NULL, $endTime = NULL)
    {
        $data = array(
            'symbol' => $symbol,
            'interval' => $interval,
        );
        if ($limit){
            $data['limit'] = $limit;
        }
        if ($startTime){
            $data['startTime'] = $startTime;
        }
        if ($endTime){
            $data['endTime'] = $endTime;
        }

        // HTTP GET
        $json = $this->get(PhinanceApi::KLINES, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] 24 hour price change statistics
     *
     * @param string $symbol
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getTicker24hr($symbol = NULL)
    {
        $data = array();
        if ($symbol){
            $data['symbol'] = $symbol;
        }

        // HTTP GET
        $json = $this->get(PhinanceApi::TICKER_24HR, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] Latest price for a symbol or symbols.
     *
     * @param string $symbol
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getTickerPrice($symbol = NULL)
    {
        $data = array();
        if ($symbol){
            $data['symbol'] = $symbol;
        }

        // HTTP GET
        $json = $this->get(PhinanceApi::TICKER_PRICE, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] Best price/qty on the order book for a symbol or symbols.
     *
     * @param string $symbol
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getTickerBookTicker($symbol = NULL)
    {
        $data = array();
        if ($symbol){
            $data['symbol'] = $symbol;
        }

        // HTTP GET
        $json = $this->get(PhinanceApi::TICKER_BOOKTICKER, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] Best price/qty on the order book for a symbol or symbols.
     *
     * @param string $symbol
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getOpenOrders($symbol = NULL, $recvWindow = NULL)
    {
        $data = array();
        if ($symbol){
            $data['symbol'] = $symbol;
        }
        if ($recvWindow){
            $data['recvWindow'] = $recvWindow;
        }

        // HTTP GET
        $json = $this->privateGet(PhinanceApi::OPENORDERS, EnumSecurityType::USER_DATA, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }


    /**
     * [USER_DATA] Get all account orders; active, canceled, or filled.
     *
     * @param string $symbol
     * @param int $orderId
     * @param int $limit
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getAllOrders($symbol = NULL, $orderId = NULL, $limit = NULL, $recvWindow = NULL)
    {
        $data = array(
        );
        if ($symbol){
            $data['symbol'] = $symbol;
        }
        if ($orderId){
            $data['orderId'] = $orderId;
        }
        if ($limit){
            $data['limit'] = $limit;
        }
        if ($recvWindow){
            $data['recvWindow'] = $recvWindow;
        }

        // HTTP GET
        $json = $this->privateGet(PhinanceApi::ALLORDERS, EnumSecurityType::USER_DATA, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] Send in a new order.
     *
     * @param string $symbol
     * @param string $side
     * @param string $type
     * @param float $quantity
     * @param float $price
     * @param array $options   timeInForce, newClientOrderId, stopPrice, icebergQty, newOrderRespType, recvWindow
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function sendOrder($symbol, $side, $type, $quantity, $price = NULL, $options = [])
    {
        $data = array(
            'symbol' => $symbol,
            'side' => $side,
            'type' => $type,
            'quantity' => $quantity,
            'recvWindow' => 60000,
        );
        if ($price){
            $data['price'] = number_format( $price, 8, '.', '' );
        }
        $data = array_merge($data, $options);

        // HTTP POST
        $json = $this->privatePost(PhinanceApi::ORDER, EnumSecurityType::TRADE, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [public] Cancel an active order.
     *
     * @param string $symbol
     * @param int $orderId
     * @param string $origClientOrderId
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function cancelOrder($symbol, $orderId = NULL, $origClientOrderId = NULL, $recvWindow = NULL)
    {
        $data = array(
            'symbol' => $symbol,
        );
        if ($recvWindow){
            $data['recvWindow'] = $recvWindow;
        }

        // HTTP DELETE
        $json = $this->privateDelete(PhinanceApi::ORDER, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }

    /**
     * [USER_DATA] Get current account information.
     *
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getAcount($recvWindow = NULL)
    {
        $data = array(
        );
        if ($recvWindow){
            $data['recvWindow'] = $recvWindow;
        }

        // HTTP GET
        $json = $this->privateGet(PhinanceApi::ACCOUNT, EnumSecurityType::USER_DATA, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }


    /**
     * [USER_DATA] Get trades for a specific account and symbol.
     *
     * @param string $symbol
     * @param int $limit
     * @param int $fromId
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws PhinanceClientExceptionInterface
     */
    public function getMyTrades($symbol, $limit = NULL, $fromId = NULL, $recvWindow = NULL)
    {
        $data = array(
            'symbol' => $symbol,
        );
        if ($limit){
            $data['limit'] = $limit;
        }
        if ($fromId){
            $data['fromId'] = $fromId;
        }
        if ($recvWindow){
            $data['recvWindow'] = $recvWindow;
        }

        // HTTP GET
        $json = $this->privateGet(PhinanceApi::MYTRADES, EnumSecurityType::USER_DATA, $data);
        // check return type
        if (!is_array($json)){
            throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
        }
        return $json;
    }
}