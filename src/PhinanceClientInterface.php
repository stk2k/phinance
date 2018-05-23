<?php
namespace Phinance;

use NetDriver\Http\HttpRequest;
use NetDriver\NetDriverInterface;

use Phinance\Exception\PhinanceClientException;

/**
 * Phinance interface
 */
interface PhinanceClientInterface
{
    /**
     * get last request
     *
     * @return HttpRequest
     */
    public function getLastRequest();

    /**
     * add net driver change listener
     *
     * @param NetDriverChangeListenerInterface|callable $listener
     */
    public function addNetDriverChangeListener($listener);

    /**
     * get net driver
     *
     * @return NetDriverInterface
     */
    public function getNetDriver();

    /**
     * set net driver
     *
     * @param NetDriverInterface $net_driver
     */
    public function setNetDriver(NetDriverInterface $net_driver);

    /**
     * [public] send ping
     *
     * @throws PhinanceClientException
     */
    public function ping();
    
    /**
     * [public] set server time offset
     *
     * @throws PhinanceClientException
     */
    public function setServerTime();
    
    /**
     * [public] get server time
     *
     * @return int
     *
     * @throws PhinanceClientException
     */
    public function getTime();
    
    /**
     * [public] get exchange info
     *
     * @return array
     *
     * @throws PhinanceClientException
     */
    public function getExchangeInfo();
    
    /**
     * [public] Order book
     *
     * @param string $symbol
     * @param int $limit
     *
     * @return array
     *
     * @throws PhinanceClientException
     */
    public function getDepth($symbol, $limit = NULL);
    
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
    public function getTrades($symbol, $limit = NULL);
    
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
    public function getHistoricalTrades($symbol, $fromId = NULL, $limit = NULL);
    
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
    public function getKlines($symbol, $interval, $limit = NULL, $startTime = NULL, $endTime = NULL);
    
    /**
     * [public] 24 hour price change statistics
     *
     * @param string $symbol
     *
     * @return array
     *
     * @throws PhinanceClientException
     */
    public function getTicker24hr($symbol = NULL);
    
    /**
     * [public] Latest price for a symbol or symbols.
     *
     * @param string $symbol
     *
     * @return array
     *
     * @throws PhinanceClientException
     */
    public function getTickerPrice($symbol = NULL);
    
    /**
     * [public] Best price/qty on the order book for a symbol or symbols.
     *
     * @param string $symbol
     *
     * @return array
     *
     * @throws PhinanceClientException
     */
    public function getTickerBookTicker($symbol = NULL);
    
    /**
     * [USER_DATA] Get all open orders on a symbol.
     *
     * @param string $symbol
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws PhinanceClientException
     */
    public function getOpenOrders($symbol = NULL, $recvWindow = NULL);
    
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
    public function getAllOrders($symbol = NULL, $orderId = NULL, $limit = NULL, $recvWindow = NULL);

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
    public function sendOrder($symbol, $side, $type, $quantity, $price = NULL, $options = []);

    /**
     * [TRADE] Cancel an active order.
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
    public function cancelOrder($symbol, $orderId = NULL, $origClientOrderId = NULL, $recvWindow = NULL);
    
    /**
     * [USER_DATA] Get current account information.
     *
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws PhinanceClientException
     */
    public function getAcount($recvWindow = NULL);
    
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
    public function getMyTrades($symbol, $limit = NULL, $fromId = NULL, $recvWindow = NULL);
}