<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Contact
{

    /**
     * @Assert\Length(max = 15, maxMessage = "Za długi numer telefonu ({{ limit }} znaków max.)")
     */
    public $phone;

    /**
     * @Assert\NotBlank(message = "Podaj swój email")
     * @Assert\Email(message = "Nieprawidłowy email", checkMX = true)
     */
    public $email;

    /**
     * @Assert\NotBlank(message = "Brak wiadomości")
     * @Assert\Length(
     *      min = 10,
     *      max = 500,
     *      minMessage = "Za mało znaków (min. {{ limit }} znaków)",
     *      maxMessage = "Za duźo znaków (max. {{ limit }} znaków)"
     * )
     */
    public $message;

}
