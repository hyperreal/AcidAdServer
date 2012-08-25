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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="banner")
 */
class Banner
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Campaign", inversedBy="banners")
     * @JoinColumn(name="banner_id", referencedColumnName="id")
     */
    protected $campaign;

    /**
     * @ORM\Column(type="string")
     * @Assert\File()
     */
    protected $file;

    /**
     * @ORM\Column(type="string", length=8)
     */
    protected $extension;

    /**
     * @ORM\Column(type="integer")
     */
    protected $width;

    /**
     * @ORM\Column(type="integer")
     */
    protected $height;

    /**
     * @ORM\Column(type="bannertype")
     * @Assert\Choice(callback={"BannerType", "getValidTypes"})
     */
    protected $type; //scyscrapper/popup/popunder/banner

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", name="link_title", nullable=true)
     */
    protected $linkTitle;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     */
    protected $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @OneToMany(targetEntity="BannerZoneReference", mappedBy="zone")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $zones;


    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }

    public function getCampaign()
    {
        return $this->campaign;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setLinkTitle($linkTitle)
    {
        $this->linkTitle = $linkTitle;
    }

    public function getLinkTitle()
    {
        return $this->linkTitle;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setZones($zones)
    {
        $this->zones = $zones;
    }

    public function getZones()
    {
        return $this->zones;
    }

    public function addZone(Zone $zone)
    {
        $this->zones->add($zone);
    }
}
