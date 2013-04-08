<?php

namespace Hyper\AdsBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RestViewEventListener implements EventSubscriberInterface
{
    private $container;
    private $reader;

    public function __construct(ContainerInterface $container, Reader $reader)
    {
        $this->container = $container;
        $this->reader = $reader;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'responseListener',
            KernelEvents::EXCEPTION => 'exceptionListener',
            KernelEvents::REQUEST => 'tokenValidationListener',
            KernelEvents::CONTROLLER => 'controllerListener',
        );
    }

    public function controllerListener(FilterControllerEvent $event)
    {
    }

    public function tokenValidationListener(GetResponseEvent $event)
    {
        $controller = $event->getRequest()->get('_controller');
        $controller = substr($controller, 0, strpos($controller, ':'));
        $class = new \ReflectionClass($controller);
        if (!$class->isSubclassOf('Hyper\AdsBundle\Controller\RestController')) {
            return;
        }

        $token = $event->getRequest()->headers->get('X-Acid-Auth');
        if (empty($token)) {
            $event->setResponse($this->getErrorResponse('Invalid token', 401));
            return;
        }

        $tokenEntity = $this->container->get('doctrine.orm.entity_manager')->getRepository('HyperAdsBundle:ApiToken')->find($token);
        if (empty($tokenEntity)) {
            return $this->getErrorResponse('Invalid token', 401);
        }
    }

    public function responseListener(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getResponse();
        if ($response && $response->headers->get('Content-type') == 'application/json') {
            return;
        }

        $controllerResult = $event->getControllerResult();
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->add(
            array(
                'Content-type' => 'application/json'
            )
        );
        $method = new \ReflectionMethod($event->getRequest()->get('_controller'));
        $annotations = $this->reader->getMethodAnnotations($method);
        $full = false;
        foreach ($annotations as $annotation) {
            if ($annotation instanceof \Hyper\AdsBundle\Api\Json) {
                $full = $annotation->full;
            }
        }

        if (is_object($controllerResult)) {
            $response->setContent($this->container->get('hyper_ads.entity_serializer')->toJson($controllerResult, $full));
        } else {
            $response->setContent(json_encode($controllerResult));
        }
    }

    public function exceptionListener(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException) {
            $event->setResponse($this->getErrorResponse($exception->getMessage(), 400));
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $event->setResponse($this->getErrorResponse($exception->getMessage(), 404));
        } else {
            $event->setResponse($this->getErrorResponse($exception->getMessage(), 500));
        }
    }

    private function getErrorResponse($message, $statusCode)
    {
        return new Response(
            json_encode(
                array(
                    's' => false,
                    'm' => $message
                )
            ),
            $statusCode,
            array(
                'Content-type' => 'application/json'
            )
        );
    }
}
