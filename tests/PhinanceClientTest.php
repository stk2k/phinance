<?php
use NetDriver\Http\HttpGetRequest;

use Phinance\PhinanceClient;
use Phinance\PhinanceApi;

class PhinanceClientTest extends PHPUnit_Framework_TestCase
{
    /** @var PhinanceClient */
    private $client;
    
    protected function setUp()
    {
        $api_key = getenv('BINANCE_API_KEY');
        $api_secret = getenv('BINANCE_API_SECRET');
        
        $this->assertGreaterThan(0,strlen($api_key),'Plase set environment variable(BINANCE_API_KEY) before running this test.');
        $this->assertGreaterThan(0,strlen($api_secret),'Plase set environment variable(BINANCE_API_SECRET) before running this test.');
        
        $this->client = new PhinanceClient($api_key, $api_secret);
    
        sleep(1);
    }

    public function testGetTicker()
    {
        $time = $this->client->getTime();
    
        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();
    
        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::TIME, $req->getUrl() );
        $this->assertInternalType('float', $time );
    }

    public function testGetExchangeInfo()
    {
        $ex_info = $this->client->getExchangeInfo();

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::EXCHANGEINFO, $req->getUrl() );
        $this->assertInternalType('array', $ex_info );
        $this->assertSame(5, count($ex_info) );
        $this->assertSame(true, isset($ex_info['timezone']) );
        $this->assertSame(true, isset($ex_info['serverTime']) );
        $this->assertSame(true, isset($ex_info['rateLimits']) );
        $this->assertSame(true, isset($ex_info['exchangeFilters']) );
        $this->assertSame(true, isset($ex_info['symbols']) );
        $this->assertInternalType('string', $ex_info['timezone'] );
        $this->assertInternalType('float', $ex_info['serverTime'] );
        $this->assertInternalType('array', $ex_info['rateLimits'] );
        $this->assertInternalType('array', $ex_info['exchangeFilters'] );
        $this->assertInternalType('array', $ex_info['symbols'] );
    }

    public function testGetDepth()
    {
        $depth = $this->client->getDepth('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::DEPTH . '?symbol=ETHBTC', $req->getUrl() );
        $this->assertInternalType('array', $depth );
        $this->assertSame(3, count($depth) );
        $this->assertSame(true, isset($depth['lastUpdateId']) );
        $this->assertSame(true, isset($depth['bids']) );
        $this->assertSame(true, isset($depth['asks']) );
        $this->assertInternalType('int', $depth['lastUpdateId'] );
        $this->assertInternalType('array', $depth['bids'] );
        $this->assertInternalType('array', $depth['asks'] );
    }

    public function testGetTrades()
    {
        $trades = $this->client->getTrades('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::TRADES . '?symbol=ETHBTC', $req->getUrl() );
        $this->assertInternalType('array', $trades );
        $this->assertGreaterThan(0, count($trades) );
        $this->assertSame(true, isset($trades[0]['id']) );
        $this->assertSame(true, isset($trades[0]['price']) );
        $this->assertSame(true, isset($trades[0]['qty']) );
        $this->assertSame(true, isset($trades[0]['time']) );
        $this->assertSame(true, isset($trades[0]['isBuyerMaker']) );
        $this->assertSame(true, isset($trades[0]['isBestMatch']) );
        $this->assertInternalType('int', $trades[0]['id'] );
        $this->assertInternalType('string', $trades[0]['price'] );
        $this->assertInternalType('string', $trades[0]['qty'] );
        $this->assertInternalType('float', $trades[0]['time'] );
        $this->assertInternalType('bool', $trades[0]['isBuyerMaker'] );
        $this->assertInternalType('bool', $trades[0]['isBestMatch'] );
    }

    public function testGetHistoricalTrades()
    {
        $trades = $this->client->getHistoricalTrades('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::HISTORICALTRADES . '?symbol=ETHBTC', $req->getUrl() );
        $this->assertInternalType('array', $trades );
        $this->assertGreaterThan(0, count($trades) );
        $this->assertSame(true, isset($trades[0]['id']) );
        $this->assertSame(true, isset($trades[0]['price']) );
        $this->assertSame(true, isset($trades[0]['qty']) );
        $this->assertSame(true, isset($trades[0]['time']) );
        $this->assertSame(true, isset($trades[0]['isBuyerMaker']) );
        $this->assertSame(true, isset($trades[0]['isBestMatch']) );
        $this->assertInternalType('int', $trades[0]['id'] );
        $this->assertInternalType('string', $trades[0]['price'] );
        $this->assertInternalType('string', $trades[0]['qty'] );
        $this->assertInternalType('float', $trades[0]['time'] );
        $this->assertInternalType('bool', $trades[0]['isBuyerMaker'] );
        $this->assertInternalType('bool', $trades[0]['isBestMatch'] );
    }

    public function testGetKlines()
    {
        $klines = $this->client->getKlines('ETHBTC', '1d');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::KLINES . '?symbol=ETHBTC&interval=1d', $req->getUrl() );
        $this->assertInternalType('array', $klines );
        $this->assertGreaterThan(0, count($klines) );
        $this->assertSame(true, isset($klines[0][0]) );
        $this->assertInternalType('float', $klines[0][0] );
    }

    public function testGetTicker24hr()
    {
        $ticker24hr = $this->client->getTicker24hr('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::TICKER_24HR . '?symbol=ETHBTC', $req->getUrl() );
        $this->assertInternalType('array', $ticker24hr );
        $this->assertSame(21, count($ticker24hr) );
        $this->assertSame(true, isset($ticker24hr['symbol']) );
        $this->assertSame(true, isset($ticker24hr['priceChange']) );
        $this->assertSame(true, isset($ticker24hr['priceChangePercent']) );
        $this->assertSame(true, isset($ticker24hr['weightedAvgPrice']) );
        $this->assertSame(true, isset($ticker24hr['prevClosePrice']) );
        $this->assertSame(true, isset($ticker24hr['lastPrice']) );
        $this->assertSame(true, isset($ticker24hr['lastQty']) );
        $this->assertSame(true, isset($ticker24hr['bidPrice']) );
        $this->assertSame(true, isset($ticker24hr['bidQty']) );
        $this->assertSame(true, isset($ticker24hr['askPrice']) );
        $this->assertSame(true, isset($ticker24hr['askQty']) );
        $this->assertSame(true, isset($ticker24hr['openPrice']) );
        $this->assertSame(true, isset($ticker24hr['highPrice']) );
        $this->assertSame(true, isset($ticker24hr['lowPrice']) );
        $this->assertSame(true, isset($ticker24hr['volume']) );
        $this->assertSame(true, isset($ticker24hr['quoteVolume']) );
        $this->assertSame(true, isset($ticker24hr['openTime']) );
        $this->assertSame(true, isset($ticker24hr['closeTime']) );
        $this->assertSame(true, isset($ticker24hr['firstId']) );
        $this->assertSame(true, isset($ticker24hr['lastId']) );
        $this->assertSame(true, isset($ticker24hr['count']) );
        $this->assertInternalType('string', $ticker24hr['symbol'] );
        $this->assertInternalType('string', $ticker24hr['priceChange'] );
        $this->assertInternalType('string', $ticker24hr['priceChangePercent'] );
        $this->assertInternalType('string', $ticker24hr['weightedAvgPrice'] );
        $this->assertInternalType('string', $ticker24hr['prevClosePrice'] );
        $this->assertInternalType('string', $ticker24hr['lastPrice'] );
        $this->assertInternalType('string', $ticker24hr['lastQty'] );
        $this->assertInternalType('string', $ticker24hr['bidPrice'] );
        $this->assertInternalType('string', $ticker24hr['bidQty'] );
        $this->assertInternalType('string', $ticker24hr['askPrice'] );
        $this->assertInternalType('string', $ticker24hr['askQty'] );
        $this->assertInternalType('string', $ticker24hr['openPrice'] );
        $this->assertInternalType('string', $ticker24hr['highPrice'] );
        $this->assertInternalType('string', $ticker24hr['lowPrice'] );
        $this->assertInternalType('string', $ticker24hr['volume'] );
        $this->assertInternalType('string', $ticker24hr['quoteVolume'] );
        $this->assertInternalType('float', $ticker24hr['openTime'] );
        $this->assertInternalType('float', $ticker24hr['closeTime'] );
        $this->assertInternalType('int', $ticker24hr['firstId'] );
        $this->assertInternalType('int', $ticker24hr['lastId'] );
        $this->assertInternalType('int', $ticker24hr['count'] );
    }

    public function testGetTickerPrice()
    {
        $ticker_price = $this->client->getTickerPrice('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::TICKER_PRICE . '?symbol=ETHBTC', $req->getUrl() );
        $this->assertInternalType('array', $ticker_price );
        $this->assertSame(2, count($ticker_price) );
        $this->assertSame(true, isset($ticker_price['symbol']) );
        $this->assertSame(true, isset($ticker_price['price']) );
        $this->assertInternalType('string', $ticker_price['symbol'] );
        $this->assertInternalType('string', $ticker_price['price'] );
    }

    public function testGetTickerBookTicker()
    {
        $ticker_bookticker = $this->client->getTickerBookTicker('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $this->assertEquals(PhinanceApi::ENDPOINT . PhinanceApi::TICKER_BOOKTICKER . '?symbol=ETHBTC', $req->getUrl() );
        $this->assertInternalType('array', $ticker_bookticker );
        $this->assertSame(5, count($ticker_bookticker) );
        $this->assertSame(true, isset($ticker_bookticker['symbol']) );
        $this->assertSame(true, isset($ticker_bookticker['bidPrice']) );
        $this->assertSame(true, isset($ticker_bookticker['bidQty']) );
        $this->assertSame(true, isset($ticker_bookticker['askPrice']) );
        $this->assertSame(true, isset($ticker_bookticker['askQty']) );
        $this->assertInternalType('string', $ticker_bookticker['symbol'] );
        $this->assertInternalType('string', $ticker_bookticker['bidPrice'] );
        $this->assertInternalType('string', $ticker_bookticker['bidQty'] );
        $this->assertInternalType('string', $ticker_bookticker['askPrice'] );
        $this->assertInternalType('string', $ticker_bookticker['askQty'] );
    }

    public function testGetOpenOrders()
    {
        $open_orders = $this->client->getOpenOrders('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $expected_url_pattern = preg_quote(PhinanceApi::ENDPOINT . PhinanceApi::OPENORDERS . '?') . 'symbol=ETHBTC&timestamp=([0-9]*)&signature=([0-9a-f]*)';
        $this->assertRegExp('@^' . $expected_url_pattern . '$@', $req->getUrl() );
        $this->assertInternalType('array', $open_orders );
        $this->assertGreaterThanOrEqual(0, count($open_orders) );
    }

    public function testGetAllOrders()
    {
        $all_orders = $this->client->getAllOrders('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $expected_url_pattern = preg_quote(PhinanceApi::ENDPOINT . PhinanceApi::ALLORDERS . '?') . 'symbol=ETHBTC&timestamp=([0-9]*)&signature=([0-9a-f]*)';
        $this->assertRegExp('@^' . $expected_url_pattern . '$@', $req->getUrl() );
        $this->assertInternalType('array', $all_orders );
        $this->assertGreaterThan(0, count($all_orders) );
        $this->assertSame(true, isset($all_orders[0]['symbol']) );
        $this->assertSame(true, isset($all_orders[0]['orderId']) );
        $this->assertSame(true, isset($all_orders[0]['clientOrderId']) );
        $this->assertSame(true, isset($all_orders[0]['price']) );
        $this->assertSame(true, isset($all_orders[0]['origQty']) );
        $this->assertSame(true, isset($all_orders[0]['executedQty']) );
        $this->assertSame(true, isset($all_orders[0]['status']) );
        $this->assertSame(true, isset($all_orders[0]['timeInForce']) );
        $this->assertSame(true, isset($all_orders[0]['type']) );
        $this->assertSame(true, isset($all_orders[0]['side']) );
        $this->assertSame(true, isset($all_orders[0]['stopPrice']) );
        $this->assertSame(true, isset($all_orders[0]['icebergQty']) );
        $this->assertSame(true, isset($all_orders[0]['time']) );
        $this->assertSame(true, isset($all_orders[0]['isWorking']) );
        $this->assertInternalType('string', $all_orders[0]['symbol'] );
        $this->assertInternalType('int', $all_orders[0]['orderId'] );
        $this->assertInternalType('string', $all_orders[0]['clientOrderId'] );
        $this->assertInternalType('string', $all_orders[0]['price'] );
        $this->assertInternalType('string', $all_orders[0]['origQty'] );
        $this->assertInternalType('string', $all_orders[0]['executedQty'] );
        $this->assertInternalType('string', $all_orders[0]['status'] );
        $this->assertInternalType('string', $all_orders[0]['timeInForce'] );
        $this->assertInternalType('string', $all_orders[0]['type'] );
        $this->assertInternalType('string', $all_orders[0]['side'] );
        $this->assertInternalType('string', $all_orders[0]['stopPrice'] );
        $this->assertInternalType('string', $all_orders[0]['icebergQty'] );
        $this->assertInternalType('float', $all_orders[0]['time'] );
        $this->assertInternalType('bool', $all_orders[0]['isWorking'] );
    }

    public function testGetAcount()
    {
        $account = $this->client->getAcount();

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $expected_url_pattern = preg_quote(PhinanceApi::ENDPOINT . PhinanceApi::ACCOUNT . '?') . 'timestamp=([0-9]*)&signature=([0-9a-f]*)';
        $this->assertRegExp('@^' . $expected_url_pattern . '$@', $req->getUrl() );
        $this->assertInternalType('array', $account );
        $this->assertSame(9, count($account) );
        $this->assertSame(true, isset($account['makerCommission']) );
        $this->assertSame(true, isset($account['takerCommission']) );
        $this->assertSame(true, isset($account['buyerCommission']) );
        $this->assertSame(true, isset($account['sellerCommission']) );
        $this->assertSame(true, isset($account['canTrade']) );
        $this->assertSame(true, isset($account['canWithdraw']) );
        $this->assertSame(true, isset($account['canDeposit']) );
        $this->assertSame(true, isset($account['updateTime']) );
        $this->assertSame(true, isset($account['balances']) );
        $this->assertInternalType('int', $account['makerCommission'] );
        $this->assertInternalType('int', $account['takerCommission'] );
        $this->assertInternalType('int', $account['buyerCommission'] );
        $this->assertInternalType('int', $account['sellerCommission'] );
        $this->assertInternalType('bool', $account['canTrade'] );
        $this->assertInternalType('bool', $account['canWithdraw'] );
        $this->assertInternalType('bool', $account['canDeposit'] );
        $this->assertInternalType('float', $account['updateTime'] );
        $this->assertInternalType('array', $account['balances'] );
    }

    public function testGetMyTrades()
    {
        $trades = $this->client->getMyTrades('ETHBTC');

        /** @var HttpGetRequest $req */
        $req = $this->client->getLastRequest();

        $expected_url_pattern = preg_quote(PhinanceApi::ENDPOINT . PhinanceApi::MYTRADES . '?') . 'symbol=ETHBTC&timestamp=([0-9]*)&signature=([0-9a-f]*)';
        $this->assertRegExp('@^' . $expected_url_pattern . '$@', $req->getUrl() );
        $this->assertInternalType('array', $trades );
        $this->assertGreaterThanOrEqual(0, count($trades) );
        $this->assertSame(true, isset($trades[0]['id']) );
        $this->assertSame(true, isset($trades[0]['orderId']) );
        $this->assertSame(true, isset($trades[0]['price']) );
        $this->assertSame(true, isset($trades[0]['qty']) );
        $this->assertSame(true, isset($trades[0]['commission']) );
        $this->assertSame(true, isset($trades[0]['commissionAsset']) );
        $this->assertSame(true, isset($trades[0]['time']) );
        $this->assertSame(true, isset($trades[0]['isBuyer']) );
        $this->assertSame(true, isset($trades[0]['isMaker']) );
        $this->assertSame(true, isset($trades[0]['isBestMatch']) );
        $this->assertInternalType('int', $trades[0]['id'] );
        $this->assertInternalType('int', $trades[0]['orderId'] );
        $this->assertInternalType('string', $trades[0]['price'] );
        $this->assertInternalType('string', $trades[0]['qty'] );
        $this->assertInternalType('string', $trades[0]['commission'] );
        $this->assertInternalType('string', $trades[0]['commissionAsset'] );
        $this->assertInternalType('float', $trades[0]['time'] );
        $this->assertInternalType('bool', $trades[0]['isBuyer'] );
        $this->assertInternalType('bool', $trades[0]['isMaker'] );
        $this->assertInternalType('bool', $trades[0]['isBestMatch'] );
    }

}