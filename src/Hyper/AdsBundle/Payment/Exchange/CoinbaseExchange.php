<?php

namespace Hyper\AdsBundle\Payment\Exchange;

use Guzzle\Http\Client;
use Guzzle\Http\Message\MessageInterface;
use Guzzle\Http\Message\RequestInterface;
use Hyper\AdsBundle\Payment\BitcoinCurrencyExchange;

class CoinbaseExchange extends BitcoinCurrencyExchange
{
  /**
   * @var Client
   */
  private $guzzleClient;

  /**
   * @var string
   */
  private $endpoint;

  /**
   * @var string
   */
  private $accessKey;

  /**
   * @var string
   */
  private $secret;

  public function __construct($endpoint, $accessKey, $secret) {
    $this->guzzleClient = new Client();
    $this->endpoint = rtrim($this->endpoint, '/');
    $this->accessKey = $accessKey;
    $this->secret = $secret;
  }

  public function supportedCurrencies() {
    return array('EUR', 'USD');
  }

  protected function exchange($amount, $fromCurrency, $toCurrency) {

    $uri = $this->buildUri();
    $options = ['query' => ['currency' => $toCurrency]];
    $headers = ['CB-VERSION' => 2];
    if (!empty($this->accessKey)) {
      $this->addAuthenticationHeaders($uri, $headers);
    }

    /** @var $clientBuilder \Guzzle\Http\Message\RequestInterface */
    $clientBuilder = $this->guzzleClient->get($uri, $headers, $options);

    $response = $clientBuilder->send()->json();
    $rate = $response['data']['amount'];

    return floatval($amount) / floatval($rate);
  }

  private function generateSign($timestamp, $uri) {
    return hash_hmac('sha256', "{$timestamp}GET{$uri}", $this->secret);
  }

  private function addAuthenticationHeaders($uri, array &$headers) {
    $timestamp = time();
    $sign = $this->generateSign($timestamp, $uri);

    $headers['CB-ACCESS-KEY'] = $this->accessKey;
    $headers['CB-ACCESS-SIGN'] = $sign;
    $headers['CB-ACCESS-TIMESTAMP'] = $timestamp;
  }

  private function buildUri() {
    return "{$this->endpoint}/v2/prices/spot";
  }
}
