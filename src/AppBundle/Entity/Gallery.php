<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="galleries")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GalleryRepository")
 */
class Gallery
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="GalleryPhoto", mappedBy="gallery")
     */
    private $galleryPhotos;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mainPhotoId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->galleryPhotos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Gallery
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getTitleFirst()
    {
        $title = explode(' ', $this->getTitle());

        return $title[0];
    }

    public function getTitleRest()
    {
        $title = explode(' ', $this->getTitle());
        unset($title[0]);

        return implode(' ', $title);
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Gallery
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Gallery
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set mainPhotoId
     *
     * @param integer $mainPhotoId
     *
     * @return Gallery
     */
    public function setMainPhotoId($mainPhotoId)
    {
        $this->mainPhotoId = $mainPhotoId;

        return $this;
    }

    /**
     * Get mainPhotoId
     *
     * @return integer
     */
    public function getMainPhotoId()
    {
        return $this->mainPhotoId;
    }

    /**
     * Add galleryPhoto
     *
     * @param \AppBundle\Entity\GalleryPhoto $galleryPhoto
     *
     * @return Gallery
     */
    public function addGalleryPhoto(\AppBundle\Entity\GalleryPhoto $galleryPhoto)
    {
        $this->galleryPhotos[] = $galleryPhoto;

        return $this;
    }

    /**
     * Remove galleryPhoto
     *
     * @param \AppBundle\Entity\GalleryPhoto $galleryPhoto
     */
    public function removeGalleryPhoto(\AppBundle\Entity\GalleryPhoto $galleryPhoto)
    {
        $this->galleryPhotos->removeElement($galleryPhoto);
    }

    /**
     * Get galleryPhotos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGalleryPhotos()
    {
        return $this->galleryPhotos;
    }
}
