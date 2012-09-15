<?php
/**
 * @author fajka <fajka@hyperreal.info>
 * @see    https://github.com/fajka
 */

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="campaign")
 */
class Campaign
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ManyToOne(targetEntity="Advertiser", inversedBy="campaigns")
     * @JoinColumn(name="advertiser_id", referencedColumnName="id")
     */
    protected $advertiser;

    /**
     * @ORM\Column(type="date", name="start_date")
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @ORM\Column(type="date", name="expire_date")
     * @var \DateTime
     */
    protected $expireDate;

    /**
     * @OneToMany(targetEntity="Banner", mappedBy="campaign")
     */
    protected $banners;


    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setAdvertiser($advertiser)
    {
        $this->advertiser = $advertiser;
    }

    public function getAdvertiser()
    {
        return $this->advertiser;
    }

    public function setBanners($banners)
    {
        $this->banners = $banners;
    }

    public function getBanners()
    {
        return $this->banners;
    }

    public function setExpireDate($expireDate)
    {
        $this->expireDate = $expireDate;
    }

    public function getExpireDate()
    {
        return $this->expireDate;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function __toString()
    {
        return $this->getName() . ' (' . $this->getId() . ')';
    }
}
