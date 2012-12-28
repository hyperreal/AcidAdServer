<?php
/**
 * @author fajka <fajka@hyperreal.info>
 * @see    https://github.com/fajka
 */

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="advertiser")
 */
class Advertiser extends BaseUser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="first_name", nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", name="last_name", nullable=true)
     */
    protected $lastName;

    /**
     * @var \Hyper\AdsBundle\Entity\Announcement[]
     *
     * @OneToMany(targetEntity="Announcement", mappedBy="advertiser")
     */
    protected $announcements;

    public function __construct()
    {
        parent::__construct();
        $this->announcements = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getBanners()
    {
        return $this->announcements;
    }

    public function addBanner(Announcement $announcement)
    {
        $this->announcements->add($announcement);
    }

    public function getAnnouncements()
    {
        return $this->announcements;
    }

    public function addAnnouncement(Announcement $announcement)
    {
        $this->announcements->add($announcement);
    }

    public function __toString()
    {
        return $this->getName() . ' (' . $this->getId() . ')';
    }
}
