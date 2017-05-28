<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LocalizationController extends Controller
{

    /**
     * @Route("/lokalizacja", name="localization")
     * @Template()
     */
    public function indexAction(Request $request)
    {
            $base = new \AppBundle\Utils\Base($this->getDoctrine());
        return [
            'backgroundFileName' => $base->getFileFor('tlo'),
            'title' => 'Willa Aziza w Kościelisku',
            'content' => 'Kościelisko usytuowane jest na zachód od Zakopanego, '
            . 'składa się z wielu osiedli rozrzuconych na zboczach pasma gubałowskiego. '
            . 'Nazwa wywodzi się od polany Stare Kościeliska w pobliskiej Dolinie Kościeliskiej.',
        ];
    }

}
