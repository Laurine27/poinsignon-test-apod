<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/', name: 'login')]
    public function login(): Response
    {
        return $this->render('login/index.html.twig');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
    }
}