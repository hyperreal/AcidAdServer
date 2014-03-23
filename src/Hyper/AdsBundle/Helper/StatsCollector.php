<?php

namespace Hyper\AdsBundle\Helper;

use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Entity\BannerZoneReference;

class StatsCollector
{
    const FIELD_CLICK = 'clicks';
    const FIELD_VIEW = 'views';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    public function collectView(BannerZoneReference $bannerZone)
    {
        $bannerZone->setViews($bannerZone->getViews() + 1);
        $this->flushEntityManager($bannerZone);
    }

    public function collectClick(BannerZoneReference $bannerZone)
    {
        $bannerZone->setClicks($bannerZone->getClicks() + 1);
        $this->flushEntityManager($bannerZone);
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    private function flushEntityManager($bannerZone)
    {
        $this->getEntityManager()->persist($bannerZone);
        $this->getEntityManager()->flush();
    }
}
