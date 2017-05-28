<?php
namespace AppBundle\Utils;

use Doctrine\Bundle\DoctrineBundle\Registry;

class Base
{

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function getFileFor($slug)
    {
        if (array_key_exists($slug, $this->getStore())) {
            return $this->getStore()[$slug];
        }

        $section = $this->registry->getRepository('AppBundle:Section')->findOneBy(['slug' => $slug]);
        if (!$section) {
            $this->addsToStore($slug, null);
            return null;
        }

        $mainPhotoId = $section->getMainPhotoId();
        $collection = $section->getSectionPhotos();
        if ($collection->isEmpty()) {
            $this->addToStore($slug, null);
            return null;
        }

        if (!$mainPhotoId) {
            $name = $collection->first()->getName();
            $this->addToStore($slug, $name);
            return $name;
        }

        foreach ($collection->getValues() as $photo) {
            if ($photo->getId() != $mainPhotoId) {
                continue;
            }
            $name = $photo->getName();
            $this->store['tlo'] = $name;
            $this->addToStore($slug, $name);
            return $name;
        }
        $this->addToStore($slug, null);
        return null;
    }

    public function getFilesFor($slug)
    {
        if (array_key_exists($slug, $this->getStore()['all'])) {
            return $this->getStore()['all'][$slug];
        }

        $names = $this->registry->getRepository('AppBundle:SectionPhoto')->getAllNamesBySlug($slug);

        if (!$names) {
            $this->addAllToStore($slug, null);
            return null;
        }

        $this->addAllToStore($slug, $names);

        return $names;
    }

//    protected function getStore()
//    {
//        if (!isset($_SESSION['store'])) {
//            $_SESSION['store'] = array();
//        }
//        return $_SESSION['store'];
//    }
//
//    protected function addToStore($key, $value)
//    {
//        $_SESSION['store'][$key] = $value;
//    }

    protected function getStore()
    {
        if (!isset($this->store)) {
            $this->store = ['all' => []];
        }

        return $this->store;
    }

    protected function addToStore($key, $value)
    {
        $this->store[$key] = $value;
    }

    protected function addAllToStore($key, $value)
    {
        $this->store['all'][$key] = $value;
    }
}