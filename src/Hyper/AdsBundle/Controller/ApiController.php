<?php

namespace Hyper\AdsBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class ApiController extends FOSRestController
{
    /**
     * @Route("/announcement", name="api_announcement_list")
     * @Method("GET")
     */
    public function getAnnouncementListAction()
    {
    }
}
