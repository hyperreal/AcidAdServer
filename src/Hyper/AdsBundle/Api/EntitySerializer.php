<?php

namespace Hyper\AdsBundle\Api;

use Hyper\AdsBundle\Exception\InvalidArgumentException;
use Hyper\AdsBundle\Entity\Announcement;
use Symfony\Component\Routing\RouterInterface;

class EntitySerializer implements EntitySerializerInterface
{
    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function toJsonArray(array $objects)
    {
        $serialized = array();

        foreach ($objects as $object) {
            $serialized[] = $this->toJson($object);
        }

        return $serialized;
    }

    public function toJson($object, $full = false)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Only object can be serialized');
        }

        $class = new \ReflectionClass($object);
        $methodName = 'convert' . ucfirst($class->getShortName());

        if (!method_exists($this, $methodName)) {
            throw new InvalidArgumentException("Serializer for class $class was not found.");
        }

        return $this->$methodName($object, !!$full);
    }

    private function convertAnnouncement(Announcement $announcement, $full)
    {
        $serialized = array(
            'id' => $announcement->getId(),
            'userName' => $announcement->getAdvertiser()->getUsername(),
            'uid' => $announcement->getAdvertiser()->getId(),
            'title' => $announcement->getTitle(),
            'addDate' => $announcement->getAddDate()->getTimestamp(),
            'type' => $announcement->getAnnouncementPaymentType(),
            'content' => $announcement->getDescription(),
            'navigation' => array()
        );

        if ($full) {
            $serialized['navigation']['list'] = $this->router->generate('api_announcement_list', array(), true);
        } else {
            unset($serialized['content']);
            $serialized['navigation']['full'] = $this->router->generate(
                'api_announcement',
                array('announcement' => $announcement->getId()),
                true
            );
        }

        return $serialized;
    }
}
