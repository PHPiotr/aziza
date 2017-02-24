<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Finder;

class GalleryController extends Controller
{

    private $slugs = [
        'pokoj-niebieski',
        'pokoj-zielony',
        'pokoj-karmelowy',
        'pokoj-pomaranczowy',
        'apartament-na-poddaszu',
        'willa-aziza',
    ];

    private $names = [
        'pokoj-niebieski' => 'Pokój niebieski',
        'pokoj-zielony' => 'Pokój zielony',
        'pokoj-karmelowy' => 'Pokój karmelowy',
        'pokoj-pomaranczowy' => 'Pokój pomarańczowy',
        'apartament-na-poddaszu' => 'Apartament na poddaszu',
        'willa-aziza' => 'Willa Aziza',
    ];

    /**
     * @Route("/galeria/{slug}", name="gallery")
     * @Template()
     */
    public function indexAction(Request $request, $slug = null)
    {
        if (!$slug) {
            return [];
        }
        if (!in_array($slug, $this->slugs)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()->in('../web/img/galerie/' . $slug . '/thumbnail');

        $images = [];
        foreach ($finder as $file) {
            $images[] = $file->getRelativePathname();
        }

        $name = $this->names[$slug];
        $names = explode(' ', $name);
        $name_first = $names[0];
        unset($names[0]);
        $name_last = implode(' ', $names);

        return $this->render('AppBundle:Gallery:gallery.html.twig', [
                    'name_first' => $name_first,
                    'name_last' => $name_last,
                    'slug' => $slug,
                    'images' => $images,
        ]);
    }

}
