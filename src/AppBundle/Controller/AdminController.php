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
        if (!$this->isGranted('ROLE_USER')) {
            return $this->createNotFoundException();
        }

        return ['galleries' => $this->getGalleries()];
    }

    protected function getGalleries()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Gallery');
        $galleries = $repo->findBy([], ['title' => 'ASC']);

        return $galleries;
    }

}