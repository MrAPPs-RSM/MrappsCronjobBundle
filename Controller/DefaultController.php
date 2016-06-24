<?php

namespace Mrapps\CronjobBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MrappsCronjobBundle:Default:index.html.twig');
    }
}
