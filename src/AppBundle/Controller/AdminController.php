<?php

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{

    /**
     * @Route("/admin", name="admin")
     * @Template()
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        return [
            'galleries' => $this->getGalleries(),
            'sections' => $this->getSections(),
        ];
    }

    protected function getGalleries()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Gallery');
        $galleries = $repo->findBy([], ['title' => 'ASC']);

        return $galleries;
    }

    protected function getSections()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Section');
        $galleries = $repo->findBy([], ['title' => 'ASC']);

        return $galleries;
    }

}