<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Contact;

class ContactController extends Controller
{

    /**
     * @Route("/kontakt", name="contact")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/kontakt/wyslij", name="contact_send")
     */
    public function sendAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('contact');
        }

        if (!$request->isMethod('post')) {
            return JsonResponse::create(['ok' => false]);
        }

        $contact = new Contact();
        $contact->email = trim($request->get('email'));
        $contact->phone = trim($request->get('phone'));
        $contact->message = trim($request->get('message'));

        $validator = $this->get('validator');
        $constraintViolationList = $validator->validate($contact);

        if (count($constraintViolationList) > 0) {
            foreach ($constraintViolationList->getIterator() as $constraintViolation) {
                $errors[$constraintViolation->getPropertyPath()] = $constraintViolation->getMessage();
            }
            return JsonResponse::create(['ok' => false, 'errors' => $errors]);
        }

        $message = \Swift_Message::newInstance()
                ->setSubject('Willa Aziza')
                ->setFrom($contact->email)
                ->setTo('willa@aziza.pl')
                ->setBody($this->renderView('AppBundle:Contact:email.html.twig', [
                    'message' => $contact->message,
                    'phone' => $contact->phone,
                    'email' => $contact->email,
                    'date' => date('d.m.Y H:i:s')
                ]), 'text/html');

        $sent = $this->get('mailer')->send($message);

        if (!$sent) {
            return JsonResponse::create(['ok' => false, 'errors' => [], 'msg' => 'Problem podczas wysyłania']);
        }

        return JsonResponse::create(['ok' => true, 'errors' => [], 'msg' => 'Wiadomość została wysłana']);
    }

}
