<?php

namespace Hyper\AdsBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Api\EntitySerializer;
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
    private $entityManager;
    private $reader;
    private $serializer;

    public function __construct(EntityManager $entityManager, Reader $reader, EntitySerializer $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->reader = $reader;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelView',
            KernelEvents::EXCEPTION => 'onKernelException',
            KernelEvents::REQUEST => 'onKernelRequest', //token validation
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {return;
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

        $tokenEntity = $this->entityManager->getRepository('HyperAdsBundle:ApiToken')->find($token);
        if (empty($tokenEntity)) {
            return $this->getErrorResponse('Invalid token', 401);
        }
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
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
        list($controllerObject, $controllerMethod) = explode('::', $event->getRequest()->get('_controller'));
        $method = new \ReflectionMethod($controllerObject, $controllerMethod);
        $annotations = $this->reader->getMethodAnnotations($method);
        $full = false;
        foreach ($annotations as $annotation) {
            if ($annotation instanceof \Hyper\AdsBundle\Api\Json) {
                $full = $annotation->full;
            }
        }

        if (is_object($controllerResult)) {
            $content = json_encode($this->serializer->toJson($controllerResult, $full));
        } elseif (is_array($controllerResult) && !empty($controllerResult) && is_object(current($controllerResult))) {
            $content = json_encode($this->serializer->toJsonArray($controllerResult));
        } else {
            $content = json_encode($controllerResult);
        }

        $response->setContent($content);
        $event->setResponse($response);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException) {
            $event->setResponse($this->getErrorResponse('Bad request', 400));
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $event->setResponse($this->getErrorResponse('Not found', 404));
        } else {
            $event->setResponse($this->getErrorResponse('Internal server error', 500));
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
