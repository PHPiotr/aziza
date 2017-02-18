<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AttractionsController extends Controller
{

    /**
     * @Route("/atrakcje", name="attractions")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return [];
    }

}