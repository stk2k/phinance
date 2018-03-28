<?php
namespace Phinance;

use Phinance\Exception\ApiClientException;
use Phinance\Exception\ServerResponseFormatException;

/**
 * Phinance interface
 */
interface IPhinanceClient
{
    /**
     * [public] send ping
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function ping();
    
    /**
     * [public] set server time offset
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function setServerTime();
    
    /**
     * [public] get server time
     *
     * @return int
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function getTime();
    
    /**
     * [public] get exchange info
     *
     * @return object
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function getExchangeInfo();
    
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
    public function getDepth($symbol, $limit = NULL);
    
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function getKlines($symbol, $interval, $limit = NULL, $startTime = NULL, $endTime = NULL);
    
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
    public function getTicker24hr($symbol = NULL);
    
    /**
     * [public] Latest price for a symbol or symbols.
     *
     * @param string $symbol
     *
     * @return array
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function getTickerPrice($symbol = NULL);
    
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
    public function getTickerBookTicker($symbol = NULL);
    
    /**
     * [USER_DATA] Get all open orders on a symbol.
     *
     * @param string $symbol
     * @param int $recvWindow
     *
     * @return array
     *
     * @throws ServerResponseFormatException
     * @throws ApiClientException
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function getAllOrders($symbol = NULL, $orderId = NULL, $limit = NULL, $recvWindow = NULL);
    
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function cancelOrder($symbol, $orderId = NULL, $origClientOrderId = NULL, $recvWindow = NULL);
    
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
     * @throws ServerResponseFormatException
     * @throws ApiClientException
     */
    public function getMyTrades($symbol, $limit = NULL, $fromId = NULL, $recvWindow = NULL);
}