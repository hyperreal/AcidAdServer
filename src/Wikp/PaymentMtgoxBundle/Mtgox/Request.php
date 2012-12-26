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

    /**
     * @return string
     */
    public function getParametersAsQueryString()
    {
        return http_build_query(
            $this->parameters->all(),
            self::NUMERIC_PREFIX,
            self::ARG_SEPARATOR
        );
    }
}
