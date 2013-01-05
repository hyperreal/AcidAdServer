<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox;

use Wikp\PaymentMtgoxBundle\Exception\InvalidArgumentException;

class Response
{
    const STATUS_OK = 200;

    /** @var array */
    private $response;
    private $isError = false;
    private $statusCode;

    public function __construct($rawResponse, $status = self::STATUS_OK)
    {
        $this->response = json_decode($rawResponse, true);
        $this->statusCode = (int)$status;
        if (isset($this->response['error'])
            || $this->response['result'] == 'error'
            || self::STATUS_OK != $this->statusCode
        ) {
            $this->isError = true;
        }
    }

    public function has($key)
    {
        return isset($this->response['return'][$key]);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws Wikp\PaymentMtgoxBundle\Exception\InvalidArgumentException When key is not provided
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new InvalidArgumentException('Given key is not provided');
        }

        return $this->response['return'][$key];
    }

    public function isError()
    {
        return $this->isError;
    }

    public function getErrorMessage()
    {
        return $this->response['error'];
    }
}
