<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox;

use Wikp\PaymentMtgoxBundle\Mtgox\Request;
use Wikp\PaymentMtgoxBundle\Mtgox\Response;
use Wikp\PaymentMtgoxBundle\Exception\ApiCallException;

class Client
{
    const API_URL = 'https://mtgox.com/api/1/%s';

    private $curl;
    private $apiKey;
    private $apiSecret;
    private $rawResponse;
    private $initialized = false;

    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Returns only valid responses.
     *
     * @param Request $request
     * @return Response
     * @throws Wikp\PaymentMtgoxBundle\Exception\ApiCallException
     */
    public function rawRequest(Request $request)
    {
        $this->initialize();
        $this->setHttpHeaders($request);
        $this->setCommonCurlHeaders($request);
        $this->execute();

        return $this->parseResponse($request);
    }

    private function parseResponse(Request $request)
    {
        $response = new Response($this->rawResponse);

        if ($response->isError()) {
            $exception = new ApiCallException($response->getErrorMessage());
            $exception->setRequest($request);
            throw $exception;
        }

        return $response;
    }

    private function execute()
    {
        $this->rawResponse = $this->lowLevelExecute();
    }

    protected function lowLevelExecute()
    {
        return curl_exec($this->curl);
    }

    protected function setCommonCurlHeaders(Request $request)
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->createRequestUrl($request->getMethod()));
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $request->getParametersAsQueryString());
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
    }

    private function createRequestUrl($method)
    {
        return sprintf(self::API_URL, $method);
    }

    protected function setHttpHeaders(Request $request)
    {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->getHttpHeaders($request));
    }

    protected function getHttpHeaders(Request $request)
    {
        return array(
            'Rest-Key: ' . $this->apiKey,
            'Rest-Sign: ' . $this->getRestSign($request),
            'Content-type: application/x-www-form-urlencoded',
            'Accept: application/json, text/javascript, */*; q=0.01',
        );
    }

    private function initialize($force = false)
    {
        if ($this->initialized && !$force) {
            return;
        }

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->getUserAgent());

        $this->initialized = true;
    }

    private function getUserAgent()
    {
        return 'Mozilla/4.0 (compatible; WikpPaymentMtgoxBundle; PHP/'.phpversion().')';
    }

    private function getRestSign(Request $request)
    {
        return base64_encode(
            hash_hmac(
                'sha512',
                $request->getParametersAsQueryString(),
                base64_decode($this->apiSecret),
                true
            )
        );
    }
}
