<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class SecurityController
 *
 * @package AppBundle\Controller
 */
class SitemapController extends Controller
{
	/**
	 * @Route("/mapa-strony.{_format}", name="sitemap", defaults={"_format": "html"}, requirements={"_format": "xml|html"})
	 * @Template()
	 */
	public function indexAction()
	{
		return [];
	}
}

