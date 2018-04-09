<?php
namespace Phinance;

use Phinance\Exception\ApiClientException;
use Phinance\Exception\ServerResponseFormatException;
use Phinance\Http\CurlRequest;
use Phinance\Http\HttpGetRequest;
use Phinance\Http\HttpDeleteRequest;
use Phinance\Http\CurlHandle;
use Phinance\Enum\EnumSecurityType;


/**
 * Phinance client class
 */
class PhinanceClient implements IPhinanceClient
{
    const DEFAULT_USERAGENT    = 'phinance';
    
    private $api_key;
    private $api_secret;
    private $user_agent;
    private $curl_handle;
    private $last_request;
    private $time_offset;
    
    /**
     * construct
     *
     * @param string|null $api_key
     * @param string|null $api_secret
     */
    public function __construct($api_key = null, $api_secret = null){
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->user_agent = self::DEFAULT_USERAGENT;
        $this->curl_handle = new CurlHandle();
        $this->last_request = null;
        $this->time_offset = 0;
    }
    
    /**
     * get last request
     *
     * @return CurlRequest
     */
    public function getLastRequest()
    {
        return $this->last_request;
    }
    
    /**
     * get user agent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }
    
    /**
     * make request URL
     *
     * @param string $api
     *
     * @return string
     */
    private static function getURL($api)
    {
        return PhinanceApi::ENDPOINT . $api;
    }
    
    /**
     * call web API by HTTP/GET
     *
     * @param string $api
     * @param array|null $query_data
     * @param bool $return_value
     *
     * @return mixed
     *
     * @throws ApiClientException
     */
    private function get($api, $query_data = null, $return_value = true)
    {
        $url = self::getURL($api);
    
        $query_data = is_array($query_data) ? array_filter($query_data, function($v){
            return $v !== null;
        }) : null;
        
        $request = new HttpGetRequest($this, $url, $query_data);
    
        return $this->executeRequest($request, $return_value);
    }
    
    /**
     * call web API(private) by HTTP/GET
     *
     * @param string $api
     * @param string $security_type
     * @param array|null $query_data
     * @param bool $return_value
     *
     * @return mixed
     *
     * @throws ApiClientException
     */
    private function privateGet($api, $security_type, $query_data = null, $return_value = true)
    {
        $query_data = is_array($query_data) ? array_filter($query_data, function($v){
            return $v !== null;
        }) : null;

        $options = [];
        switch($security_type){
            case EnumSecurityType::TRADE:
            case EnumSecurityType::USER_DATA:
                $ts = (microtime(true)*1000) + $this->time_offset;
                $query_data['timestamp'] = number_format($ts,0,'.','');
                $query = http_build_query($query_data, '', '&');
                $signature = hash_hmac('sha256', $query, $this->api_secret);
                $options['http_headers'] = [
                    'X-MBX-APIKEY' => $this->api_key,
                ];
                $url = self::getURL($api) . '?' . $query . '&signature=' . $signature;
                break;

            case EnumSecurityType::USER_STREAM:
            case EnumSecurityType::MARKET_DATA:
                $query = http_build_query($query_data, '', '&');
                $options['http_headers'] = [
                    'X-MBX-APIKEY' => $this->api_key,
                ];
                $url = self::getURL($api) . '?' . $query;
                break;
            default:
                throw new \LogicException('Invalid security type:' . $security_type);
                break;
        }

        $request = new HttpGetRequest($this, $url, array(), $options);
    
        return $this->executeRequest($request, $return_value);
    }

    /**
     * call web API(private) by HTTP/GET
     *
     * @param string $api
     * @param array|null $query_data
     * @param bool $return_value
     *
     * @return mixed
     *
     * @throws ApiClientException
     */
    private function privateDelete($api, $query_data = null, $return_value = true)
    {
        $query_data = is_array($query_data) ? array_filter($query_data, function($v){
            return $v !== null;
        }) : null;
        
        $ts = (microtime(true)*1000) + $this->time_offset;
        $query_data['timestamp'] = number_format($ts,0,'.','');
        $query = http_build_query($query_data, '', '&');
        $signature = hash_hmac('sha256', $query, $this->api_secret);
        
        $options['http_headers'] = array(
            'X-MBX-APIKEY' => $this->api_key,
        );
        //$options['verbose'] = 1;
        
        $url = self::getURL($api) . '?' . $query . '&signature=' . $signature;
        $request = new HttpDeleteRequest($this, $url,  $options);
        
        return $this->executeRequest($request, $return_value);
    }
    
    /**
     * execute request
     *
     * @param CurlRequest $request
     * @param bool $return_value
     *
     * @return mixed
     *
     * @throws ApiClientException
     */
    private function executeRequest($request, $return_value = true)
    {
        $json = $request->execute($this->curl_handle, $return_value);
    
        $this->last_request = $request;
    
        return $json;
    }
    
    /**
     * [public] send ping
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function setServerTime()
    {
        $server_time = $this->getTime();
        $this->time_offset = $server_time - (microtime(true)*1000);
    }
    
    /**
     * [public] get server time
     *
     * @return int
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @return object
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @return object
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
        if ($symbol)
        {
            if (!is_object($json)){
                throw new ServerResponseFormatException('response must be an object, but returned:' . gettype($json));
            }
        }
        else{
            if (!is_array($json)){
                throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
            }
        }
        return $json;
    }
    
    /**
     * [public] Latest price for a symbol or symbols.
     *
     * @param string $symbol
     *
     * @return array|object
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
        if ($symbol)
        {
            if (!is_object($json)){
                throw new ServerResponseFormatException('response must be an object, but returned:' . gettype($json));
            }
        }
        else{
            if (!is_array($json)){
                throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
            }
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
        if ($symbol)
        {
            if (!is_object($json)){
                throw new ServerResponseFormatException('response must be an object, but returned:' . gettype($json));
            }
        }
        else{
            if (!is_array($json)){
                throw new ServerResponseFormatException('response must be an array, but returned:' . gettype($json));
            }
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * [public] Cancel an active order.
     *
     * @param string $symbol
     * @param int $orderId
     * @param string $origClientOrderId
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function cancelOrder($symbol, $orderId = NULL, $origClientOrderId = NULL, $recvWindow = NULL)
    {
        $data = array(
            'symbol' => $symbol,
        );
        if ($recvWindow){
            $data['recvWindow'] = $recvWindow;
        }
    
        // HTTP GET
        $json = $this->privateDelete(PhinanceApi::ORDER, $data);
        // check return type
        if (!is_object($json)){
            throw new ServerResponseFormatException('response must be an object, but returned:' . gettype($json));
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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