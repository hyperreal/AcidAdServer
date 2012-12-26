<?php

namespace Wikp\ShoppingCartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WikpShoppingCartBundle:Default:index.html.twig', array('name' => $name));
    }
}
