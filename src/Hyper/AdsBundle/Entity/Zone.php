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
 * @ORM\Entity(repositoryClass="Hyper\AdsBundle\Entity\ZoneRepository")
 * @ORM\Table(name="zone")
 */
class Zone
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ManyToOne (targetEntity="Page", inversedBy="zones")
     * @JoinColumn(name="page_id", referencedColumnName="id")
     */
    private $page;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @OneToMany(targetEntity="BannerZoneReference", mappedBy="zone", cascade={"persist", "remove"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $banners;

    /**
     * @ORM\Column(type="smallint")
     */
    private $enabled = 1;

    /**
     * @ORM\Column(type="integer", name="max_width")
     */
    private $maxWidth;

    /**
     * @ORM\Column(type="integer", name="max_height")
     */
    private $maxHeight;

    /**
     * @ORM\Column(type="zonetype")
     * @Assert\Choice(callback="getZoneTypes")
     */
    private $type = 'desktop';

    /**
     * @ORM\Column(type="decimal", name="daily_price", scale=8, precision=14, nullable=true)
     * @ORM\ManyToOne(targetEntity="Currency")
     */
    private $dailyPrice;

    /**
     * @ORM\Column(type="decimal", name="view_price", scale=8, precision=14, nullable=true)
     */
    private $viewPrice;

    /**
     * @ORM\Column(type="decimal", name="click_price", scale=8, precision=14, nullable=true)
     */
    private $clickPrice;

    /**
     * @ORM\Column(type="integer", name="max_banners")
     */
    private $maxBanners;

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

    public function getDailyPrice()
    {
        return $this->dailyPrice;
    }

    public function setDailyPrice($dailyPrice)
    {
        $this->dailyPrice = $dailyPrice;
    }

    public function setViewPrice($viewPrice)
    {
        $this->viewPrice = (float)$viewPrice;
    }

    public function getViewPrice()
    {
        return $this->viewPrice;
    }

    public function setClickPrice($clickPrice)
    {
        $this->clickPrice = $clickPrice;
    }

    public function getClickPrice()
    {
        return $this->clickPrice;
    }

    public function getMaxBanners()
    {
        return $this->maxBanners;
    }

    public function setMaxBanners($maxBanners)
    {
        $this->maxBanners = (int)$maxBanners;
    }

    public function getBannerReferencesIds()
    {
        return array_map(
            function (BannerZoneReference $ref) {
                return $ref->getId();
            },
            $this->banners->toArray()
        );
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
