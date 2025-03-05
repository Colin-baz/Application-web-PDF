<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends AbstractController
{
    public function changeSubscription(Request $request): Response
    {
        return $this->render('subscription/index.html.twig');
    }
}
