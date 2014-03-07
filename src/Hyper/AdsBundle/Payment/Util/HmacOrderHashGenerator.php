<?php

namespace Hyper\AdsBundle\Payment\Util;

use Hyper\AdsBundle\Payment\OrderInterface;

class HmacOrderHashGenerator implements OrderHashGeneratorInterface
{
    private $hashAlgorithm;
    private $hashKey;

    public function __construct($hashAlgorithm, $hashKey)
    {
        $this->hashAlgorithm = $hashAlgorithm;
        $this->hashKey = $hashKey;
    }

    public function hashOrder(OrderInterface $order)
    {
        return base64_encode(hash_hmac($this->hashAlgorithm, $this->getHash($order), $this->hashKey));
    }

    private function getHash(OrderInterface $order)
    {
        return json_encode(
            array(
                'id' => $order->getId(),
                'pii' => $order->getPaymentInstruction()->getId(),
            )
        );
    }

}