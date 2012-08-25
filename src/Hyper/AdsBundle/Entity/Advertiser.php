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

/**
 * @ORM\Entity
 * @ORM\Table(name="advertiser")
 */
class Advertiser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Assert\Email(
     *      message="The email {{ email }} is not a valid e-mail address.",
     *      checkMX = false
     * )
     */
    protected $email;

    /**
     * @ORM\Column(type="string", name="first_name", nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", name="last_name", nullable=true)
     */
    protected $lastName;

    /**
     * @OneToMany(targetEntity="Campaign", mappedBy="advertiser")
     */
    protected $campaigns;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
         return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setCampaigns($campaigns)
    {
        $this->campaigns = $campaigns;
    }

    public function getCampaigns()
    {
        return $this->campaigns;
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
}
