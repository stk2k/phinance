<?php
namespace Phinance;

use NetDriver\Http\HttpRequest;
use NetDriver\NetDriverInterface;

use Psr\Log\LoggerInterface;

use Phinance\Exception\PhinanceClientException;

/**
 * Logger decorator
 */
class PhinanceLoggerClient implements PhinanceClientInterface, NetDriverChangeListenerInterface
{
    /** @var PhinanceClientInterface  */
    private $client;
    
    /** @var LoggerInterface */
    private $logger;
    
    /**
     * construct
     *
     * @param PhinanceClientInterface $client
     * @param LoggerInterface $logger
     */
    public function __construct($client, $logger){
        $this->client = $client;
        $this->logger = $logger;

        $client->addNetDriverChangeListener($this);
    }

    /**
     * get last request
     *
     * @return HttpRequest
     */
    public function getLastRequest()
    {
        return $this->client->getLastRequest();
    }

    /**
     * Net driver change callback
     *
     * @param NetDriverInterface $net_driver
     */
    public function onNetDriverChanged(NetDriverInterface $net_driver)
    {
        $net_driver->setLogger($this->logger);
    }

    /**
     * add net driver change listener
     *
     * @param NetDriverChangeListenerInterface|callable $listener
     */
    public function addNetDriverChangeListener($listener)
    {
        $this->client->addNetDriverChangeListener($listener);
    }

    /**
     * [public] send ping
     *
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @return array
     *
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
     */
    public function sendOrder($symbol, $side, $type, $quantity, $price = NULL, $options = [])
    {
        $data = array(
            'symbol' => $symbol,
            'side' => $side,
            'type' => $type,
            'quantity' => $quantity,
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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
     * @throws PhinanceClientException
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