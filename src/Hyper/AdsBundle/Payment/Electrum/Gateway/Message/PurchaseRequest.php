<?php

namespace Hyper\AdsBundle\Payment\Electrum\Gateway\Message;


use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

class PurchaseRequest extends AbstractRequest
{

  public function setEndpoint($value) {
    return $this->setParameter('endpoint', $value);
  }
  
  public function getEndpoint() {
    return $this->getParameter('endpoint');
  } 

  public function setOrder($order) {
    return $this->setParameter('order', $order);
  }
  
  public function getOrder() {
    return $this->getParameter('order');
  }
  
  public function setExpirationTime($value) {
    return $this->setParameter('expirationTime', $value);
  }
  
  public function getExpirationTime() {
    return $this->getParameter('expirationTime');
  }
  
  public function setMemo($value) {
    return $this->setParameter('memo', $value);
  }
  
  public function getMemo() {
    return $this->getParameter('memo');
  }

  public function getData() {
    return array(
      "id" => "curltext",
      "method" => 'addrequest',
      "params" => array(
        'amount' => $this->getParameter('amount'),//$this->getAmount(),
        'expiration' => $this->getExpirationTime(),
        'memo' => $this->getMemo(),
      ),
    );
  }

  /**
   * Send the request with specified data
   *
   * @param  mixed $data The data to send
   * @return ResponseInterface
   */
  public function sendData($data) {

    $httpRequest = $this->httpClient->post($this->getParameter('endpoint'), null, json_encode($data));
    $httpResponse = $httpRequest->send();

    return $this->response = new PurchaseResponse($this, $httpResponse->json());
  }
}
