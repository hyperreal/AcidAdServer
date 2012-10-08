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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Hyper\AdsBundle\DBAL\BannerType;

/**
 * @ORM\Entity(repositoryClass="Hyper\AdsBundle\Entity\BannerRepository")
 * @ORM\Table(name="banner")
 */
class Banner
{
    const RAND_MIN = 10000000;
    const RAND_MAX = 99999999;
    const DEFAULT_PROBABILITY = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Campaign", inversedBy="banners")
     * @JoinColumn(name="campaign_id", referencedColumnName="id")
     */
    protected $campaign;

    /**
     * @Assert\File()
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $file;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $path;

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
     * @Assert\Choice(callback="getBannerTypes")
     */
    protected $type;

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
     * @OneToMany(targetEntity="BannerZoneReference", mappedBy="banner")
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

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
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

    public function __toString()
    {
        return $this->getId();
    }

    public static function getBannerTypes()
    {
        return BannerType::getValidTypes();
    }

    /**
     * @param $zoneId
     *
     * @return \Hyper\AdsBundle\Entity\BannerZoneReference|null
     */
    public function getReferenceInZone($zoneId)
    {
        foreach ($this->zones as $zoneRef) {
            /** @var $zoneRef \Hyper\AdsBundle\Entity\BannerZoneReference */
            if ($zoneId == $zoneRef->getZone()->getId()) {
                return $zoneRef;
            }
        }

        return null;
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads';
    }

    public function getFileUrl()
    {
        return 'uploads/' . $this->getPath();
    }

    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        $info         = pathinfo($this->file->getClientOriginalName());
        $filename     = md5(mt_rand(self::RAND_MIN, self::RAND_MAX) . $info['filename']) . '.' . $info['extension'];
        $absolutePath = $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $filename;

        $this->file->move($this->getUploadRootDir(), $filename);
        list($width, $height,) = getimagesize($absolutePath);

        $this->width     = $width;
        $this->height    = $height;
        $this->extension = $info['extension'];
        $this->path      = $filename;
        $this->file      = null;
    }
}
