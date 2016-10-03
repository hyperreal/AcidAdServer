<?php

namespace Hyper\AdsBundle\Payment\Electrum\Gateway\Message;


use Omnipay\Common\Message\AbstractResponse;

class PurchaseResponse extends AbstractResponse {


  /*
{
  "jsonrpc": "2.0",
  "result": {
    "status": "Unknown",
    "amount (BTC)": "0.04",
    "index_url": "https://hyperre.al/bitcoin/index.html?id=522c6cedf0",
    "memo": "Test dla Fajki upływający za tydzień",
    "time": 1474370401,
    "URI": "bitcoin:1Pj1464PxGHqsX5U7V1nxc2bV6tRMdRoq1?amount=0.04&r=https://hyperre.al/bitcoin/522c6cedf0",
    "amount": 4000000,
    "exp": 604800,
    "address": "1Pj1464PxGHqsX5U7V1nxc2bV6tRMdRoq1",
    "request_url": "https://hyperre.al/bitcoin/522c6cedf0",
    "id": "522c6cedf0"
  },
  "id": "curltext"
}
   */


  public function getRequestUrl() {
    return $this->data['result']['request_url'];
  }

  public function getRedirectUrl() {
    return $this->getRequestUrl();
  }

  public function getMemo() {
    return $this->data['result']['memo'];
  }

  public function getTime() {
    return $this->data['result']['time'];
  }

  public function getStatus() {
    return $this->data['result']['status'];
  }

  public function getId() {
    return $this->data['result']['id'];
  }

  /**
   * Is the response successful?
   *
   * @return boolean
   */
  public function isSuccessful() {
    return true;
  }
}