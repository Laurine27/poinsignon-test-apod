<?php

namespace App\Controller;

use Google\Client;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleAuthController extends AbstractController
{
    public function __construct(
        #[Autowire(env: 'GOOGLE_CLIENT_ID')]
        private string $clientId,
        #[Autowire(env: 'GOOGLE_CLIENT_SECRET')]
        private string $clientSecret,
    )
    {
    }

    #[Route('/google-connect', name: 'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        //Redirect to google
        return $clientRegistry->getClient('google')->redirect([], []);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request)
    {
        $client = new Client();
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($this->generateUrl('connect_google_check', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL));
        $client->setScopes(['email', 'profile']);

        if (!$request->query->get('code')) {
            $authUrl = $client->createAuthUrl();
            return new RedirectResponse($authUrl);
        }

        $client->authenticate($request->query->get('code'));
        return $this->redirectToRoute('nasa_photo_of_the_day');

    }
}