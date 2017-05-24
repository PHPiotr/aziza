<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class SecurityController
 *
 * @package AppBundle\Controller
 */
class SecurityController extends Controller
{
	/**
	 * @Route("/login", name="login")
	 * @Template()
	 */
	public function loginAction()
	{
        if ($this->isGranted('ROLE_USER')) {
			return $this->redirectToRoute('admin');
		}
		$authenticationUtils = $this->get('security.authentication_utils');

		$error = $authenticationUtils->getLastAuthenticationError();

		$lastUsername = $authenticationUtils->getLastUsername();

		return [
			'last_username' => $lastUsername,
			'error' => $error,
		];
	}

	/**
	 * @Route("/logout", name="logout")
	 */
	public function logoutAction()
	{
		$this->redirectToRoute('default');
	}
}

