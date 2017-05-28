<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="sections")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SectionRepository")
 */
class Section
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
     * @ORM\Column(type="string", length=255)
     */
    private $filter;

    /**
     * @ORM\OneToMany(targetEntity="SectionPhoto", mappedBy="section")
     */
    private $sectionPhotos;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mainPhotoId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sectionPhotos = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Section
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

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Section
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
     * @return Section
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
     * @return Section
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
     * Add sectionPhoto
     *
     * @param \AppBundle\Entity\SectionPhoto $sectionPhoto
     *
     * @return Section
     */
    public function addSectionPhoto(\AppBundle\Entity\SectionPhoto $sectionPhoto)
    {
        $this->sectionPhotos[] = $sectionPhoto;

        return $this;
    }

    /**
     * Remove sectionPhoto
     *
     * @param \AppBundle\Entity\SectionPhoto $sectionPhoto
     */
    public function removeSectionPhoto(\AppBundle\Entity\SectionPhoto $sectionPhoto)
    {
        $this->sectionPhotos->removeElement($sectionPhoto);
    }

    /**
     * Get sectionPhotos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSectionPhotos()
    {
        return $this->sectionPhotos;
    }

    /**
     * Set filter
     *
     * @param string $filter
     *
     * @return Section
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
