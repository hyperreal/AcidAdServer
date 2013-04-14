<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="advertisement_report")
 */
class AdvertisementReport
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Hyper\AdsBundle\Entity\Advertisement")
     * @ORM\JoinColumn(name="advertisement_id", referencedColumnName="id")
     * @var Advertisement
     */
    private $advertisement;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setAdvertisement(Advertisement $advertisement)
    {
        $this->advertisement = $advertisement;
    }

    /**
     * @return Advertisement
     */
    public function getAdvertisement()
    {
        return $this->advertisement;
    }
}
