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
     * get net driver
     *
     * @return NetDriverInterface
     */
    public function getNetDriver()
    {
        return $this->client->getNetDriver();
    }

    /**
     * [public] send ping
     *
     * @throws PhinanceClientException
     */
    public function ping()
    {
        $this->logger->debug('started ping');
        $this->client->ping();
        $this->logger->debug('finished ping');
    }

    /**
     * [public] set server time offset
     *
     * @throws PhinanceClientException
     */
    public function setServerTime()
    {
        $this->logger->debug('started setServerTime');
        $this->client->setServerTime();
        $this->logger->debug('finished setServerTime');
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
        $this->logger->debug('started getTime');
        $ret = $this->client->getTime();
        $this->logger->debug('finished getTime');
        return $ret;
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
        $this->logger->debug('started getExchangeInfo');
        $ret = $this->client->getExchangeInfo();
        $this->logger->debug('finished getExchangeInfo');
        return $ret;
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
        $this->logger->debug('started getDepth');
        $ret = $this->client->getDepth($symbol, $limit);
        $this->logger->debug('finished getDepth');
        return $ret;
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
        $this->logger->debug('started getTrades');
        $ret = $this->client->getTrades($symbol, $limit);
        $this->logger->debug('finished getTrades');
        return $ret;
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
        $this->logger->debug('started getHistoricalTrades');
        $ret = $this->client->getHistoricalTrades($symbol, $fromId, $limit);
        $this->logger->debug('finished getHistoricalTrades');
        return $ret;
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
        $this->logger->debug('started getKlines');
        $ret = $this->client->getKlines($symbol, $interval, $limit, $startTime, $endTime);
        $this->logger->debug('finished getKlines');
        return $ret;
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
        $this->logger->debug('started getTicker24hr');
        $ret = $this->client->getTicker24hr($symbol);
        $this->logger->debug('finished getTicker24hr');
        return $ret;
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
        $this->logger->debug('started getTickerPrice');
        $ret = $this->client->getTickerPrice($symbol);
        $this->logger->debug('finished getTickerPrice');
        return $ret;
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
        $this->logger->debug('started getTickerBookTicker');
        $ret = $this->client->getTickerBookTicker($symbol);
        $this->logger->debug('finished getTickerBookTicker');
        return $ret;
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
        $this->logger->debug('started getOpenOrders');
        $ret = $this->client->getOpenOrders($symbol, $recvWindow);
        $this->logger->debug('finished getOpenOrders');
        return $ret;
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
        $this->logger->debug('started getAllOrders');
        $ret = $this->client->getAllOrders($symbol, $orderId, $limit, $recvWindow);
        $this->logger->debug('finished getAllOrders');
        return $ret;
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
        $this->logger->debug('started sendOrder');
        $ret = $this->client->sendOrder($symbol, $side, $type, $quantity, $price, $options);
        $this->logger->debug('finished sendOrder');
        return $ret;
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
        $this->logger->debug('started cancelOrder');
        $ret = $this->client->cancelOrder($symbol, $orderId, $origClientOrderId, $recvWindow);
        $this->logger->debug('finished cancelOrder');
        return $ret;
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
        $this->logger->debug('started getAcount');
        $ret = $this->client->getAcount($recvWindow);
        $this->logger->debug('finished getAcount');
        return $ret;
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
        $this->logger->debug('started getMyTrades');
        $ret = $this->client->getMyTrades($symbol, $limit, $fromId, $recvWindow);
        $this->logger->debug('finished getMyTrades');
        return $ret;
    }
}