<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="api_tokens")
 */
class ApiToken
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="token", type="string")
     */
    private $token;

    /**
     * @ORM\OneToOne(targetEntity="Hyper\AdsBundle\Entity\Advertiser")
     * @ORM\JoinColumn(name="advertiser_id", referencedColumnName="id")
     */
    private $advertiser;

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setAdvertiser(Advertiser $advertiser)
    {
        $this->advertiser = $advertiser;
    }

    public function getAdvertiser()
    {
        return $this->advertiser;
    }
}
