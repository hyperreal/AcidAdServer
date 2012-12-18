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

use Hyper\AdsBundle\DBAL\ZoneType;

/**
 * @ORM\Entity
 * @ORM\Table(name="zone")
 */
class Zone
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne (targetEntity="Page", inversedBy="zones")
     * @JoinColumn(name="page_id", referencedColumnName="id")
     */
    protected $page;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @OneToMany(targetEntity="BannerZoneReference", mappedBy="zone")
     */
    protected $banners;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $enabled = 1;

    /**
     * @ORM\Column(type="integer", name="max_width")
     */
    protected $maxWidth;

    /**
     * @ORM\Column(type="integer", name="max_height")
     */
    protected $maxHeight;

    /**
     * @ORM\Column(type="zonetype")
     * @Assert\Choice(callback="getZoneTypes")
     */
    protected $type = 'desktop';

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setBanners($banners)
    {
        $this->banners = $banners;
    }

    public function getBanners()
    {
        return $this->banners;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = (bool)$enabled;
    }

    public function getEnabled()
    {
        return (bool)$this->enabled;
    }

    public function setMaxHeight($maxHeight)
    {
        $this->maxHeight = $maxHeight;
    }

    public function getMaxHeight()
    {
        return $this->maxHeight;
    }

    public function setMaxWidth($maxWidth)
    {
        $this->maxWidth = $maxWidth;
    }

    public function getMaxWidth()
    {
        return $this->maxWidth;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function __toString()
    {
        return sprintf('%s @%s (%s)', $this->getName(), $this->getPage()->getName(), $this->getType());
    }

    public static function getZoneTypes()
    {
        return ZoneType::getValidTypes();
    }
}
