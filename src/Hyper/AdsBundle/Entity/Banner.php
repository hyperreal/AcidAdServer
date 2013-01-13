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
use Hyper\AdsBundle\DBAL\AnnouncementPaymentType;
use Hyper\AdsBundle\Helper\BannerTypeDeterminer;
use Hyper\AdsBundle\Exception\InvalidArgumentException;

/**
 * @ORM\Entity(repositoryClass="Hyper\AdsBundle\Entity\AnnouncementRepository")
 */
class Banner extends Announcement
{
    const RAND_MIN = 10000000;
    const RAND_MAX = 99999999;
    const DEFAULT_PROBABILITY = 1;
    const UPLOAD_DIR = 'uploads';

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
     * @ORM\Column(type="bannertype", name="banner_type")
     * @Assert\Choice(callback="getBannerTypes")
     */
    protected $type;

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
     * @ORM\Column(type="string", name="original_file_name")
     */
    protected $originalFileName;

    /**
     * @OneToMany(targetEntity="BannerZoneReference", mappedBy="banner")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $zones;

    public function __construct()
    {
        $this->zones = new ArrayCollection();
        $this->announcementPaymentType = AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM;
        $this->type = BannerType::BANNER_TYPE_TEXT;
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

    public function getOriginalFileName()
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName($fileName)
    {
        $this->originalFileName = $fileName;
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
        if (!is_numeric($zoneId) || intval($zoneId) != $zoneId || $zoneId < 0) {
            throw new InvalidArgumentException('Zone id is invalid');
        }

        foreach ($this->zones as $zoneRef) {
            /** @var $zoneRef \Hyper\AdsBundle\Entity\BannerZoneReference */
            if ($zoneId == $zoneRef->getZone()->getId()) {
                return $zoneRef;
            }
        }

        return null;
    }

    /**
     * @param Zone $zone
     * @return Order[]
     */
    public function getOrdersInZone(Zone $zone)
    {
        $ordersInZone = array();
        foreach ($this->orders as $order) {
            /** @var $order Order */
            /** @var $orderZone Zone */
            $reference = $order->getBannerZoneReference();
            if (!empty($reference)) {
                $orderZone = $reference->getZone();
                if (!empty($orderZone) && $orderZone->getId() == $zone->getId()) {
                    $ordersInZone[] = $order;
                }
            }
        }

        return $ordersInZone;
    }

    public function getPaidToInZone(Zone $zone)
    {
        $orders = $this->getOrdersInZone($zone);

        if (empty($orders)) {
            return null;
        }

        $paidToDates = array_filter(
            array_map(
                function (Order $order) {
                    return $order->getPaymentTo();
                },
                $orders
            )
        );

        if (empty($paidToDates)) {
            return null;
        }

        return max($paidToDates);
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
        return self::UPLOAD_DIR;
    }

    public function getFileUrl()
    {
        return self::UPLOAD_DIR . '/' . $this->getPath();
    }

    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        $info         = pathinfo($this->file->getClientOriginalName());
        $filename     = $this->generateFileName($info);
        $this->file->move($this->getUploadRootDir(), $filename);

        $absolutePath = $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $filename;
        list($width, $height,/*comma left intentionally*/) = getimagesize($absolutePath);

        $this->width = $width;
        $this->height = $height;
        $this->extension = $info['extension'];
        $this->path = $filename;
        $this->originalFileName = $info['basename'];
        $this->file = null;

        $this->setBannerType();
    }

    private function setBannerType()
    {
        $determiner = new BannerTypeDeterminer($this);
        $this->type = $determiner->getType();
    }

    private function generateFileName(array $pathInfo)
    {
        return md5(mt_rand(self::RAND_MIN, self::RAND_MAX) . $pathInfo['filename']) . '.' . $pathInfo['extension'];
    }
}
