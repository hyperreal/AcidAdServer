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
        $microTime = explode(' ', microtime());
        $parametersArray = $this->parameters->all();
        $parametersArray['nonce'] = $microTime[1] . substr($microTime[0], 2, 6);

        return http_build_query(
            $parametersArray,
            self::NUMERIC_PREFIX,
            self::ARG_SEPARATOR
        );
    }
}
