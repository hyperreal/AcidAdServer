<?php
/**
 * @author fajka <fajka@hyperreal.info>
 * @see    https://github.com/fajka
 */

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="advertiser")
 */
class Advertiser extends BaseUser
{
    const DEFAULT_CURRENCY = 'EUR';

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
     * @var \Hyper\AdsBundle\Entity\Advertisement[]
     *
     * @ORM\OneToMany(targetEntity="Advertisement", mappedBy="advertiser", cascade={"persist", "remove"})
     */
    protected $advertisements;

    /**
     * @ORM\ManyToOne(targetEntity="Wikp\PaymentMtgoxBundle\Entity\Currency")
     * @ORM\JoinColumn(name="default_currency", referencedColumnName="id")
     */
    private $defaultCurrency;

    public function __construct()
    {
        parent::__construct();
        $this->advertisements = new ArrayCollection();
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
        return $this->advertisements;
    }

    public function addBanner(Advertisement $announcement)
    {
        $this->advertisements->add($announcement);
    }

    public function getAdvertisements()
    {
        return $this->advertisements;
    }

    public function addAdvertisement(Advertisement $announcement)
    {
        $this->advertisements->add($announcement);
    }

    public function getDefaultCurrency($default = self::DEFAULT_CURRENCY)
    {
        if (empty($this->defaultCurrency)) {
            return $default;
        }

        return $this->defaultCurrency;
    }

    public function setDefaultCurrency($currency)
    {
        $this->defaultCurrency = $currency;
    }

    public function __toString()
    {
        return $this->getName() . ' (' . $this->getId() . ')';
    }
}
