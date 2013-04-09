<?php

namespace Hyper\AdsBundle\Api;

use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Exception\InvalidArgumentException;

//@todo factory service!!
class AnnouncementArrayConverter implements ArrayConverterInterface
{
    public function toArray($announcement, $full = false)
    {
        if (!($announcement instanceof Announcement)) {
            throw new InvalidArgumentException('Only objects of Announcement class will be transformed here');
        }

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
