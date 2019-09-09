<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @route("/api")
 */
class UserController extends AbstractController
{
    /**
     * Test custom route for API
     * 
     * @Route("/test", name="user")
     */
    public function test()
    {
        $jsonResponse = new JsonResponse(['test' => 'value']);

        return $jsonResponse;
    }
}
