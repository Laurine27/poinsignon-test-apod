<?php

namespace App\Controller;

use App\Service\NasaApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/nasa')]
class NasaApiController extends AbstractController
{
    #[Route('/photo-of-the-day', name: 'nasa_photo_of_the_day')]
    #[IsGranted('ROLE_GOOGLE')]
    public function photoOfTheDay(NasaApiService $nasaApiService): Response
    {
        $date = new \DateTime();

        $nasaPhoto = $nasaApiService->getNasaPhotoByDate($date);

        if (null === $nasaPhoto) {
            return $this->render('error/no_data.html.twig');
        }

        return $this->render('nasa/photo_of_the_day.html.twig', [
            'nasaPhoto' => $nasaPhoto,
        ]);
    }
}