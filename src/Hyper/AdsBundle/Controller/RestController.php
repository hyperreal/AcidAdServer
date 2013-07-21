<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class RestController extends Controller
{
    protected function getInvalidTokenResponse()
    {
        return $this->getJsonErrorResponse('Invalid token', 401);
    }

    protected function getJsonErrorResponse($message, $statusCode)
    {
        return $this->getJsonResponse(
            array(
                's' => false,
                'm' => $message
            ),
            $statusCode
        );
    }

    protected function getJsonResponse($content, $statusCode = 200)
    {
        return new Response(
            json_encode($content),
            $statusCode,
            array(
                'Content-type' => 'application/json; charset=utf-8',
            )
        );
    }
}
