<?php

namespace Hyper\AdsBundle\Payment\Electrum\Requests;

use Hyper\AdsBundle\Payment\Requests\AbstractOmnipayRequest;
use Symfony\Component\HttpFoundation\Request;

class ElectrumIpnRequest extends AbstractOmnipayRequest {

  const STATUS_COMPLETE = 'complete';

  private $id;
  private $hash;

  public function __construct($id, $hash) {
    $this->id = $id;
    $this->hash = $hash;
  }

  public function getId() {
    return $this->id;
  }

  public function getHash() {
    return $this->hash;
  }


  public function getStatus() {
    return self::STATUS_COMPLETE;
  }
}
