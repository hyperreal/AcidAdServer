<?php

namespace Wikp\PaymentMtgoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WikpPaymentMtgoxBundle:Default:index.html.twig', array('name' => $name));
    }
}
