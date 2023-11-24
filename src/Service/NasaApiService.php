<?php

namespace App\Service;

use App\Entity\NasaPhoto;
use App\Exception\PhotoAlreadyExistException;
use App\Repository\NasaPhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NasaApiService
{
    public function __construct(
        private readonly HttpClientInterface    $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly NasaPhotoRepository $nasaPhotoRepository,
        #[Autowire(env: 'NASA_API_KEY')]
        private readonly string $apiKey)
    {
    }

    public function fetchAndStoreNasaPhoto(\DateTime $date): void
    {
        if ($this->isExistingPhoto($date, true)) {
            throw new PhotoAlreadyExistException(message: 'Photo already exists.');
        }

        $previousNasaPhoto = $this->nasaPhotoRepository->findByPreviousDate($date);

        $this->fetchApiNasaPhoto($date);

        if (null === $previousNasaPhoto) {
            $this->fetchApiNasaPhoto($date->modify('-1 day'));
        }
    }

    public function storeNasaPhoto(array $content): void
    {
        $nasaPhoto = new NasaPhoto();
        $nasaPhoto->setDate(new \DateTime($content['date']));
        $nasaPhoto->setExplanation($content['explanation']);
        $nasaPhoto->setTitle($content['title']);
        $nasaPhoto->setUrl($content['url']);
        $nasaPhoto->setIsPhoto($content['media_type'] === NasaPhoto::IMAGE);

        $this->entityManager->persist($nasaPhoto);
        $this->entityManager->flush();
    }

    public function getNasaPhotoByDate(\DateTime $date): ?NasaPhoto
    {
        if ($this->isExistingPhoto($date)) {
            return $this->nasaPhotoRepository->findByDate($date);
        }

        // Vérifier si l'image est déjà stockée en base de données
        $previousNasaPhoto = $this->nasaPhotoRepository->findByPreviousDate($date);

        if ($previousNasaPhoto === null) {
            // Enregistre l'image en base de données afin d'affiche l'image du jour précédent
            $previousNasaPhoto = $this->nasaPhotoRepository->findByPreviousDate($date);
        }

        return $previousNasaPhoto;
    }

    private function isExistingPhoto(\DateTime $date, bool $allowVideo = false): bool
    {
        $existingPhoto = $this->nasaPhotoRepository->findByDate($date);

        if (!$allowVideo && !$existingPhoto?->isPhoto()) {
            return false;
        }

        return $existingPhoto !== null;
    }

    public function fetchApiNasaPhoto(\DateTime $date): void
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'https://api.nasa.gov/planetary/apod?api_key=' . $this->apiKey,
                [
                    'query' => [
                        'date' => $date->format('Y-m-d')
                    ],
                ]
            );

            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                $this->storeNasaPhoto($content);
            } else {
                throw new \Exception('Error while fetching NASA\'s daily photo.');
            }
        } catch (\Exception $e) {
            throw new \Exception('Error while fetching NASA\'s daily photo.');
        }
    }
}