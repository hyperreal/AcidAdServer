<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox;

use Symfony\Component\HttpFoundation\ParameterBag;

class Request
{
    const NUMERIC_PREFIX = '';
    const ARG_SEPARATOR = '&';

    /** @var \Symfony\Component\HttpFoundation\ParameterBag */
    private $parameters;

    /** @var string */
    private $method;

    private $nonce;

    public function __construct($method, ParameterBag $params)
    {
        $this->method = $method;
        $this->parameters = $params;

    }

    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getParametersAsQueryString()
    {
        $parametersArray = $this->parameters->all();
        $parametersArray['nonce'] = $this->getNonce();

        return http_build_query(
            $parametersArray,
            self::NUMERIC_PREFIX,
            self::ARG_SEPARATOR
        );
    }

    private function getNonce()
    {
        if (empty($this->nonce)) {
            $microTime = explode(' ', microtime());
            $this->nonce = $microTime[1] . substr($microTime[0], 2, 6);
        }

        return $this->nonce;
    }
}
