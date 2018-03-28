<?php
namespace Phinance;

/**
 * Phinance API consts class
 */
final class PhinanceApi
{
    const ENDPOINT      = 'https://api.binance.com';
    
    // binance public API
    const PING              = '/api/v1/ping';
    const TIME              = '/api/v1/time';
    const EXCHANGEINFO      = '/api/v1/exchangeInfo';
    const DEPTH             = '/api/v1/depth';
    const TRADES            = '/api/v1/trades';
    const HISTORICALTRADES  = '/api/v1/historicalTrades';
    //const AGGTRADES         = '/api/v1/aggTrades';
    const KLINES            = '/api/v1/klines';
    const TICKER_24HR       = '/api/v1/ticker/24hr';
    const TICKER_PRICE      = '/api/v3/ticker/price';
    const TICKER_BOOKTICKER = '/api/v3/ticker/bookTicker';
    
    // binance private API
    const OPENORDERS        = '/api/v3/openOrders';
    const ALLORDERS         = '/api/v3/allOrders';
    const ORDER             = '/api/v3/order';
    const ACCOUNT           = '/api/v3/account';
    const MYTRADES          = '/api/v3/myTrades';
    
}