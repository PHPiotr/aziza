<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="default")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $base = new \AppBundle\Utils\Base($this->getDoctrine());
        return [
            'backgroundFileName' => $base->getFileFor('tlo'),
            'roomsFileName' => $base->getFileFor('komfortowe-pokoje'),
            'sorroundingsFileName' => $base->getFileFor('urokliwa-okolica'),
            'localizationFileName' => $base->getFileFor('dogodna-lokalizacja'),
            'carouselPhotos' => $base->getFilesFor('slajder'),
        ];
    }

}
