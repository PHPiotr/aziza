<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class OfferController extends Controller
{

    /**
     * @Route("/oferta", name="offer")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return [];
    }

}
