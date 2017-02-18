<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class GalleryController extends Controller
{

    /**
     * @Route("/galeria", name="gallery")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return [];
    }

}