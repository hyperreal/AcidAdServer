<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class Advertisement
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="date", name="expire_date")
     * @var \DateTime
     */
    protected $expireDate;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getExpireDate()
    {
        return $this->expireDate;
    }

    public function setExpireDate(\DateTime $date)
    {
        $this->expireDate = $date;
    }

    public function isExpired()
    {
        return $this->getExpireDate() < new \DateTime();
    }
}
