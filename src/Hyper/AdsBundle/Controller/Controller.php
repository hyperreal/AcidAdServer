<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;

class Controller extends SymfonyController
{
    protected function trans($word, $params = array())
    {
        return $this->get('translator')->trans($word, $params, 'HyperAdsBundle');
    }
}
